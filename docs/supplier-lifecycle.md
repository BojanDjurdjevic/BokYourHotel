# Supplier / hotel / room lifecycle

## Pre-change audit (2026-09-13)

Verified using Laravel Boost against MySQL book_your_hotel after starting the existing Laragon MySQL instance. No SoftDeletes exist. Supplier is a users row (role=supplier), not a separate table. Refund fields live on payments; vouchers are rendered booking views, not separate records.

| Parent → child | Before ON DELETE | Intended after |
| --- | --- | --- |
| users → hotels.supplier_id | CASCADE | RESTRICT |
| hotels → rooms.hotel_id | CASCADE | unchanged |
| hotels → bookings.hotel_id | CASCADE | RESTRICT |
| users → bookings.user_id | SET NULL | unchanged |
| bookings → booking_items.booking_id | CASCADE | RESTRICT |
| rooms → booking_items.room_id | CASCADE | RESTRICT |
| board_types → booking_items.board_type_id | CASCADE | RESTRICT |
| bookings → payments.booking_id | NO ACTION | unchanged |
| room_types / bed_types → rooms | RESTRICT | unchanged |
| hotels → hotel_images | CASCADE | unchanged |
| rooms → room_images / room_inventories / room_board_types / facility_room | CASCADE | unchanged |
| board_types → room_board_types | CASCADE | unchanged |
| facilities → facility_room | CASCADE | unchanged |

Hotel facilities are JSON, room facilities use facility_room. Cascaded image DB deletion does not itself clean filesystem assets.

The findings in the next list are the pre-change audit results. They describe the unsafe behavior that this lifecycle implementation replaces; they are not the current supplier account behavior.

Pre-change outcomes:
1. Profile deletion logs out then hard-deletes user: owned hotels, rooms and bookings cascade. Payments cause a statement rollback/error; without payments, history disappears. Own guest bookings instead keep user_id=NULL.
2. Hotel destroy endpoint returns 405; direct hotel deletion cascades rooms, images, inventories, bookings and items, unless a payment restricts it.
3. Room destroy endpoint returns 405; direct room deletion removes items even on paid bookings (booking/payment remain, incomplete).
4. Board type has no destroy endpoint; direct deletion cascades pivots and historical items, including paid bookings.
5. Future bookings have no database lifecycle guard: same hotel cascade/restrict outcome.
6. Completed bookings have the same outcome; status does not affect FK behavior.
7. Supplier with paid/refunded payments gets a DB error; the payment FK prevents booking deletion, but is not a coherent account lifecycle.

Booking deleting model event blocks direct Eloquent deletes only; database cascades bypass it. HotelPolicy checks ownership, permits superadmin view/update, and has no archived-state guard.

## Implementation contract

Explicit archived_at on hotels, rooms and board_types; supplier_deactivated_at on users. No global soft-delete scope: historical belongsTo relations continue resolving. Hotel rooms() and room boardTypes() represent active sale catalog; historical items resolve room()/boardType() directly. Supplier hotel lists include archived records with a label; archived records cannot be edited or republished. Active suppliers still access historical booking management.

User-facing hotel/room removal always archives, including unused drafts. No automatic hard-delete or restore endpoint. Administrative maintenance may hard-delete an unused hotel/room/board type only where restrictive FKs permit it; supplier rows owning hotels cannot be hard-deleted. Auxiliary inventory/media/pivot cascades remain valid for unused catalog records.

Any pending or confirmed booking blocks hotel/room archive and supplier deactivation, regardless of payment or dates (includes in-progress and overdue unresolved bookings). Expired holds must be explicitly processed by the existing expiry flow. No automatic cancellation, refund or completion. Terminal completed/cancelled/expired history is retained.

Supplier profile removal deactivates business access and atomically archives owned hotels and rooms, retaining the account and ownership. Guest account deletion retains the existing SET NULL behavior. This is business-access deactivation, not personal-data erasure.

Current verification: the active MySQL schema has `RESTRICT` for `hotels.supplier_id`, `bookings.hotel_id`, and all three historical `booking_items` parent references. `bookings.user_id` is `SET NULL`, and `payments.booking_id` remains `NO ACTION`. `ProfileController` routes supplier profile removal through `SupplierLifecycleService::deactivateSupplier()`, so it does not hard-delete the supplier account or cascade through its business history. The lifecycle MySQL suite passes, including direct deletion restrictions and preservation of booking/payment/voucher history. The remaining `rooms.hotel_id` `CASCADE` is limited to auxiliary catalog cleanup when an unused hotel is explicitly removed through maintenance; it is not the supplier account deletion path.

Existing room_name, board_name, quantity, adults, children, price_per_night, subtotal, nights, dates and currency snapshots already represent the purchased agreement and are populated server-side. Voucher and management already use them. No JSON dump, new snapshot columns, or backfill of guessed historical values. Room-type taxonomy/capacity are not substituted for the purchased room name and booked guest counts.

