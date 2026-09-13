# Fictional local demo image pack

Pack status: **26/36 supplied and approved** in the current working tree. The supplied files are fictional AI-generated demo assets; no images were downloaded from external sources or fabricated as placeholders in the repository. The manifest contains the planned filenames and records provenance for each supplied asset.

Add source files under `resources/demo/images/`, then review each manifest record:
- `filename`, `category`, `source_type`, `note`, `license_note`, `approved`.
- Supported v2 source types: `generated-demo-asset`, `local-original`, `licensed-local`.
- Set `approved: true` only after the real source file and its provenance/permission are reviewed.
- For generated fictional assets, describe generation/provenance honestly. Do not invent a photographer, licence or source URL.
- Legacy v1 categories exterior/lobby/standard-room/deluxe-room remain import-compatible.

Accepted files: local JPEG/PNG/WebP, at most 8 MB and 40 MP; realpath must remain beneath the configured image directory. Prefer web-sized source images (roughly 1200–1800 pixels wide), not the maximum file limit. Unknown source types, unapproved files, invalid MIME, missing files and path escapes do not create image records.

## Exact source files still needed

- `exterior-city/exterior-city-02.webp`
- `exterior-resort/exterior-resort-02.webp`
- `lobby-modern/lobby-modern-02.webp`
- `restaurant/restaurant-02.webp`
- `pool/pool-01.webp`
- `pool/pool-02.webp`
- `rooftop/rooftop-01.webp`
- `rooftop/rooftop-02.webp`
- `spa/spa-01.webp`
- `spa/spa-02.webp`

## Import

```powershell
php artisan demo:images
# demo:seed also invokes this importer after its non-destructive dataset check.
```

Current report after the first import:
```
26/36 assets found; 26 approved valid assets.
100 hotels populated; 450 rooms populated.
Missing categories: pool, rooftop, spa
New local image relations: 2825 on first import; 0 on an unchanged repeat run.
```

Missing categories means no usable approved asset in that category, including present but unapproved files. Found counts manifest entries whose files exist safely under the root; it is distinct from the approved-valid count. Populated counts existing demo image relations after import, so repeat runs can have zero new relations but populated hotels/rooms.

Mapping is deterministic by ordered hotel/room IDs. Hotel exterior/lobby variants alternate; shared reception/restaurant/breakfast/pool/rooftop/spa/gym images fill galleries. Room name/order chooses standard/deluxe/suite/family plus twin/king, bathroom, balcony and view variants. Photos are fictional shared portfolio illustrations, not a claim that a property has a particular real-world view; curate the pack/mapping before presenting it as a geographically realistic catalogue. Missing categories are skipped, not replaced with invented URLs.

The importer copies assets into per-hotel/per-room storage directories with content-hash filenames. Repeating an unchanged import does not duplicate relations and never modifies bookings/inventory. Files are not shared by storage path across supplier entities. Run `php artisan storage:link` if the public storage link is absent. No automatic internet fetching exists.
