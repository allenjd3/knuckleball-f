#!/usr/bin/env python3
"""
Knuckleball Card Shop Fetcher
Pulls sports card shops from the Google Places API (Text Search) and
pushes them to the Knuckleball API as pending shops for review.
Also supports a re-verification pass that flags permanently closed shops.

Usage:
    python card_shop_fetcher.py --dry-run              # fetch + print, no push
    python card_shop_fetcher.py                        # fetch + push
    python card_shop_fetcher.py --metros "Dayton, OH"  # single metro sweep
    python card_shop_fetcher.py --verify               # re-check live shops for closures

Env vars (.env supported):
    GOOGLE_PLACES_API_KEY     Google Cloud key with Places API (New) enabled
    KNUCKLEBALL_API_URL       e.g. https://knuckleball.app/api/shops/import
    KNUCKLEBALL_API_TOKEN     bearer token (ability: shops:import)
    KNUCKLEBALL_VERIFY_URL    e.g. https://knuckleball.app/api/shops/verify

Cost note: Text Search calls are billed per request. The free monthly
credit covers thousands of searches; a full 50-state sweep at ~3 queries
per metro x ~150 metros lands well inside it. Don't run the full sweep
on a daily cron — monthly is plenty for discovery, quarterly for verify.
"""

import argparse
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

try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
log = logging.getLogger("knuckleball-shops")

PLACES_SEARCH_URL = "https://places.googleapis.com/v1/places:searchText"
PLACES_DETAILS_URL = "https://places.googleapis.com/v1/places/{place_id}"

FIELD_MASK = ",".join([
    "places.id",
    "places.displayName",
    "places.formattedAddress",
    "places.addressComponents",
    "places.location",
    "places.nationalPhoneNumber",
    "places.websiteUri",
    "places.regularOpeningHours.weekdayDescriptions",
    "places.businessStatus",
    "places.rating",
    "places.userRatingCount",
    "places.types",
    "nextPageToken",
])

# Tight search terms — avoids broad "collectibles" which pulls antique stores
SEARCH_TERMS = [
    "sports card shop",
    "baseball card store",
    "trading card hobby shop",
]

# Seed list — expand to full national coverage over time.
METROS = [
    "Cincinnati, OH",
    "Dayton, OH",
    "Columbus, OH",
    "Lynchburg, VA",
    "Richmond, VA",
    "Charlotte, NC",
]

REQUEST_DELAY = 0.5

# Shop name must contain at least one of these to pass the filter
CARD_SHOP_KEYWORDS = re.compile(
    r"\b(card|cards|collectible|collectibles|hobby|memorabilia|comic|autograph|baseball|sports)\b",
    re.I,
)

# If the name contains any of these it's almost certainly not a card shop
BLOCKLIST = re.compile(
    r"\b(gift|book|antique|thrift|toy|game store|candy|floral|florist|"
    r"pharmacy|salon|spa|restaurant|cafe|coffee|pizza|burger|sushi|"
    r"grocery|hardware|clothing|jewelry|furniture|pet|auto|insurance)\b",
    re.I,
)

# Google place types that indicate a card shop is plausible
ALLOWED_TYPES = {
    "hobby_shop", "book_store", "store", "point_of_interest",
    "establishment", "shopping_mall",
}


def is_card_shop(name: str, types: list) -> bool:
    """Return True only if the place looks like a card/hobby shop."""
    if BLOCKLIST.search(name):
        return False
    if CARD_SHOP_KEYWORDS.search(name):
        return True
    # Name didn't match keywords — only keep if Google typed it as hobby_shop
    if "hobby_shop" in (types or []):
        return True
    return False


@dataclass
class CardShop:
    place_id: str
    name: str
    address: Optional[str] = None
    city: Optional[str] = None
    state: Optional[str] = None
    zip_code: Optional[str] = None
    lat: Optional[float] = None
    lng: Optional[float] = None
    phone: Optional[str] = None
    website: Optional[str] = None
    hours: Optional[list] = None
    rating: Optional[float] = None
    rating_count: Optional[int] = None
    business_status: Optional[str] = None
    source_name: str = "google_places"
    scraped_at: str = field(default_factory=lambda: datetime.utcnow().isoformat())

    def to_payload(self) -> dict:
        return asdict(self)


def _address_components(components: list) -> dict:
    out = {"city": None, "state": None, "zip_code": None}
    for c in components or []:
        types = c.get("types", [])
        if "locality" in types:
            out["city"] = c.get("longText")
        elif "administrative_area_level_1" in types:
            out["state"] = c.get("shortText")
        elif "postal_code" in types:
            out["zip_code"] = c.get("longText")
    return out


def parse_place(p: dict) -> CardShop:
    comps = _address_components(p.get("addressComponents"))
    loc = p.get("location") or {}
    hours = (p.get("regularOpeningHours") or {}).get("weekdayDescriptions")
    return CardShop(
        place_id=p["id"],
        name=(p.get("displayName") or {}).get("text", "Unknown"),
        address=p.get("formattedAddress"),
        city=comps["city"],
        state=comps["state"],
        zip_code=comps["zip_code"],
        lat=loc.get("latitude"),
        lng=loc.get("longitude"),
        phone=p.get("nationalPhoneNumber"),
        website=p.get("websiteUri"),
        hours=hours,
        rating=p.get("rating"),
        rating_count=p.get("userRatingCount"),
        business_status=p.get("businessStatus"),
    )


