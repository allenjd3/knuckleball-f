#!/usr/bin/env python3
"""
Knuckleball Signing Scraper
Scrapes paid/free autograph signing announcements from promoter and
shop sites, and pushes them into the same /api/events/import staging
pipeline as the card show scraper, tagged event_type='player_signing'.

Primary strategy: JSON-LD. Most promoter sites embed Schema.org Event
markup (<script type="application/ld+json">) for their own SEO. Parsing
that is far more durable than CSS selectors — it survives redesigns.
Fallback: per-site CSS selector sources, same as the show scraper.

Usage:
    python signing_scraper.py --dry-run
    python signing_scraper.py

Env vars (.env supported) — same as the show scraper:
    KNUCKLEBALL_API_URL, KNUCKLEBALL_API_TOKEN
"""

import argparse
import hashlib
import json
import logging
import os
import re
import sys
import time
from dataclasses import dataclass, asdict, field
from datetime import datetime
from typing import Optional

import requests
from bs4 import BeautifulSoup

try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
log = logging.getLogger("knuckleball-signings")

USER_AGENT = "KnuckleballEventBot/1.0 (+https://knuckleball.app/about; events@knuckleball.app)"
REQUEST_DELAY = 2.0

MAIL_IN_KEYWORDS = re.compile(r"\b(mail[\s-]?in|send[\s-]?in|mail order)\b", re.I)


@dataclass
class Signing:
    title: str
    start_date: str
    end_date: Optional[str] = None
    player_name: Optional[str] = None
    venue: Optional[str] = None
    address: Optional[str] = None
    city: Optional[str] = None
    state: Optional[str] = None
    zip_code: Optional[str] = None
    admission: Optional[str] = None        # price info lands here
    promoter: Optional[str] = None
    contact: Optional[str] = None
    event_type: str = "player_signing"
    event_format: str = "in_person"        # or "mail_in"
    source_url: str = ""
    source_name: str = ""
    scraped_at: str = field(default_factory=lambda: datetime.utcnow().isoformat())

    @property
    def dedupe_hash(self) -> str:
        key = "|".join([
            re.sub(r"\W+", "", (self.title or "").lower()),
            self.start_date or "",
            (self.zip_code or self.city or "").lower().strip(),
        ])
        return hashlib.sha256(key.encode()).hexdigest()[:16]

    def to_payload(self) -> dict:
        d = asdict(self)
        d["dedupe_hash"] = self.dedupe_hash
        return d


def make_session() -> requests.Session:
    s = requests.Session()
    s.headers.update({"User-Agent": USER_AGENT})
    return s


def fetch_html(session: requests.Session, url: str) -> Optional[str]:
    try:
        time.sleep(REQUEST_DELAY)
        resp = session.get(url, timeout=30)
        resp.raise_for_status()
        return resp.text
    except requests.RequestException as e:
        log.warning("Fetch failed for %s: %s", url, e)
        return None


# ---------------------------------------------------------------------------
# JSON-LD extraction (primary strategy)
# ---------------------------------------------------------------------------

def _iso_date(value: str) -> Optional[str]:
    """Schema.org dates arrive as '2026-07-11', '2026-07-11T10:00:00-04:00', etc."""
    if not value:
        return None
    m = re.match(r"(\d{4}-\d{2}-\d{2})", value.strip())
    return m.group(1) if m else None


def _walk_for_events(node) -> list[dict]:
    """Recursively find Event-typed objects in a JSON-LD structure."""
    found = []
    if isinstance(node, dict):
        node_type = node.get("@type", "")
        types = node_type if isinstance(node_type, list) else [node_type]
        if any("Event" in str(t) for t in types):
            found.append(node)
        for v in node.values():
            found.extend(_walk_for_events(v))
    elif isinstance(node, list):
        for item in node:
            found.extend(_walk_for_events(item))
    return found


def _parse_jsonld_event(ev: dict, source_url: str, source_name: str) -> Optional[Signing]:
    title = ev.get("name") or ""
    start = _iso_date(str(ev.get("startDate", "")))
    if not title or not start:
        return None

    end = _iso_date(str(ev.get("endDate", "")))
    if end == start:
        end = None

    venue = address = city = state = zip_code = None
    location = ev.get("location") or {}
    if isinstance(location, list):
        location = location[0] if location else {}
    if isinstance(location, dict):
        venue = location.get("name")
        addr = location.get("address") or {}
        if isinstance(addr, dict):
            address = addr.get("streetAddress")
            city = addr.get("addressLocality")
            state = addr.get("addressRegion")
            zip_code = addr.get("postalCode")
        elif isinstance(addr, str):
            address = addr

    admission = None
    offers = ev.get("offers") or {}
    if isinstance(offers, list):
        offers = offers[0] if offers else {}
    if isinstance(offers, dict):
        price = offers.get("price")
        currency = offers.get("priceCurrency", "USD")
        if price not in (None, ""):
            admission = f"{price} {currency}"

    organizer = ev.get("organizer") or {}
    if isinstance(organizer, list):
        organizer = organizer[0] if organizer else {}
    promoter = organizer.get("name") if isinstance(organizer, dict) else None

    blob = f"{title} {ev.get('description', '')}"
    event_format = "mail_in" if MAIL_IN_KEYWORDS.search(blob) else "in_person"

    performer = ev.get("performer") or {}
    if isinstance(performer, list):
        performer = performer[0] if performer else {}
    player = performer.get("name") if isinstance(performer, dict) else None

    return Signing(
        title=title.strip(),
        start_date=start,
        end_date=end,
        player_name=player,
        venue=venue,
        address=address,
        city=city,
        state=state,
        zip_code=zip_code,
        admission=admission,
        promoter=promoter,
        event_format=event_format,
        source_url=source_url,
        source_name=source_name,
    )


