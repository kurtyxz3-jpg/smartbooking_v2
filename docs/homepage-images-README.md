# Homepage image update

Apply this update AFTER the single-salon patch. It replaces index.php with the version containing both updates.

## Changes
- Gallery, Packages and Visit Us use clean photographs extracted directly from the five supplied PDFs. Separate CamScanner footer graphics and PDF page margins are excluded; original salon logos and embedded branding remain.
- Adds a Brows gallery tab using the supplied brow photographs.
- Preserves the existing page layout, package prices and booking links. The promotion fallback image also uses a clean photograph.
- No database migration is required for this image update. Other pages and the AI generation module are unchanged.

## Changed/new files
- index.php (updated)
- includes/homepage-media.php (new gallery asset mapping)
- assets/images/homepage-clean/*.webp (97 new images: 19 nails, 24 salon, 17 lips, 31 services/campaigns, 6 brows)
- docs/homepage-images-README.md (these instructions)

## Apply
1. Back up the current project folder, especially index.php.
2. Apply the earlier single-salon patch first if it has not been installed.
3. Extract this ZIP into the smartbooking_v2 project root. Merge folders and replace index.php when prompted. Do not create an extra nested smartbooking_v2 folder.
4. Hard-refresh the homepage (Ctrl+F5).

## Verification checklist
- Open all five Gallery tabs and enlarge a photograph.
- Check the package spotlight and all three package cards.
- Check Visit Us on desktop and mobile.
- Confirm photos load without scanner footers and booking links still work.

Validation performed: both PHP files pass syntax checks. Browser checks cover gallery tabs, enlarged images, local image requests, and desktop/mobile layout.

Rollback: restore the backed-up index.php. The added image folder and mapping file can remain unused or be removed.
