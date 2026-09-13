> Latest phase (2026-09-13): [Search, notifications, vouchers and fictional media](SEARCH-NOTIFICATIONS-VOUCHERS.md). Historical missing-email/search/voucher findings below are superseded by that report; the supplier lifecycle/cascade P1 is closed by the archive/deactivation implementation and restrictive history FKs.

# BookYourHotel — MVP integration audit

Audit i implementacija: 12.09.2026. Ovo je portfolio MVP sa simuliranim plaćanjima, ne production release.

## Mapa sistema i nalazi pre implementacije

Pregledani su lokalni modeli, migracije, controllers, servisi, policies, middleware, FormRequests, svi route fajlovi, Blade/layout/Livewire/Alpine izvori i postojeći testovi. Laravel Boost je korišćen za informacije o aplikaciji i stvarnu MySQL šemu; `migrate:status` je potvrdio izvršene postojeće migracije.

| Domen | Stvarno stanje pre integracije | Rezultat ovog zadatka |
|---|---|---|
| Auth i role | Breeze radi; user/supplier/admin/superadmin helperi; glavni dashboard auth+verified; User ne implementira MustVerifyEmail | Breeze i middleware role semantika ostaju; navigacija povezana |
| Hoteli | Hotel belongsTo supplier(User), hasMany rooms/images; published flag; supplier CRUD/setup | Public listing/details iz DB, samo published; provera publikacije i na booking endpoint-ima |
| Sobe | Room belongsTo hotel/roomType/bedType; boardTypes pivot sa doplatom; facilities pivot; images i inventory | Ownership/nested hotel-room provere; ispravljen edit pansiona i očuvanje facilities |
| Inventory | Unique(room_id,date); total_units fallback; calendar/bulk/setup endpoint-i | Ownership i validacija setup JSON-a; UI greške i usklađen mesečni query; matematika nije promenjena |
| Booking create | Server cene; inventory lock; sabiranje quantity po sobi; snapshots u items; nullable user_id | Očuvano; novi redirect vodi na checkout; UI unos broja gostiju |
| Booking management | BookingPolicy; owner/supplier/admin prava; signed guest; status recheck pod booking lock-om; restore postojećih inventory redova | Ponovo iskorišćeno, dodat atomic fake refund i payment prikaz |
| Payment | Nema modela, migracije, servisa niti checkout-a | Odvojen Payment lifecycle i fake checkout |
| Public UI | Home je Hello World/test komponenta; nema aktivnog listing/details flow-a | Home → hotels → detail → booking → checkout → rezultat → receipt/manage |
| User UI | Generički dashboard sa izmišljenim brojkama; management dostupan ali slaba navigacija | Dashboard i desktop/mobile navigacija vode na sopstvene rezervacije i profil |
| Supplier UI | Dashboard, pending/confirmed i revenue koriste dummy brojeve; myhotels nema podatke za view | Prave owned booking liste i brojači, ispravan hotels index; revenue izričito odložen |
| Admin UI | admin.dashboard pokazuje nepostojeći layouts.dashboard; sidebar linkovi # | Prava mala management landing stranica; postojeća SuperMiddleware zaštita ostaje |
| Images | Hotel upload helper očekuje Request facade, a dobija UploadedFile; Livewire globalni image ID upiti; dummy kontrole | Ispravan tip fajla, validacija, scoped image upiti i Gate na svakoj relevantnoj akciji; uklonjene aktivne test kontrole |

Važne relacije: Booking belongsTo Hotel i nullable User; hasMany BookingItem i sada hasOne Payment. BookingItem čuva room/board reference i istorijske nazive/cene/datume. RoomInventory nije payment ledger. Hotel facilities su JSON, a room facilities su poseban pivot — nisu nasilno ujedinjeni.

Stvarna početna DB šema potvrđuje nullable `bookings.user_id`, unique booking_number, unique(room_id,date), decimal(10,2) cene i tiny unsigned adults/children. Payment tabela nije postojala. Nova migracija dodaje samo payments; postojeće FK cascade politike nisu menjane. Novi FK payments.booking_id ima podrazumevani NO ACTION, a booking_id je unique.