def extract_jsonld_signings(html: str, source_url: str, source_name: str) -> list[Signing]:
    soup = BeautifulSoup(html, "html.parser")
    signings: list[Signing] = []
    for script in soup.find_all("script", type="application/ld+json"):
        try:
            data = json.loads(script.string or "")
        except (json.JSONDecodeError, TypeError):
            continue
        for ev in _walk_for_events(data):
            parsed = _parse_jsonld_event(ev, source_url, source_name)
            if parsed:
                signings.append(parsed)
    return signings


# ---------------------------------------------------------------------------
# Sources
# ---------------------------------------------------------------------------

class JsonLdSource:
    """
    Point at any page (or list of pages) that embeds Schema.org Event
    markup. No selectors needed. Works for many promoter sites,
    Eventbrite pages, and shop sites built on modern platforms.
    """

    def __init__(self, name: str, urls: list[str]):
        self.name = name
        self.urls = urls

    def scrape(self, session: requests.Session) -> list[Signing]:
        out: list[Signing] = []
        for url in self.urls:
            html = fetch_html(session, url)
            if html:
                found = extract_jsonld_signings(html, url, self.name)
                log.info("%s: %d signings from %s", self.name, len(found), url)
                out.extend(found)
        return out


class CssSource:
    """
    Fallback for sites without JSON-LD. Same idea as the show scraper's
    GenericTableSource: configure selectors per site after inspecting
    its markup. Stub provided — fill in per source.
    """

    def __init__(self, name: str, url: str, item_selector: str,
                 title_selector: str, date_selector: str,
                 location_selector: Optional[str] = None):
        self.name = name
        self.url = url
        self.item_selector = item_selector
        self.title_selector = title_selector
        self.date_selector = date_selector
        self.location_selector = location_selector

    def scrape(self, session: requests.Session) -> list[Signing]:
        html = fetch_html(session, self.url)
        if not html:
            return []
        soup = BeautifulSoup(html, "html.parser")
        out: list[Signing] = []
        for item in soup.select(self.item_selector):
            title_el = item.select_one(self.title_selector)
            date_el = item.select_one(self.date_selector)
            if not title_el or not date_el:
                continue
            raw_date = date_el.get_text(" ", strip=True)
            m = re.search(r"(\d{4}-\d{2}-\d{2})", raw_date)
            start = m.group(1) if m else None
            if not start:
                # extend with the date formats from the show scraper as needed
                continue
            title = title_el.get_text(" ", strip=True)
            event_format = "mail_in" if MAIL_IN_KEYWORDS.search(item.get_text()) else "in_person"
            out.append(Signing(
                title=title,
                start_date=start,
                event_format=event_format,
                source_url=self.url,
                source_name=self.name,
            ))
        log.info("%s: %d signings", self.name, len(out))
        return out


# ---------------------------------------------------------------------------
# Hardcoded seed signings — real upcoming events for dry-run testing.
# These are pulled from public promoter listings. Once the pipeline is
# proven, swap these out for live scraped sources below.
# ---------------------------------------------------------------------------

class HardcodedSource:
    """Emits a static list of Signing objects — no network calls needed."""

    def __init__(self, name: str, signings: list[Signing]):
        self.name = name
        self._signings = signings

    def scrape(self, session) -> list[Signing]:
        log.info("%s: %d hardcoded signings", self.name, len(self._signings))
        return self._signings


