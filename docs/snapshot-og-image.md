# Snapshot Page — OG Image Generation

## Goal

When a user shares their snapshot URL (e.g. on Twitter, iMessage, Discord), the stats card should appear as the link preview image rather than a generic fallback.

## Approach: Sidecar + Browsershot

Use [Sidecar](https://hammerstone.dev/sidecar/docs) (by Aaron Francis) to invoke a Node.js AWS Lambda function that runs Puppeteer/Chromium, screenshots the snapshot page's stats card, and stores the result on S3.

**Why this approach:**
- No Chrome/Chromium needed on the Cloudways server
- Lambda costs are effectively free at our volume (within AWS free tier)
- S3 is already configured — store the image there and serve it directly
- The OG image automatically matches the Blade component exactly, no reimplementation needed
- Once generated, subsequent shares are just an S3 read

## Implementation sketch

1. Install `hammerstone/sidecar` and configure a `ScreenshotFunction` Lambda (Node.js, with a Chromium layer)
2. On first visit to a snapshot URL, dispatch a queued job that:
   - Invokes the Sidecar function with the snapshot URL
   - Receives the PNG back and stores it at `snapshots/{user}/{year}.png` on S3
3. Add OG meta tags to `snapshot-card.blade.php` via `@push('head')` — pattern already exists on `show-return.blade.php`
4. Image URL can be a public S3 URL or a signed URL depending on bucket visibility

## Reference

- Pattern to follow for OG tags: `resources/views/livewire/show-return.blade.php`
- Pattern to follow for image storage: `app/Services/ReturnCardService.php`
- Sidecar docs: https://hammerstone.dev/sidecar/docs