## Payment arhitektura

- `PaymentStatus`: pending, paid, failed, refunded, nezavisno od BookingStatus.
- Jedan Payment agregat po rezervaciji, enforce-ovan unique indeksom. Kreira se pri prvom POST-u, ne na GET-u niti za stare bookings pri migraciji.
- amount i currency kopiraju se iz zaključane rezervacije; controller nikada ne uzima njihovu vrednost iz browsera.
- reference identifikuje taj payment agregat. attempt broji eksplicitne pokušaje, paid_at/failed_at/refunded_at beleže lifecycle, refund_reference identifikuje jedini fake refund. Nema detaljnog attempt ledger-a; failed_at ostaje poslednje vreme neuspeha i nakon retry-ja.
- `FakePaymentService` sadrži submit/retry/refund operacije, bez gateway framework-a i bez kartica.
- GET checkout je read-only. POST koristi PRG redirect na checkout koji prikazuje rezultat. Paid rezultat se ne menja ponavljanjem POST-a, čak ni ako novi POST traži failure.
- Failed ne može direktno postati paid: zaseban retry POST pravi pending narednog attempt-a. Stari attempt ne može izvršiti novi. To je zaštita od ponavljanja zahteva, nije novi create-booking idempotency sistem.
- `FAKE_PAYMENTS_ENABLED` je opcion; podrazumevano uključen samo u local/testing. U production mora ostati isključen. Nema pravog charging/refund API poziva.

## Lifecycle matrica

| Booking status | Payment operacije | Booking akcije i efekat na payment |
|---|---|---|
| pending | Prvi pokušaj/retry pre check-out-a; uspeh → paid, neuspeh → failed | Staff confirm → confirmed; dozvoljen cancel → cancelled i paid → refunded |
| confirmed | Isto kao pending; potvrda nije dokaz plaćanja | Cancel po postojećim pravima; complete tek od check-out-a; paid ostaje paid |
| cancelled | Nema plaćanja ili retry-ja | Terminal; bez ponovnog refund-a/inventory restore-a |
| completed | Nema plaćanja ili retry-ja | Terminal; nema cancellation/refund-a |
| rejected / expired | Nema plaćanja ili retry-ja | Nasleđeni kompatibilni terminalni statusi |

Nema automatskog confirmation-a posle payment-a. Očuvano je postojeće pravilo da supplier/admin može potvrditi ili završiti i neplaćenu rezervaciju; payment nije dodatan kriterijum za te postojeće tranzicije. Booking cancellation bez payment-a ne pravi payment. Pending/failed payment uz cancelled booking ostaje pending/failed, bez refund timestamp-a — stanje rezervacije zabranjuje nastavak naplate.

## Authorization i signed linkovi

- Običan user vidi i otkazuje samo svoj booking. `BookingPolicy::pay` dopušta payment samo vlasniku, čak i kada je prijavljen supplier/admin; staff vidi tuđi dozvoljeni payment status samo read-only.
- Supplier management query filtrira hotel.supplier_id, a akcije ponovo prolaze BookingPolicy. Admin/superadmin koriste postojeća BookingPolicy prava nad svim bookings.
- SuperMiddleware i dalje dopušta admin.dashboard samo superadmin-u; običan admin ulazi kroz Bookings, bez zaobilaženja tog middleware-a.
- Hotel endpoint-i i Livewire akcije proveravaju HotelPolicy; nested room mora pripadati hotelu iz URL-a. Superadmin hotel view/update pristup usklađen je sa postojećim role middleware bypass-om; nije uvedena nova role.
- Guest koristi posebno potpisane show/submit/retry/manage/cancel URL-ove, samo za booking sa user_id NULL. Guest checkout i management linkovi ističu krajem check-out dana.
- Booking number nije authorization. Potpis pokriva putanju/query, uključujući attempt na payment POST-u. POST body ne može zameniti potpisani attempt.
- Potpisani link je bearer capability: svako kome ga gost prosledi dobija ista guest prava. UI traži čuvanje privatnog management linka. Guest recovery šalje novi potpisani management link samo za booking sa `user_id = NULL`; revocation i retention politika ostaju otvoreni.
- Checkout, guest management i receipt imaju no-referrer i private/no-store response headere. Sve mutation forme imaju CSRF; signed POST nije izuzet od CSRF.
- POST command prihvata samo success/failure simulaciju. Browser bira ishod simulacije, ali ne može direktno zadati proizvoljan payment status ili zaobići lifecycle/authorization provere.

