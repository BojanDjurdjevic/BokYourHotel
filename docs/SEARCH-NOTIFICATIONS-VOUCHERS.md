# Search, notifications, vouchers and fictional media

Implementation audit: 2026-09-13. This extends the historical MVP and production-readiness reports; it is not a production-readiness declaration.

## Search architecture and semantics

`HotelSearchRequest` validates URL state and `HotelSearchService` builds the query. Controllers remain thin. Home and the hotel listing share the existing Blade/Alpine search component. Hotel details preserve search context and booking receives validated dates/guest counts.

- Autocomplete queries distinct published hotel city/country pairs directly, with a two-character minimum, city-prefix matching, eight-result limit and the existing availability limiter (60 requests/minute/IP). Selection stores city + country. No location API or City table exists.
- Alpine debounces for 300 ms and invalidates outstanding responses immediately on input. ArrowUp/Down, Enter, Escape, labels, combobox/listbox state, loading and empty/error states are included.
- Canonical city/country selection matches exactly. City-only legacy input supports substring search. Dates, guests, filters, sorting and pagination are ordinary query parameters. Pagination preserves them and loads 12 hotels.
- Stars are nullable self-reported hotel ratings, not review scores. Existing demo Aurelune/Veloria properties receive fictional 5/4-star values; other properties remain unrated. Supplier setup can set them.
- Every selected hotel facility must be present in the hotel's existing JSON facilities. Every selected room facility must belong to the SAME matching room through its facility pivot. These are separate filters.
- Search finds one room configuration capable of accommodating adults + children in one unit. It does not allocate a party across multiple rooms; the existing booking selection/quantity flow remains available.
- A selected board must be offered by that matching room. With no board selected, the cheapest offered board is used for the quote.
- Dated search checks every night in `[check_in, check_out)`, up to the existing 30-night maximum. A sold-out inventory night excludes the room. Missing inventory uses existing `total_units`; missing/NULL inventory prices use base room price, matching AvailabilityService.
- The dated quote is one unit's whole-stay room total plus board surcharge per night. Without dates it is an indicative one-night base + board quote. Price bounds and sorting use this same value; the hotel quote is the minimum qualifying room quote. No new currency conversion or pricing model was introduced.
- Default catalogue browsing retains published hotels without a bookable configuration and shows no invented quote. Dates/guest/room/board/price constraints require a qualifying room. Booking still recalculates and locks authoritative inventory/prices; search is not a reservation.

## Performance evidence and schema

Measured against the existing local demo plus development records: 101 hotels, 454 rooms, approximately 191,311 inventory rows. `php tests/Support/search-profile.php` reproduces query counts and MySQL EXPLAIN without mutating data.

| Rendered guest page | Before | After | Loaded / matching hotels |
|---|---:|---:|---|
| Public catalogue | 3 | 6 | 12 / 101 |
| All destinations, selected stay | Not implemented | 6 | 12 / 100 |
| Paris, selected stay | Not implemented | 6 | 2 / 2 |
| Autocomplete | Not implemented | 1 | At most 8 destination rows |

The additional three fixed queries supply filter options. These measurements cover the search service, option lists and rendered guest Blade, not HTTP session/rate-limit queries or FormRequest existence checks for selected room-facility/board IDs. Counts do not grow per hotel. Authenticated navigation adds one unread-count query per request, shared by desktop/mobile navigation. No massive hotel model collection is loaded for autocomplete.

Migration `2026_09_14_000002_index_inventory_search_dates.php` adds only `room_inventories(date, room_id)`. For a three-night inventory aggregate, EXPLAIN changed from an estimated 172,960-row scan of the existing room/date index to a 1,350-row range scan of the new index. Grouping still uses a temporary result. The existing unique room/date index and booking locks remain intact. A 101-row hotel scan does not justify another speculative index.

Migration `2026_09_14_000001_add_search_and_notifications.php` adds nullable `hotels.star_rating` and the Laravel notifications table with its notifiable index. Both new migrations were applied locally and by isolated test database migrations; each has a targeted down method. No existing foreign key policy changed.

## Notifications and transaction boundary

One `BookingActivity` event carries a scalar snapshot and a `BookingNoticeType` value. Six business types are supported: BookingCreated, BookingConfirmed, BookingCancelled, PaymentSucceeded, PaymentRefunded and BookingExpired. PaymentFailed stays immediate UI feedback to avoid noisy retry email. Demo seeding does not emit historical notifications. Suppliers/admins are not broadcast generic customer notices.

The event implements `ShouldDispatchAfterCommit`; successful service transitions record it inside their existing transaction. Its listener is registered once. The queued Laravel Notification also requests `afterCommit()`. Consequently outer rollback, failed cancellation/restore and failed payment do not enqueue mail/database notifications. Snapshots preserve the event's status/reason instead of silently showing a later booking state when a worker runs.