def search_metro(api_key: str, term: str, metro: str) -> list[CardShop]:
    """Run one text search (with pagination) for a term in a metro."""
    shops: list[CardShop] = []
    page_token = None
    headers = {
        "Content-Type": "application/json",
        "X-Goog-Api-Key": api_key,
        "X-Goog-FieldMask": FIELD_MASK,
    }
    while True:
        body = {"textQuery": f"{term} in {metro}"}
        if page_token:
            body["pageToken"] = page_token
        time.sleep(REQUEST_DELAY)
        resp = requests.post(PLACES_SEARCH_URL, headers=headers, json=body, timeout=30)
        if not resp.ok:
            log.warning("Places search failed (%s) for '%s in %s': %s",
                        resp.status_code, term, metro, resp.text[:300])
            break
        data = resp.json()
        for place in data.get("places", []):
            if place.get("businessStatus") == "CLOSED_PERMANENTLY":
                continue
            name = (place.get("displayName") or {}).get("text", "")
            types = place.get("types", [])
            if not is_card_shop(name, types):
                log.debug("Filtered out: %s", name)
                continue
            shops.append(parse_place(place))
        page_token = data.get("nextPageToken")
        if not page_token:
            break
    return shops


def discover(api_key: str, metros: list[str]) -> list[CardShop]:
    all_shops: dict[str, CardShop] = {}
    for metro in metros:
        metro_count_before = len(all_shops)
        for term in SEARCH_TERMS:
            for shop in search_metro(api_key, term, metro):
                all_shops[shop.place_id] = shop
        added = len(all_shops) - metro_count_before
        log.info("%s: +%d shops (%d total)", metro, added, len(all_shops))
    return list(all_shops.values())


# ---------------------------------------------------------------------------
# Push / verify against Knuckleball
# ---------------------------------------------------------------------------

def kb_headers(token: str) -> dict:
    return {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json",
        "User-Agent": "KnuckleballShopBot/1.0 (+https://knuckleball.app)",
    }


def push_to_knuckleball(shops: list[CardShop]) -> None:
    api_url = os.environ.get("KNUCKLEBALL_API_URL")
    token = os.environ.get("KNUCKLEBALL_API_TOKEN")
    if not api_url or not token:
        log.error("Missing KNUCKLEBALL_API_URL or KNUCKLEBALL_API_TOKEN")
        sys.exit(1)
    created = skipped = 0
    for i in range(0, len(shops), 200):
        chunk = shops[i:i + 200]
        resp = requests.post(
            api_url,
            json={"shops": [s.to_payload() for s in chunk]},
            headers=kb_headers(token),
            timeout=60,
        )
        if resp.ok:
            data = resp.json()
            created += data.get("created", 0)
            skipped += data.get("skipped", 0)
        else:
            log.error("Push failed (%s): %s", resp.status_code, resp.text[:500])
            sys.exit(1)
    log.info("Pushed: %d created, %d duplicates skipped", created, skipped)


def verify_closures() -> None:
    """Quarterly pass: re-check live shops against Place Details for closures."""
    api_key = os.environ.get("GOOGLE_PLACES_API_KEY")
    verify_url = os.environ.get("KNUCKLEBALL_VERIFY_URL")
    token = os.environ.get("KNUCKLEBALL_API_TOKEN")
    if not all([api_key, verify_url, token]):
        log.error("Missing GOOGLE_PLACES_API_KEY, KNUCKLEBALL_VERIFY_URL, or token")
        sys.exit(1)

    resp = requests.get(verify_url, headers=kb_headers(token), timeout=30)
    resp.raise_for_status()
    place_ids = resp.json().get("place_ids", [])
    log.info("Verifying %d shops", len(place_ids))

    closed = []
    for pid in place_ids:
        time.sleep(REQUEST_DELAY)
        r = requests.get(
            PLACES_DETAILS_URL.format(place_id=pid),
            headers={
                "X-Goog-Api-Key": api_key,
                "X-Goog-FieldMask": "id,businessStatus",
            },
            timeout=30,
        )
        if r.ok and r.json().get("businessStatus") == "CLOSED_PERMANENTLY":
            closed.append(pid)
            log.info("CLOSED: %s", pid)

    if closed:
        requests.post(
            verify_url,
            json={"closed_place_ids": closed},
            headers=kb_headers(token),
            timeout=60,
        ).raise_for_status()
    log.info("Verify complete: %d closures reported", len(closed))


def main() -> None:
    parser = argparse.ArgumentParser(description="Knuckleball card shop fetcher")
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--metros", nargs="*", help="Override the METROS list")
    parser.add_argument("--verify", action="store_true",
                        help="Re-check live shops for permanent closures")
    args = parser.parse_args()

    if args.verify:
        verify_closures()
        return

    api_key = os.environ.get("GOOGLE_PLACES_API_KEY")
    if not api_key:
        log.error("Missing GOOGLE_PLACES_API_KEY")
        sys.exit(1)

    metros = args.metros or METROS
    shops = discover(api_key, metros)
    log.info("Total unique shops: %d", len(shops))

    if args.dry_run:
        print(json.dumps([s.to_payload() for s in shops], indent=2))
        return

    if shops:
        push_to_knuckleball(shops)


if __name__ == "__main__":
    main()