## Transaction i concurrency

Sve payment i booking status operacije najpre učitavaju svež Booking sa `lockForUpdate()` u transakciji i ponovo proveravaju pravo i status. Payment lock sledi booking lock. Unique booking_id je dodatna DB zaštita; sama unique provera ne zamenjuje lock.

Cancellation redosled:

1. Booking lock, authorization, provera da je status pending/confirmed.
2. Owner/guest deadline: najkasnije početak dana pre check-in-a; staff zadržava postojeći bypass tog roka.
3. Lock postojećeg Payment-a; paid postaje refunded uz server timestamp/reference. Za unpaid nema refund-a.
4. Sabiranje količina iz neizmenjenih BookingItems; deterministički room/date redosled; lock svih postojećih inventory redova. Nedostajući red je greška, bez firstOrCreate.
5. Jedno vraćanje inventory-ja, promena booking statusa na cancelled, commit.

Sve se odvija u jednoj DB transakciji. Greška refund zapisa ili inventory restore-a rollback-uje i status i refund i inventory. Sledeći cancellation čita cancelled pod lock-om i ne vraća inventory ponovo. Payment pobedi race → cancel ga refunduje; cancel pobedi → payment se odbija. Transakcije imaju postojeći ograničeni retry za deadlock.

MySQL concurrency testovi koriste dva odvojena PHP procesa i čekaju dokaz `LOCK WAIT` za oba pre otpuštanja zajedničkog booking lock-a. Kreiraju i brišu samo nasumično imenovanu izdvojenu test bazu. SQLite tests nisu predstavljeni kao dokaz row locking-a.

## Kompletni UI tokovi

- Guest: Home → Hotels/search city → published hotel detail → availability → room/board/quantity/adults/children → guest details/review → booking → signed fake checkout → failure/retry ili success → receipt → privatni signed manage/cancel.
- User: login → Dashboard/Bookings → svoje details/payment → checkout ili cancellation → status/refund. Public booking pod prijavljenim nalogom ostaje vezan za njegov user_id.
- Supplier: Supplier dashboard → own hotels/setup/rooms/images/facilities/inventory; Pending/Confirmed liste → postojeći booking details → confirm/cancel/complete kada status/period dozvoljava; payment status read-only za tuđe bookings.
- Admin: Bookings → sve dostupne rezervacije → policy akcije. Superadmin dobija i admin landing. Nema novog CMS-a ili accounting panela.
- Desktop i mobile koriste isti navigation partial. Prazne hotels/bookings liste, validation errors, flash poruke i payment/booking submit guards imaju vidljiv prikaz.

Stari `supplier/bookings/{pending,confirmed,index}` dummy template-i više nemaju aktivnu rutu; njihove rute sada koriste stvarni zajednički booking index. Nisu pretvoreni u paralelan management UI. Dormant Livewire test/supplier-dashboard/room-image demo UI nije aktiviran. Room-image akcije su ipak scoped i autorizovane. Neaktivni HotelController::publish i dalje je stari metod; aktivni publish je HotelSetupController::publishHotel.

## Svesno ostavljen tehnički dug / production zahtevi