Owners receive database + mail; guests receive mail routed to guest_email. Cancellation includes reference, hotel, reason where supplied and payment state. Refund copy explicitly says this is a fake-system refund record, not a bank transfer. Duplicate successful POSTs do not emit another success transition notice.

The notification center is authenticated, paginated, scoped to the current user, and supports CSRF-protected PATCH mark-as-read. Navigation shows unread count. Booking links continue through existing authorization.

### Development and deployment

Existing Laravel jobs/failed_jobs infrastructure is reused; no Redis or provider dependency was added. For local operation without a worker, configure `QUEUE_CONNECTION=sync` and `MAIL_MAILER=log` in your own environment, then clear cached configuration. No project secrets or actual environment values were changed. Tests use array mail/isolated queues.

For database queues:

```sh
php artisan queue:work --queue=default --tries=3 --timeout=60
php artisan queue:failed
```

Production needs a supervised worker, worker restart on deployment, monitored failures, correct APP_URL/mail configuration and the existing scheduler. Keep worker timeout below the configured retry_after (currently 90 seconds). Changing MAIL_MAILER later does not require business-code changes.

After-commit dispatch is not a durable outbox: a process crash or enqueue failure after commit can lose a notice. Enqueue exceptions are reported rather than returning a false booking failure after a successful commit. Queue delivery is at least once; an SMTP success followed by worker failure can duplicate mail. Monitoring/retry and any stronger delivery guarantees need a deliberate operational decision.

## Voucher and UI flow

`VoucherController` loads booking, hotel, all items and payment from the server. The shared Blade source produces an on-demand downloadable HTML document with print CSS, branding, reference/status, address, guest, dates/nights, every room/board/quantity/adult/child item, total/currency, payment state, cancellation deadline and generation time. Cancelled/refunded states are explicit.

There was no existing PDF dependency. The supported fallback is downloading the HTML voucher and using browser Print / Save as PDF. It is not a binary PDF endpoint, screenshot, pre-stored file, payment receipt or guarantee that a pending booking is confirmed.

Authenticated voucher access uses existing BookingPolicy view rules: owner, supplier of the hotel, admin/superadmin. Another user/supplier receives 403. Guest access requires a valid signed route and a guest booking. Signed links retain the existing end-of-checkout-day expiry. Responses use private/no-store, no-referrer and nosniff headers.

Public flow: Home autocomplete → filtered hotel results → hotel details → availability/booking → checkout/manage → voucher. Authenticated users reach notifications and bookings through navigation. Guest manage/checkout and email contain signed voucher links. Owner email links require authentication; supplier/admin details reuse the same policy-protected voucher route. Existing payment/lifecycle actions are unchanged.

## Fictional media pack

Current report: **0/36 files found, 0 approved valid assets, 0 demo hotels and 0 demo rooms populated, 0 new image relations**. All 21 planned categories are missing. No images were downloaded, generated or replaced by fake image files.

The v2 manifest includes filename/category/source_type/note/license_note/approved. Supported provenance types are generated-demo-asset, local-original and licensed-local; planned records are not approved. Import validates local containment, MIME, file size/dimensions and approval. Missing or invalid assets produce no phantom database references. Legacy approved local manifests remain compatible.

See [the exact 36-file list and import instructions](../resources/demo/README.md). Add reviewed source files under `resources/demo/images/<category>/`, update their truthful metadata, then run `php artisan demo:images`. Mapping is deterministic and shared fictional images are illustrative, not factual geographic/property claims. Populated counts use aggregate queries. Unchanged repeat imports do not duplicate relations.

## Changed files by domain

- Search: `app/Http/Controllers/{PublicHotelController,DestinationController}.php`, `app/Http/Requests/{HotelSearchRequest,AddHotelRequest}.php`, `app/Services/HotelSearchService.php`, `app/Models/Hotel.php`, `database/seeders/DemoSeeder.php`, both new migrations, `routes/web.php`, `resources/views/hotels/{index,show,_search,_filters}.blade.php`, `resources/views/welcome.blade.php`, `resources/views/supplier/hotels/setup/info.blade.php`, booking controller/show prefill.
- Notifications: `app/Enums/BookingNoticeType.php`, `app/Events/BookingActivity.php`, `app/Notifications/{BookingNotice,SendBookingNotice}.php`, `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/NotificationController.php`, booking/payment service event hooks, `resources/views/emails/booking-notice.blade.php`, `resources/views/notifications/index.blade.php`, navigation links and web routes.

### Guest booking recovery and SMTP

Guests can use `Find my booking` with a booking number and email address. A valid guest match receives the existing signed management notification; the response is generic for matches, misses and authenticated-user bookings. The endpoint uses a dedicated IP rate limit and never creates a guest link for a booking with a non-null `user_id`.