Production decisions: distinguish account access removal, personal-data anonymization and legally required business retention; define jurisdiction-specific retention periods and responsible operator for unresolved supplier departures. Hotel identity/address on vouchers currently represents retained property context, not an independently versioned legal document. Broader immutable financial ledgers and retention enforcement are outside this task.

## Authorization and concurrency

Supplier operations remain owner-only. Superadmin retains existing hotel management access, including archive; neither supplier nor superadmin can edit an archived hotel. Admin booking/payment management remains unchanged; this task does not grant admin new supplier routes. Room editing, images, facilities and inventory reject archived rooms, including direct URLs. Deactivated accounts cannot log in and existing sessions are rejected on their next web request. Password resets do not reactivate business access.

Booking creation and hotel/room archive serialize on the hotel row with lockForUpdate. Supplier deactivation locks its user row then owned hotels in ID order, checks unresolved bookings and archives atomically. Hotel creation locks the same user row to prevent creation after deactivation. Inventory writes recheck lifecycle after obtaining the hotel lock. Existing booking confirmation/cancellation/payment semantics remain unchanged.

Board retirement is an admin-only service operation (no new catalog admin UI). It prevents new selection but preserves active reservations and their snapshots; unlike retiring a property/room, it does not withdraw accommodation or block servicing an existing reservation. Room/bed types keep existing RESTRICT rules and no new lifecycle UI. Removing room-board/facility pivots never deletes a booking item.

## Migration and development verification

Only new migration: `database/migrations/2026_09_13_000001_preserve_supplier_business_history.php`. Adds three nullable indexed archived_at columns and one nullable supplier_deactivated_at column, then replaces five destructive business-history CASCADE keys with RESTRICT. It neither rewrites commercial snapshots nor deletes rows. `down()` restores original constraints and removes only the added fields/indexes. Rolling back also removes lifecycle metadata/protection: use coordinated application rollback, not rollback as a restore/unarchive feature.

Applied `php artisan migrate --pretend --force` then `php artisan migrate --force` against the existing development MySQL database. No migrate:fresh. Boost verified all 17 resulting FK rules. Before/after counts match: users 34, hotels 101, rooms 454, bookings 406, booking_items 406, payments 300. Application tests compare complete historical payment and item attributes across archive. Migration down/up is exercised only in disposable MySQL databases with existing test history.

MySQL DDL implicitly commits: deploy this migration with application writes paused so no requests run between FK replacement statements. The rollback restores the original unsafe cascade rules intentionally, matching the previous schema; it is not a production retention strategy.

## Changed files

- `app/Http/Controllers/Booking/BookingController.php`
- `app/Http/Controllers/DestinationController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/PublicHotelController.php`
- `app/Http/Controllers/RoomSetupController.php`
- `app/Http/Controllers/Supplier/HotelController.php`
- `app/Http/Controllers/Supplier/RoomController.php`
- `app/Http/Middleware/EnsureSupplierAccountActive.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/CreateBookingRequest.php`
- `app/Http/Requests/RoomRequest.php`
- `app/Models/BoardType.php`
- `app/Models/Hotel.php`
- `app/Models/Room.php`
- `app/Models/User.php`
- `app/Policies/HotelPolicy.php`
- `app/Services/BookingService.php`
- `app/Services/HotelSearchService.php`
- `app/Services/InventoryService.php`
- `app/Services/SupplierLifecycleService.php`
- `bootstrap/app.php`
- `database/migrations/2026_09_13_000001_preserve_supplier_business_history.php`
- `docs/supplier-lifecycle.md`
- `resources/views/profile/partials/delete-user-form.blade.php`
- `resources/views/supplier/hotels/index.blade.php`
- `resources/views/supplier/rooms/index.blade.php`
- `tests/Feature/SupplierLifecycleMySqlTest.php`
- `tests/Feature/SupplierLifecycleTest.php`
- `tests/Support/supplier-lifecycle-worker.php`

## Final test results

- `php artisan test --compact`: 126 passed, 26 opt-in MySQL tests skipped, 1419 assertions.
- `RUN_LIFECYCLE_MYSQL_TESTS=1 php artisan test --compact --filter=SupplierLifecycleMySqlTest`: 16 passed, 144 assertions. Covers the same lifecycle HTTP/history contract on InnoDB plus down/up with existing rows and a proven InnoDB lock-wait race between booking creation and archive.
- `RUN_BOOKING_MYSQL_TESTS=1 php artisan test --compact --filter=BookingManagementConcurrencyTest`: 10 passed, 109 assertions. Existing cancellation, confirmation, expiry, payment, refund and inventory concurrency invariants remain green.
- Combined: all 152 tests passed in their appropriate runs (1672 assertions); the 26 opt-in skips in the default run were executed separately above.
- `git diff --check` and PHP syntax checks for new migration/service/MySQL tests passed. Supplier lists, archive errors, direct URL denial, historical management/voucher and public search are exercised by feature tests; no manual browser visual QA was performed.

The first broad run encountered a transient existing DemoMediaPackTest Windows directory-cleanup failure, which prevented normal database teardown and caused secondary transaction errors. Its isolated rerun and the final complete suite both passed without modifying that unrelated test.