- Pravi provider, webhook signature verification, provider idempotency, reconciliation, retry/outbox za eksterni refund i zasebni attempts/refund ledger nisu implementirani. Jedna lokalna transakcija ne može garantovati atomarnost sa budućim eksternim providerom.
- Nema automatskog isteka unpaid rezervacija niti inventory release job-a; pending može držati inventory do otkazivanja. Potrebna poslovna odluka o hold TTL-u.
- Postojeći supplier inventory endpoint-i apsolutno zadaju available. Stari admin/supplier ekran može prepisati novije stanje; nije redizajnirana inventory adjustment semantika. To je važna production prepreka, odvojena od dokazane booking/payment concurrency zaštite.
- Pre supplier lifecycle taska, FK cascade na hotel/room/board relacijama i masovni query delete mogli su zaobići Booking deleting event. Aktuelni supplier/historical FK ugovor i archive/deactivate lifecycle opisani su u `docs/supplier-lifecycle.md`; pomoćne katalog/image kaskade ostaju dozvoljene za maintenance brisanje unused zapisa.
- `Room::bookings()` je zastarela direktna relacija na uklonjeni bookings.room_id; nema aktivnog korisnika te relacije. Potrebno zasebno uklanjanje ili zamena preko items.
- User ne implementira MustVerifyEmail; middleware verified sam po sebi ne nameće verifikaciju u tom stanju. Breeze/role sistem nije redizajniran.
- Cene i dalje koriste postojeći decimal DB/float obračun, bez money-in-cents refaktora. Production zahteva jasna rounding/tax/currency pravila i numeričke granice svih aggregate totals.
- Image fajlovi i DB nisu jedan transaction resource; upload/delete/featured operacije zahtevaju naknadnu obradu orphan fajlova i stroži concurrency invariant za featured image. Nije uveden media subsystem.
- Guest signed-link e-mail dostava, oporavak izgubljenog linka, revocation, abuse/rate-limit politike, privacy/retention/legal tekstovi i audit trail osoblja nedostaju.
- Revenue/payout/accounting je izričito odložen; fake paid iznosi nisu stvarni prihod.
- Za release treba zasebno proveriti HTTPS, secure cookies, APP_DEBUG=false, secrets, proxy trust, mail/queue/scheduler, backup/restore, monitoring, dependency audit i storage deployment. Prolaz testova nije production garancija.

## Provere

Komande za ponavljanje:

```powershell
php vendor/phpunit/phpunit/phpunit
node --test tests/Frontend/booking.test.mjs tests/Frontend/checkout.test.mjs tests/Frontend/inventory.test.mjs
$env:RUN_BOOKING_MYSQL_TESTS = '1'
try { php vendor/phpunit/phpunit/phpunit tests/Feature/BookingManagementConcurrencyTest.php } finally { Remove-Item Env:RUN_BOOKING_MYSQL_TESTS }
php artisan route:list
php artisan migrate:status
php artisan view:cache
npm.cmd run build
git diff --check
```

Finalni rezultati:

- Ceo PHP suite sa `RUN_BOOKING_MYSQL_TESTS=1`: **79 testova, 1052 assertions, sve prolazi, bez skipped testova**. Obuhvata postojeći create/availability, management, Breeze/profile, nove payment/security/route/Livewire testove i pet MySQL concurrency scenarija.
- Zaseban MySQL concurrency run: **5 testova / 49 assertions**, sve prolazi. Ukupni suite već uključuje ovih pet testova; nisu dvaput uračunati.
- Frontend Node: **8 testova**, sve prolazi (booking reset/stale response/submit, checkout duplicate guard, inventory load/save errors).
- Historical route snapshot for that integration phase was **81 pre → 89 after**. The current final route audit has **84** registered routes; there are no route deletions in the final polish diff, and the route integrity test passes.
- PHP syntax: **62 promenjena/nova PHP i Blade fajla**, bez sintaksnih grešaka. `view:cache`, `git diff --check` i Vite build uspešni.
- Nova payments migracija izvršena na lokalnom MySQL-u; Boost potvrđuje unique booking_id/reference/refund_reference i NO ACTION FK. Nema pending migracija. Fresh migracije uspešne i na SQLite i na izdvojenim MySQL bazama.
- Browser na izdvojenoj SQLite bazi: Home → listing → details → availability → izbor sobe i dva gosta → create guest booking → failed payment → explicit retry → paid → receipt → signed management → cancellation → Refunded. Proveren stvarni native form submit sa CSRF, kao i vizuelni dark checkout. Supplier login/navigacija dodatno provereni; role/status kombinacije pokriva feature suite. Browser test nije zamena za MySQL concurrency dokaz.
- `npm` PowerShell shim blokiran execution policy-jem; korišćen `npm.cmd`. Sandbox je blokirao esbuild/Node spawn sa EPERM, pa su build/test komande uspešno ponovljene uz odobreno izvršavanje van sandbox-a.