For a normal Laravel SMTP transport, set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` in the deployment environment. `MAIL_ENCRYPTION` is accepted for standard `tls`/`ssl` settings and `MAIL_SCHEME` remains supported. Local testing can continue using `log`, `array`, `sync` or the existing fake notification setup; no credentials belong in the repository.
- Voucher: `app/Http/Controllers/Booking/VoucherController.php`, `resources/views/booking/{voucher,manage,checkout}.blade.php`, `routes/booking.php`.
- Media: `app/Console/Commands/DemoImages.php`, `resources/demo/{manifest.json,README.md}`, 21 image category directories containing only `.gitkeep`.
- Tests/profiling: `tests/Feature/{HotelSearchTest,BookingNotificationTest,BookingVoucherTest,DemoMediaPackTest}.php`, `tests/Frontend/search.test.mjs`, `tests/Support/{CreatesBookingScenario,search-profile}.php`; adjusted `ReadinessHardeningTest`, `RouteIntegrityTest` and `readiness-profile.php` for the new unread query, Notification::route false positive and controller signature. `BookingManagementConcurrencyTest` now checks buffered worker READY output, avoiding a Windows waitUntil startup race while retaining the timeout and actual two-worker database-lock assertions.
- Documentation: this report and phase pointers in both previous audit reports.

## Verification

Final execution results are recorded below. Tests cover published/distinct destinations, same-room filters, whole-period/missing inventory, NULL prices, capacity, sorting, pagination/query count, malformed URL input, rollback/actual queue commit behavior, signed email links, notification ownership, voucher ownership/multiple items/cancelled state and missing/invalid/repeated media import. Existing MySQL concurrency tests remain the locking evidence; SQLite tests are not presented as such.

- Frontend: 14/14 Node tests passed (baseline 11).
- Vite production build passed: 58 modules, approximately 41.70 kB CSS / 51.52 kB JS before gzip.
- PHP syntax: 145 files passed; Blade compilation, route cache/clear and git diff --check passed.
- Historical route snapshot for that phase was **90 → 95**. The current final route audit has **84** registered routes; final polish adds only the two guest recovery routes and has no route deletions.
- Both new migrations show Ran in the local MySQL database. Existing data was not reset.
- In-app browser was unavailable (browser list empty), so no manual browser success is claimed. Server-rendered feature tests and Alpine tests cover the flows but do not replace visual browser QA.
- Two full-suite attempts encountered the same legacy Windows worker READY/waitUntil timeout; the isolated expiration test passed. Inspection showed both workers already executing SELECT ... FOR UPDATE while the parent still awaited READY. The test harness now observes already-buffered output. Final rerun result follows.
- Final full PHP suite with `RUN_BOOKING_MYSQL_TESTS=1`: **122 tests / 1,389 assertions, all passed**, including the 10 opt-in MySQL concurrency scenarios. Runtime 1m17s; baseline was 101 tests / 1,219 assertions. No locking assertion was removed or relaxed.

Reproduction commands (PowerShell):

```powershell
$env:RUN_BOOKING_MYSQL_TESTS='1'
php vendor/phpunit/phpunit/phpunit
node --test tests/Frontend/*.test.mjs
php artisan view:cache
npm.cmd run build
php artisan route:list
php tests/Support/search-profile.php
php artisan migrate:status
git diff --check
```

The MySQL suite creates/removes its own randomly named disposable databases and requires local test database privileges. It does not reset the demo database. Full lint covered app, routes, database and tests PHP files. Browser visual QA remains outstanding.

## Remaining findings and decisions

- P0: no new confirmed issue in these implemented paths.
- Supplier account deletion/history cascade P1 is closed by the supplier lifecycle implementation: supplier profile removal deactivates the account and archives owned business data, while restrictive history FKs prevent direct hard-delete cascades. MySQL lifecycle tests cover the retention contract. Personal-data retention/anonymization remains a separate deployment policy decision.
- P2: legacy migration rollback paths include an incorrect historical inventory index/table reference and a nullable-user reversal that cannot succeed with guest records. New tests isolate in-memory databases without invoking these unrelated old down paths. Do not assume a blanket production rollback is safe.
- P2: durable notification delivery, guest link revocation/retention beyond checkout, real email delivery setup, timezone/check-in-time policy, curated media rights and geographically appropriate image mapping remain human/operational decisions. Guest recovery itself is implemented with a generic response and dedicated rate limit.
- P2: direct PDF generation is absent by design; the printable downloadable HTML fallback is documented. Search supports a single room unit for the party, not a multi-room allocation engine.
- Previously documented real payment provider, storage/database atomicity and deployment monitoring obligations remain. This task did not alter Breeze, role rules, FK cascades, cancellation/payment/expiry transitions, availability math, supplier inventory locking, introduce external APIs/search services, or fetch images.