SOURCES = [
    HardcodedSource(
        name="tristar-national-chicago-2026",
        signings=[
            Signing(
                title="TRISTAR National Autograph Pavilion — Chicago 2026",
                start_date="2026-07-29",
                end_date="2026-08-02",
                venue="Donald E. Stephens Convention Center",
                address="5555 N River Rd",
                city="Rosemont",
                state="IL",
                zip_code="60018",
                admission="Varies by athlete",
                promoter="TRISTAR Productions",
                contact="https://www.tristarproductions.com/National/tickets.html",
                event_type="player_signing",
                event_format="in_person",
                source_url="https://www.tristarproductions.com/schedule.html",
                source_name="tristar-national-chicago-2026",
            ),
            Signing(
                title="Johnny Bench Autograph Signing — TRISTAR Chicago National",
                start_date="2026-07-29",
                end_date="2026-08-02",
                player_name="Johnny Bench",
                venue="Donald E. Stephens Convention Center",
                address="5555 N River Rd",
                city="Rosemont",
                state="IL",
                zip_code="60018",
                promoter="TRISTAR Productions",
                contact="https://www.tristarproductions.com/National/tickets.html",
                event_type="player_signing",
                event_format="in_person",
                source_url="https://www.tristarproductions.com/schedule.html",
                source_name="tristar-national-chicago-2026",
            ),
            Signing(
                title="Mike Tyson Autograph Signing — TRISTAR Chicago National",
                start_date="2026-07-29",
                end_date="2026-08-02",
                player_name="Mike Tyson",
                venue="Donald E. Stephens Convention Center",
                address="5555 N River Rd",
                city="Rosemont",
                state="IL",
                zip_code="60018",
                promoter="TRISTAR Productions",
                contact="https://www.tristarproductions.com/National/tickets.html",
                event_type="player_signing",
                event_format="in_person",
                source_url="https://www.tristarproductions.com/schedule.html",
                source_name="tristar-national-chicago-2026",
            ),
            Signing(
                title="Cal Ripken Jr. Autograph Signing — TRISTAR Chicago National",
                start_date="2026-07-29",
                end_date="2026-08-02",
                player_name="Cal Ripken Jr.",
                venue="Donald E. Stephens Convention Center",
                address="5555 N River Rd",
                city="Rosemont",
                state="IL",
                zip_code="60018",
                promoter="TRISTAR Productions",
                contact="https://www.tristarproductions.com/National/tickets.html",
                event_type="player_signing",
                event_format="in_person",
                source_url="https://www.tristarproductions.com/schedule.html",
                source_name="tristar-national-chicago-2026",
            ),
            Signing(
                title="Drew Brees Autograph Signing — TRISTAR Chicago National",
                start_date="2026-07-29",
                end_date="2026-08-02",
                player_name="Drew Brees",
                venue="Donald E. Stephens Convention Center",
                address="5555 N River Rd",
                city="Rosemont",
                state="IL",
                zip_code="60018",
                promoter="TRISTAR Productions",
                contact="https://www.tristarproductions.com/National/tickets.html",
                event_type="player_signing",
                event_format="in_person",
                source_url="https://www.tristarproductions.com/schedule.html",
                source_name="tristar-national-chicago-2026",
            ),
            Signing(
                title="Roger Clemens Private Autograph Signing",
                start_date="2026-07-01",
                player_name="Roger Clemens",
                admission="Varies by item",
                promoter="TRISTAR Productions",
                contact="https://shop.tristarproductions.com/roger-clemens-private-signing",
                event_type="player_signing",
                event_format="mail_in",
                source_url="https://www.tristarproductions.com/schedule.html",
                source_name="tristar-national-chicago-2026",
            ),
        ],
    ),
    HardcodedSource(
        name="leftys-sports-cards",
        signings=[
            Signing(
                title="Jeff Kent Autograph Signing — Lefty's Sports Cards",
                start_date="2026-08-30",
                player_name="Jeff Kent",
                venue="Lefty's Sports",
                city="San Mateo",
                state="CA",
                admission="$124.99 regular / $159.99 premium",
                contact="(650) 697-2274",
                promoter="Lefty's Sports",
                event_type="player_signing",
                event_format="in_person",
                source_url="https://leftyssportscards.com/pages/upcoming-events",
                source_name="leftys-sports-cards",
            ),
        ],
    ),
]


# ---------------------------------------------------------------------------
# Push (same endpoint as the show scraper)
# ---------------------------------------------------------------------------

def push_to_knuckleball(signings: list[Signing]) -> None:
    api_url = os.environ.get("KNUCKLEBALL_API_URL")
    token = os.environ.get("KNUCKLEBALL_API_TOKEN")
    if not api_url or not token:
        log.error("Missing KNUCKLEBALL_API_URL or KNUCKLEBALL_API_TOKEN")
        sys.exit(1)
    resp = requests.post(
        api_url,
        json={"events": [s.to_payload() for s in signings]},
        headers={
            "Authorization": f"Bearer {token}",
            "Accept": "application/json",
            "User-Agent": USER_AGENT,
        },
        timeout=60,
    )
    if resp.ok:
        data = resp.json()
        log.info("Pushed %d signings: %s created, %s skipped",
                 len(signings), data.get("created", "?"), data.get("skipped", "?"))
    else:
        log.error("API push failed (%s): %s", resp.status_code, resp.text[:500])
        sys.exit(1)


def main() -> None:
    parser = argparse.ArgumentParser(description="Knuckleball signing scraper")
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()

    if not SOURCES:
        log.warning("No sources configured. Add sources to SOURCES.")
        return

    session = make_session()
    seen: set[str] = set()
    all_signings: list[Signing] = []
    for source in SOURCES:
        for s in source.scrape(session):
            if s.dedupe_hash in seen:
                continue
            seen.add(s.dedupe_hash)
            all_signings.append(s)

    log.info("Total unique signings: %d", len(all_signings))

    if args.dry_run:
        print(json.dumps([s.to_payload() for s in all_signings], indent=2))
        return

    if all_signings:
        push_to_knuckleball(all_signings)


if __name__ == "__main__":
    main()