Vreme rokova koristi postojeći `config('app.timezone')` (u pregledanom okruženju UTC), prikazano u UI-ju. Nije uvedeno zasebno check-in vreme niti hotelska vremenska zona.

## Promenjeni i dodati fajlovi

### Payment i booking

- `.env.example`
- `app/Enums/PaymentStatus.php`
- `app/Http/Controllers/Booking/BookingController.php`
- `app/Http/Controllers/Booking/BookingManagementController.php`
- `app/Http/Controllers/Booking/GuestBookingController.php`
- `app/Http/Controllers/Booking/PaymentController.php`
- `app/Http/Requests/CreateBookingRequest.php`
- `app/Models/Booking.php`
- `app/Models/Payment.php`
- `app/Policies/BookingPolicy.php`
- `app/Services/BookingService.php`
- `app/Services/FakePaymentService.php`
- `config/payments.php`
- `database/migrations/2026_09_12_000001_create_payments_table.php`
- `resources/views/booking/checkout.blade.php`
- `resources/views/booking/index.blade.php`
- `resources/views/booking/manage.blade.php`
- `resources/views/booking/show.blade.php`
- `resources/views/booking/success.blade.php`

### Public, navigacija i admin

- `app/Http/Controllers/PublicHotelController.php`
- `resources/css/app.css`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/hotels/index.blade.php`
- `resources/views/hotels/show.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/footer.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/partials/navigation-links.blade.php`
- `resources/views/layouts/partials/sidebar-admin.blade.php`
- `resources/views/welcome.blade.php`

### Supplier i hotel setup

- `app/Http/Controllers/RoomSetupController.php`
- `app/Http/Controllers/Supplier/HotelController.php`
- `app/Http/Controllers/Supplier/HotelSetupController.php`
- `app/Http/Controllers/Supplier/RoomController.php`
- `app/Http/Controllers/Supplier/RoomInventoryController.php`
- `app/Http/Controllers/SupplierController.php`
- `app/Http/Requests/AddHotelRequest.php`
- `app/Http/Requests/RoomRequest.php`
- `app/Models/Hotel.php`
- `app/Models/HotelImage.php`
- `app/Policies/HotelPolicy.php`
- `app/Traits/HandleImagesUpload.php`
- `resources/views/livewire/supplier/⚡hotel-images-manager.blade.php`
- `resources/views/livewire/supplier/⚡room-images-manager.blade.php`
- `resources/views/supplier/dashboard.blade.php`
- `resources/views/supplier/hotels/index.blade.php`
- `resources/views/supplier/hotels/setup/_steps.blade.php`
- `resources/views/supplier/inventory/calendar.blade.php`
- `resources/views/supplier/revenue.blade.php`
- `resources/views/supplier/rooms/edit.blade.php`
- `resources/views/supplier/rooms/index.blade.php`
- `resources/views/supplier/rooms/setup/images.blade.php`
- `resources/views/supplier/rooms/setup/inventory.blade.php`

### Rute

- `routes/admin.php`
- `routes/booking.php`
- `routes/supplier.php`
- `routes/web.php`

### Testovi i dokumentacija

- `docs/MVP-INTEGRATION.md`
- `tests/Feature/BookingFlowTest.php`
- `tests/Feature/BookingManagementConcurrencyTest.php`
- `tests/Feature/MvpIntegrationTest.php`
- `tests/Feature/PaymentFlowTest.php`
- `tests/Feature/RouteIntegrityTest.php`
- `tests/Frontend/checkout.test.mjs`
- `tests/Frontend/inventory.test.mjs`
- `tests/Support/booking-management-worker.php`
