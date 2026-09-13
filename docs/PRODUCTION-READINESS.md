> Latest phase (2026-09-13): [Search, notifications, vouchers and fictional media](SEARCH-NOTIFICATIONS-VOUCHERS.md). Historical missing-email/search/voucher findings below are superseded by that report; the supplier lifecycle/cascade P1 is closed by the archive/deactivation implementation and restrictive history FKs.

# Production readiness — baseline i plan

Datum audita: 2026-09-12. Aplikacija nije proglašena production-ready.

## Početni baseline

- Čist working tree pre zadatka. Laravel 12, PHP 8.3, MySQL; Breeze, Blade/Livewire/Alpine/Tailwind.
- Current final route:list audit: **84 registered routes**. The earlier 89-route figure is a historical phase baseline; final polish adds two guest recovery routes and removes none.
- Lokalna baza: 1 hotel, 4 sobe, 61 inventory red, 6 bookings, 0 payments. Postojeći razvojni podaci neće biti brisani.
- Controller/service + Blade render, bez HTTP/session overhead-a, učitan actor van merenja: public listing 3; supplier hotels 7; supplier dashboard 3; supplier bookings index 4; public details 6; availability 6 upita. Milisekunde nisu stabilan performance kriterijum.
- Postoje unique booking_number, payment booking_id/reference/refund_reference, inventory(room_id,date), room-board i room-facility indeksi. Bookings već ima (hotel_id,status); user_id i parent FK indeksi postoje. Inventory ima redundantni običan (room_id,date) pored unique indeksa; nema potrebe za novim identičnim indeksom.

## Početni nalazi

| Prioritet | Nalaz | Plan |
|---|---|---|
| P0 | Za sada nije potvrđen novi P0 u aktivnim flow-ovima | Ne izmišljati nalaze; dopuniti posle testova |
| P1 | Supplier single/bulk/setup apsolutno prepisuje dostupnost bez stale-write provere | Version check pod inventory lock-om; setup samo inicijalizuje nepostojeće datume |
| P1 | Pending unpaid booking ne ističe | locked_until = 30 min; transaction-safe expiry command/scheduler; isti booking lock kao payment |
| P1 | Supplier hotels nema pagination i setupChecklist/featuredImage stvaraju N+1 | Meriti na demo bazi pa paginate + withExists/eager loading |
| P1 | Public create nema granicu broja items ni rate limit; inventory bulk period nije ograničen | Razumne request granice i Laravel limiters |
| P1 | Upload storage write rezultat se ignoriše; DB greška može ostaviti orphan; room fajl nije normalizovan | Provera write-a, WebP normalizacija, kompenzacioni cleanup i scoped path |
| P2 | FK cascade i query-level delete mogu zaobići Booking deleting event | Dokumentovati; ne menjati masovno FK politiku |
| P2 | Room::bookings je zastarela direktna relacija | Lokalna korekcija samo ako test pokaže bezbedan put preko items |
| P2 | MustVerifyEmail nije implementiran; guest-link revocation/retention i SMTP/queue operativa traže deployment odluku | Recovery flow postoji bez otkrivanja booking existence; bez Breeze redizajna |
| P2 | Pravi provider, webhook/reconciliation, detaljan financial ledger ne postoje | Van scope-a |
| P2 | Postoje dormant demo template-i/metode i external Sortable CDN | Ne aktivirati; bez širokog style refaktora |

## Plan po domenima

1. `database/seeders`, demo command/config/assets manifest: 50 destinacija, 100 fictional hotela, 3–6 soba, 365 dana inventory-ja, 400 bookings; bulk insert; lokalni marker i sigurno ponavljanje bez resetovanja korisničkih izmena. Nema internet slika.
2. Meriti isti veliki dataset pre/posle optimizacije; sačuvati harness i EXPLAIN rezultate. `Hotel`/supplier controllers/views: pagination i uklanjanje potvrđenih N+1.
3. Mala nova migracija: inventory version i expiry scan indeks; `InventoryService` za kontrolisan stale-write model. Booking decrement/restore uvećavaju version. Single/bulk UI šalje pročitane verzije; bulk preview je obavezan. Setup ne sme prepisivati postojeći datum.
4. `BookingService`, `FakePaymentService`, command i scheduler: 30-min hold samo pending unpaid; expiry vraća inventory tačno jednom. Postojeći pending dobijaju rollout grace od 30 minuta, ne automatsko trenutno otkazivanje. Confirm ne sme zaobići istekao unpaid hold.
5. Laravel rate limiters i male request/upload provere; bez nove infrastrukture. Za postojeće P2 koje zahtevaju poslovnu odluku ostaje predlog.
6. Proširiti feature/frontend/MySQL tests, izvršiti ceo suite, migration/cache/build/syntax/audits i browser smoke. Dependency audit zavisi od mrežnog pristupa, rezultat će biti jasno naveden.

## Ljudske odluke pre pravog deployment-a

- Pravi payment provider i refund/reconciliation protokol.
- Politika čuvanja istorije i lifecycle hotela/soba/board type-ova pre menjanja FK kaskada.
- Verify-email, guest-link revocation/retention, privatnost i stvarna SMTP/queue dostava.
- Hotelske vremenske zone i eventualno konkretno check-in vreme; sada rok koristi app timezone.
- Deployment scheduler, queue, HTTPS, secure cookies, trusted proxies/hosts, secrets, backups i monitoring.
- Licenciran lokalni image pack: ovaj zadatak ne preuzima fotografije i ne izmišlja attribution.

## Završna mapa sistema

Audit je koristio Laravel Boost (stvarna MySQL šema, read-only SQL i EXPLAIN), kod, renderovanje Blade-a i testove. Nije korišćena SQLite kao dokaz zaključavanja.

| Domen | Stvarna implementacija i granice |
|---|---|
| Identitet | Breeze controllers/FormRequests, User role helpers, HotelPolicy/BookingPolicy, RoleMiddleware, SuppMidleware i SuperMiddleware. Registracija ne prima privilegovanu rolu. Glavni dashboard ostaje auth + verified; User još ne implementira MustVerifyEmail. |
| Katalog | Hotel pripada supplier User-u; ima Rooms, HotelImages i JSON hotel facilities. Room ima RoomType/BedType, RoomImages, inventory i board/facility pivot relacije. PublicHotelController prikazuje samo published hotele. Listing paginate(12), detalji učitavaju sobe sa potrebnim relacijama. |
| Supplier | HotelController/HotelSetupController/RoomController, setup koraci, postojeći inventory calendar i grid; katalog soba/hotela sada paginate(12). Bookings dashboard/pending/confirmed već koriste stvarnu bazu. |
| Booking | BookingController + CreateBookingRequest + BookingService/AvailabilityService. BookingItems čuvaju snapshots naziva, cena, količina i datuma. Server određuje cene, user_id i status; availability sabira quantity po room_id kroz različite board types. |
| Management | BookingManagementController + GuestBookingController, BookingPolicy, potpisane guest rute. User vidi svoje, supplier hotele koje poseduje, admin/superadmin odgovarajuće bookings. Supplier nema payment write privilegije. |
| Payment | FakePaymentService, PaymentController, Payment i odvojen PaymentStatus. Jedan payment po booking-u, eksplicitni attempt/retry; nema kartica/provider-a. Iznos i valuta dolaze iz zaključanog booking-a. |
| Media | Hotel/room upload koristi postojeći GD/Intervention pristup, lokalni public storage, UUID WebP putanje. Livewire akcije imaju Gate i scoped image lookup. Demo importer koristi isti image domain. |
| UI | Blade/Alpine i Livewire single-file komponente; postojeći layouts, Tailwind dark mode, role navigation. Public → listing → detalji → availability → booking → fake checkout → success/manage i auth management ostaju povezani. |
| Pozadinski rad | Novi bookings:expire u routes/console.php, svakog minuta. Ostaje sinhron lokalni fake payment/refund; nije potrebna nova queue infrastruktura za ove operacije. |

Nisu aktivirani dormant placeholder template-i ili stare metode. `Room::bookings()` je zastarela direktna relacija; aktivan booking model koristi BookingItems. `SetHotelInventory`/`HotelService::createInventory()` ostali su neaktivni legacy put sa upsert-om: ne uključivati ih ponovo bez preusmeravanja na InventoryService. Svi aktivni single/bulk/setup putevi koriste novi servis. Ovo je dokumentovan P2, ne široko čišćenje koda.

## Demo dataset i pokretanje

```powershell
php artisan migrate
php artisan demo:seed
# Za reprodukciju početnog vremenskog prozora, samo pri prvom seed-u:
php artisan demo:seed --date=2026-09-12
# Posle dodavanja lokalnih odobrenih slika:
php artisan demo:images
```

| Demo entitet | Generisano |
|---|---:|
| Destinacije / fictional hoteli | 50 / 100 |
| Supplier nalozi / svi demo nalozi | 10 / 33 |
| Room konfiguracije / room types / board types | 450 / 6 / 3 |
| Inventory ukupno | 191.250 |
| Inventory narednih 365 dana | 164.250 |
| Inventory prethodnih 60 dana | 27.000 |
| Booking / BookingItems / Payment | 400 / 400 / 300 |
| Guest / authenticated bookings | 200 / 200 |
| Importovane image relacije | 0 — asset pack nije prisutan |

Ukupna lokalna baza posle dodavanja: 101 hotel, 454 sobe, 191.311 inventory redova, 406 bookings i 300 payments. Postojeći razvojni podaci nisu brisani. Anchor prvog lokalnog run-a je 2026-09-12.

Imena su `Demo Aurelune House <city>` i `Demo Veloria Terrace <city>`, adrese/opisi eksplicitno fictional. Postoje weekend/sezonske cenovne razlike, različiti kapaciteti/jedinice, povremeni sold-out dani i tri board doplate. Sezona je deterministična ilustracija, ne tržišni pricing model za svaku destinaciju.

Scenario ciklus ima pending+paid, confirmed+paid, completed+paid, cancelled+refunded, pending bez payment-a, pending+failed, expired+failed, rejected bez payment-a. Aktivni i završeni boravci smanjuju odgovarajuće inventory dane; otkazani/istekli/odbijeni predstavljaju već oslobođene istorijske rezervacije. Iznosi i valute su usklađeni sa items/inventory/board podacima.

Inventory insert-i su bulk: 450 SQL insert operacija za 191.250 redova, ne po jedan Eloquent insert za svaki dan. Manji broj Eloquent izmena za konkretne booking dane je nameran. Seeder radi u transakciji; `demo_seed_runs` marker sprečava ponovno generisanje. Ponovljena komanda ne resetuje datume, password-e, status, payment, inventory ili korisničke izmene. Sudar demo email namespace-a prekida seed bez overwrite-a. Ne poziva migrate:fresh i nije deo automatskog DatabaseSeeder-a.

Reproducibilnost podrazumeva isti anchor i iste formule, ne iste auto-increment ID-jeve, password hash ili timestamps. Ponovno pokretanje ne produžava prozor inventory-ja niti hold rezervacija. Za nov čist benchmark koristiti zasebnu lokalnu/testing bazu. Ne brisati marker radi resetovanja postojeće baze. Demo pending holds vremenom normalno ističu.

Svi poznati nalozi ispod imaju lokalni password **`Demo-Local-2026!`**:

| Rola | Email |
|---|---|
| User | `user@demo.bookyourhotel.test` |
| Supplier | `supplier01@demo.bookyourhotel.test` do `supplier10@demo.bookyourhotel.test` |
| Admin | `admin@demo.bookyourhotel.test` |
| Superadmin | `superadmin@demo.bookyourhotel.test` |
| Dodatni user-i | `traveler1@demo.bookyourhotel.test` do `traveler20@demo.bookyourhotel.test` |

Komande odbijaju okruženja izvan local/testing; sam DemoSeeder takođe proverava environment. Ovi javno poznati nalozi nisu production credentials i ne smeju dospeti u produkcionu bazu. Ne postavljati javni server na APP_ENV=local radi omogućavanja demo komande.

### Lokalni image pack

`resources/demo/README.md`, `manifest.json`, `images/<category>/` definišu exterior, lobby, standard-room, deluxe-room, suite, bathroom, pool, restaurant, spa, city-view. Predviđeno je 30–40 source fajlova. Manifest ima filename/category/source/author/source_url/license_note/approved. Source URL je opcion attribution, nikada instrukcija za download.

Import prihvata samo odobrene lokalne JPEG/PNG/WebP, do 8 MB i 40 MP, proverava realpath unutar image root-a i kategoriju/licencnu napomenu. Nedostajući/neodobreni fajlovi se preskaču. Nema remote placeholder URL-ova ni internet preuzimanja. Različiti hoteli/sobe dobijaju odvojene storage putanje zasnovane na hash-u sadržaja; brisanje jedne kopije ne dira tuđi hotel. Ponovljeni import ne duplira istu image relaciju. Za efikasan prikaz preporučeni pack treba pripremiti u odgovarajućim dimenzijama, ne koristiti gornju granicu kao cilj veličine.

## P0/P1/P2 završni nalazi

| Prioritet | Nalaz / ishod |
|---|---|
| P0 | Nije potvrđen nov P0 exploit u aktivnom application flow-u. Dependency audit je imao i critical advisory u build alatu; zakrpljen, nije predstavljen kao dokaz udaljenog exploit-a aplikacije. |
| P1 zatvoren | Supplier stale absolute inventory write: sada version check pod row lock-om, atomic bulk, create-only setup. |
| P1 zatvoren | Pending unpaid neograničen hold: sada 30-min deadline, server expiry, idempotentan restore i payment/expiry serialization. |
| P1 zatvoren | Create/create MySQL deadlock na insertOrIgnore davao je neobrađenu DB grešku: potvrđeno novim testom pre izmene; create transakcija sada ima najviše tri Laravel pokušaja. |
| P1 zatvoren | Supplier hotels N+1 i neograničene hotel/room liste: eager loading/withExists i pagination; 73 → 3 upita za hotels. |
| P1 zatvoren | Neograničen broj booking items / bulk inventory redova i odsustvo abuse limita: max 20 items, max 366 dana/reda i ciljane Laravel rate limits. |
| P1 zatvoren | Upload write failure, orphan pri neuspelom DB insert-u i raw room upload: kontrola write-a, cleanup, MIME/veličina/piksel granice, WebP i scoped putanje. |
| P1 zatvoren | Poznate dependency advisories u instaliranim paketima: Composer 40 → 0; pnpm 49 → 0 na dan audita. |
| P1 zatvoren | Supplier profile removal sada deactivates the supplier and atomically archives owned hotels/rooms; restrictive supplier, hotel and booking-item history FKs prevent direct hard-delete cascades. MySQL lifecycle tests potvrđuju blokadu direktnog brisanja i očuvanje booking/payment/voucher istorije. |
| P2 ostaje | Neaktivne legacy metode/relacije, javni details/availability učitavaju sve room konfiguracije jednog hotela, image galerije nisu globalno ograničene po hotelu. Za 3–6 konfiguracija nema potvrđenog N+1; za ogromne pojedinačne hotele treba posebno definisati granice/UI. |
| P2 ostaje | City `%term%` filter i određena sortiranja rade filesort; na izmerenom datasetu nije opravdano dodavanje novih search indeksa/infrastrukture. |
| P2 ostaje | Filesystem i DB nisu jedna transakcija: disk-delete pa DB-delete failure može ostaviti nevažeću image referencu; batch upload može biti delimično uspešan. Nema orphan reconciliation job-a ni storage quota. |
| P2 ostaje | Featured image/order concurrent izmene nisu posebno serializovane; nema jedinstvenog featured indeksa. Nije booking/payment integritet. |
| P2 ostaje | E-mail verifikacija, guest-link revocation/retention, privacy retention i concrete hotel timezone nisu završeni. Guest recovery sada postoji uz generički odgovor i rate limit; stvarna SMTP/queue operativa ostaje deployment obaveza. |
| P2 ostaje | Floating-point money računanje nije money-in-cents refaktorisano. Fake payment ostaje portfolio simulator, ne payment ledger/provider. |

## Performance i indeksi

Reprodukcija: `php tests/Support/readiness-profile.php`. Harness je read-only, local/testing; meri controller/service + Blade render, actor lookup izvan merenja, uključuje fresh model lookup gde je naveden. Ne predstavlja HTTP requests/sec benchmark, ne uključuje reverse proxy/network/session middleware. Time vrednosti zavise od host-a i cache zagrevanja; nisu assertions.

| Flow | Mala baza baseline | Velika baza pre optimizacije | Velika baza posle |
|---|---:|---:|---:|
| Public listing | 3 | 3 | 3 |
| Supplier hotels | 7 | 73 | 3 |
| Supplier dashboard | 3 | 3 | 3 |
| Supplier bookings index | 4 | 4 | 4 |
| Public hotel details | 6 | 6 | 6 |
| Availability, 3 noći | 6 | 6 | 6 |

Brojevi su query counts. Veliki supplier uzorak ima 10 hotela. Posle: supplier rooms 7 (uključuje setup checklist), inventory calendar 2, pending/confirmed liste 4/4, booking details supplier 5/user 4, user bookings 4, user checkout 2, admin bookings 4. Dodatni demo hotel details render takođe 6. Testovi sa 16 hotela i 16 room konfiguracija proveravaju 12 zapisa po strani i stabilan query plafon; ne samo trajanje. Nedovršeni hotel checklist indikator sada se odnosi na trenutnu stranu paginator-a.

Jedini novi query indeks je **bookings(status, locked_until, id)** za expiry scan. Boost EXPLAIN na 406 bookings: range preko `bookings_expiry_scan_index`, procena 155 kandidata, Using index; id ordering i dalje može koristiti filesort. Skenira se po 200 ID-jeva, svaki se ponovo proverava pod lock-om.

Postojeći unique inventory(room_id,date) je izabran kao range/index condition za 3 sobe × 3 dana, procena 9 redova. Existing bookings(hotel_id,status) je izabran za supplier/status filter, procena 1 red za uzorak. `user_id`, `supplier_id`, room/hotel/booking FK indeksi i payment unique booking_id ostaju. Payments status nije zasebno pretraživan velikom listom, pa mu nije dodat indeks.

Hotel listing je na 101 hotelu koristio ALL + filesort; probni (published,name) indeks nije poboljšao odabrani EXPLAIN plan i nije zadržan. Nema nove takve migracije. Redundantni plain inventory(room_id,date) pored unique indeksa je dokumentovan i ostavljen; nisu dodavani identični indeksi. Nisu obećani latency/throughput rezultati bez pravog HTTP load testa i produkcionog hardvera.

## Inventory concurrency ugovor

Svaki postojeći inventory red ima monotono rastući `version`, početno 1. Snapshot nepostojećeg dana ima version 0. Single-day i bulk UI šalju pročitanu verziju; bulk prvo traži eksplicitan preview od najviše 366 dana. Promena selekcije/željene cene ili količine zahteva novi preview.

InventoryService autorizuje vlasništvo, validira sve redove, sortira datume, materijalizuje nedostajuće redove, zaključava ih i poredi sve verzije PRE bilo koje izmene. Neslaganje vraća HTTP 409 i rollback celog bulk-a. Booking decrement, cancellation restore i expiration restore povećavaju version pod istim postojećim inventory lock-ovima. Time stara forma ne može vratiti prodatu sobu u availability niti izbrisati nov cancellation restore.

Semantika ostaje apsolutno postavljanje availability/price. Supplier koji pregleda nove vrednosti i namerno ih promeni to i dalje može; sistem ne uvodi novu definiciju fizičkog kapaciteta. Setup samo stvara nepostojeće dane i odbija postojeće, čak i kada browser pokuša da podmetne aktuelnu verziju. Stari klijenti bez version polja dobijaju validation error; ne postoji tihi compatibility overwrite.

Alpine odbacuje zakašnjele mesečne odgovore; save ima duplicate guard, bulk conflict poništava snapshot i traži novo učitavanje. Ovo nije distributed locking sistem. DB lock i optimistic verzija rade zajedno: lock serijalizuje upis, verzija otkriva da korisnik menja zastareo prikaz.

## Hold / payment / cancellation lifecycle

Koristi se postojeća `bookings.locked_until` kolona kao payment deadline. Novi pending booking dobija `now()+30 minutes` na serveru. Migracija postojećim pending redovima bez roka daje rollout grace od 30 minuta; ne ističu trenutno. Plaćanje čisti deadline. Legacy paid booking sa rokom neće isteći; expiry servis očisti taj rok.

| Booking / payment stanje | Dozvoljeno ponašanje |
|---|---|
| pending + nema payment / pending / failed, pre roka | Pay/retry; retry ne produžava deadline. Staff može potvrditi. |
| pending + unpaid, sada >= rok | Payment/retry/confirm odbijeni; command vraća inventory i postavlja expired. |
| pending + paid | Nema unpaid hold-a; staff confirm/cancel. Dozvoljen cancel daje fake refund. |
| confirmed + unpaid | Ostaje postojeća staff odluka; nema automatskog 30-min expiry-ja. Pay dozvoljen pre checkout-a. |
| confirmed + paid | Dozvoljen cancellation + refund; completion posle checkout-a. |
| cancelled | Items ostaju; paid → refunded, unpaid nema refund; bez ponovnih transition-a. |
| expired / rejected / completed | Nema novog plaćanja ili management status promene. Payment failed/pending istorija se ne pretvara u uspešnu naplatu. |

Expiry transaction: lock booking → ponovna provera pending/deadline → lock payment → odbijanje expiry-ja ako paid → agregacija BookingItems → zaključavanje svih postojećih inventory redova sortiranih po room/date → restore + version increment → expired → commit. Nedostajući inventory izaziva rollback i command failure/report; ne izmišlja novi inventory red. Items se ne brišu. Dupli command/cancel ne vraća inventory dvaput.

Payment, cancel, confirm, complete i expiry prvo uzimaju isti booking lock. Payment proverava server time i status nakon lock-a. Race se završava ili pending+paid sa zadržanim inventory-jem ili expired+unpaid sa vraćenim inventory-jem; nikad paid+expired. Dozvoljena paid cancellation u istoj DB transakciji beleži fake refund i restore. Create radi najviše tri transaction pokušaja posle MySQL deadlock-a; nema spoljnih side effect-a u tom closure-u. Ovo nije HTTP idempotency key za dva namerna create POST-a.

Operativno:

```powershell
php artisan bookings:expire
php artisan schedule:list
```

Production scheduler mora pokretati `php artisan schedule:run` svakog minuta. `withoutOverlapping()` smanjuje dupli rad; DB provere su stvarna zaštita integriteta. Expiry može kasniti do sledećeg scheduler prolaza, ali payment se odbija odmah po server deadline-u. Potrebni su alarm za neuspešan command i provera da scheduler radi. Browser countdown nije authority i nije dodat.

## Security, uploads i deployment konfiguracija

- Booking/payment IDOR, supplier hotel/room/image ownership i admin/user granice ostaju server-side policy/Gate/scope provere. Signed route podrazumeva guest capability; booking number nije authorization. Izmenjen/pogrešan/istekao potpis odbija se. POST/CSRF middleware ostaje; signed URL ne zamenjuje CSRF.
- Završna provera stvarne FK šeme potvrđuje zatvoren supplier account-deletion/history P1: `hotels.supplier_id`, `bookings.hotel_id` i `booking_items` history FK-ovi su `RESTRICT`; `bookings.user_id` je `SET NULL`, a `payments.booking_id` ostaje `NO ACTION`. `ProfileController` supplier profile removal šalje kroz `SupplierLifecycleService::deactivateSupplier()`, koji zadržava nalog i arhivira business podatke, uz blokadu dok postoje pending/confirmed bookings. `rooms.hotel_id` `CASCADE` ostaje samo za pomoćno brisanje unused kataloga, van supplier account deletion puta. Lifecycle MySQL testovi potvrđuju ovaj ugovor.
- Guest management/payment odgovori ostaju private/no-store i no-referrer. Link ostaje bearer capability do kraja checkout dana; zaštita njegovog čuvanja, email dostava i opoziv su preostale poslovne odluke.
- Kontroleri koriste validated/eksplicitno odabrana polja. Cene/status/user_id/refund ne preuzimaju se iz proizvoljnih browser polja. Nije potvrđeno nesanitizovano dinamičko SQL sortiranje ili SQL injection u aktivnim upitima. City filter koristi query binding; `%` wildcard je pretraga, ne SQL kod.
- Aktivni `{!! !!}` u setup steps emituju fiksan znak ✓, ne user HTML. Opisi/imena/feedback koriste Blade escaping. Nije uveden blanket HTML filter bez potrebe.
- Upload: do 10 fajlova po request-u, JPEG/PNG/WebP, max 4 MB, dimenzije do 6000 po osi i ukupno 12 MP. UUID putanja ispod odgovarajućeg hotels/id ili rooms/id, GD decode/scaleDown(1200)/WebP(85). SVG/script i MIME spoof se odbijaju; storage write failure ne kreira image zapis. DB insert failure čisti nov fajl. Delete je scoped i ne sme izaći iz entity direktorijuma. Javne putanje sadrže samo javne hotelske slike, ne privatne payment dokumente.
- Rate limits: availability 60/min/IP; create 10/min i 60/hour/IP; booking/payment/guest POST akcije 30/min/actor (ili IP za guest) i 15/min/booking/IP; room upload 10/min/user, Livewire hotel/room upload svaki svoj 10/min/user bucket. Login zadržava Breeze throttle. GET navigation/manage/checkout nije agresivno ograničen. Shared NAT može pogoditi IP granice; stvarne pragove treba pratiti. Multi-node deployment zahteva zajednički cache limiter backend ili edge limiter; Redis nije dodat.
- `.env.example` ostaje development primer sa APP_DEBUG=true, ne production konfiguracija. Pre deployment-a zahtevati APP_ENV=production, APP_DEBUG=false, HTTPS APP_URL, SESSION_SECURE_COOKIE=true, HttpOnly/lax cookies, odgovarajuće session domain/trusted proxy/host pravilo. Nisu upisivane tajne ili production credentials.
- Config/route/view cache kompatibilnost proverena. Local config/route cache vraćen na uncached stanje posle provere. Storage symlink mora postojati; web server mora servirati samo public/, bez izvršavanja upload fajlova. Log rotate/retention, backup+restore proba, scheduler monitoring i queue worker ako se kasnije uključe jobs nisu zamenjeni prolaznim testovima.
- Fake payment je podrazumevano uključen samo local/testing. Javni portfolio demo zahteva eksplicitnu odluku o FAKE_PAYMENTS_ENABLED i jasnu oznaku simulacije; nikada ga predstavljati kao realnu naplatu. Laravel Boost/dev tooling ne instalirati u production runtime (`composer install --no-dev`), ne izlagati Vite dev server. Postojeći Sortable CDN je P2 supply-chain/deployment odluka.

## Testovi i ponavljanje verifikacije

Završni kompletan suite sa uključenim MySQL testovima: **101 test, 1219 assertions, 0 failures/errors/skips**. Node: **11/11**. Test broj baseline-a je bio 79; dodata su 22 testa. Raniji staff deadline test zadržan je uz legacy fixture bez unpaid hold-a da testira staff cancellation cutoff, dok novi test eksplicitno zabranjuje potvrdu isteklog unpaid hold-a.

Deset MySQL testova koriste dve nezavisne PHP konekcije u nasumičnoj disposable bazi. Parent drži lock i proverava `information_schema.innodb_trx` da su OBA workera zaista u LOCK WAIT pre otpuštanja. Pokrivaju: duplicate cancellation, confirm/cancel, duplicate successful payment, payment/cancel, duplicate refund/cancel, duplicate expiry, payment/expiry, supplier/create, supplier/cancel i create/create. Worker startup timeout je povećan 20 → 60 s zbog potvrđenog baseline timeout-a; provera stvarnog lock wait-a nije uklonjena. Test baze se kreiraju/brišu samo pod nasumičnim `booking_management_test_...` imenom, nikada development baza.

Feature testovi pokrivaju expiry cutoff/rollback/paid zaštitu, stale i missing-row verzije, atomic bulk, setup version injection, supplier ownership, upload MIME/storage failure, demo invariants/repeat/production guard, approved-local image import/path traversal, pagination/query count i rate limiting. Postojeći booking, management, payment, auth i route testovi ostaju. Frontend testovi pokrivaju date/search races, duplicate submits, inventory response races, preview/version i conflict reset.

```powershell
$env:RUN_BOOKING_MYSQL_TESTS='1'
php vendor/phpunit/phpunit/phpunit
node --test tests/Frontend/*.test.mjs
php tests/Support/readiness-profile.php
php artisan route:list
php artisan migrate:status
php artisan view:cache
npm.cmd run build
git diff --check
composer audit
pnpm.cmd audit --json
```

PHP syntax: 128 app/database/routes/tests PHP fajlova provereno. Blade compilation, config:cache, route:cache i Vite production build prolaze. Route integrity testovi proveravaju duple names i URI+method, stvarne controller metode i literalne route reference. Aktuelni route audit ima 84 registrovane rute; route diff u final polish-u nema obrisanih ruta, a guest recovery je dodao dve nove named rute. `RouteIntegrityTest` potvrđuje jedinstvene names/method+URI, realne controller actions i literalne route reference. Nova migracija je izvršena na lokalnoj MySQL bazi i u disposable test bazama; svi migrate:status redovi su Ran. Git diff --check prolazi.

Composer update je ostao u postojećim major granicama: Laravel 12.69.2, Livewire 4.4.4 i ciljane tranzitivne zakrpe; nema Composer advisories/abandoned paketa. Frontend ostaje Vite 7.3.6/axios 1.20, esbuild 0.28.2 je u podržanom Vite rasponu. `concurrently>shell-quote` ima uski override ^1.8.5 jer parent pinuje ranjivu patch verziju. Pnpm audit: 0 advisories. To je rezultat na datum audita, ne trajna garancija.

Browser smoke pokušaj nije uspeo: postojeća browser veza nije dostupna, discovery vraća praznu listu. Nije tvrđeno da su izvršeni realni browser klikovi. HTTP feature render/testovi i Blade compilation jesu izvršeni. Nije rađen distribuirani HTTP load/soak test; query-count i realni MySQL lock testovi predstavljaju tačno navedenu granicu provere.

## Promenjeni i dodati fajlovi po domenima

- Demo: `app/Console/Commands/DemoSeed.php`, `DemoImages.php`; `database/seeders/DemoSeeder.php`, `database/seeders/demo/destinations.php`; `resources/demo/README.md`, `manifest.json`, `images/.gitkeep`.
- Inventory/expiry: `database/migrations/2026_09_13_000001_add_readiness_support.php`; `app/Services/InventoryService.php`, `BookingService.php`, `FakePaymentService.php`; `app/Models/Booking.php`, `RoomInventory.php`; `app/Console/Commands/ExpireBookings.php`; `routes/console.php`.
- Supplier/performance: `app/Models/Hotel.php`; `app/Http/Controllers/RoomSetupController.php`; `app/Http/Controllers/Supplier/HotelController.php`, `HotelSetupController.php`, `RoomController.php`, `RoomInventoryController.php`; `resources/views/supplier/hotels/index.blade.php`, `hotels/setup/inventory.blade.php`, `rooms/index.blade.php`, `rooms/setup/inventory.blade.php`, `inventory/calendar.blade.php`.
- Upload: `app/Traits/HandleImagesUpload.php`; `app/Actions/Hotels/UploadHotelImage.php`, `app/Actions/Rooms/UploadRoomImage.php`; `resources/views/livewire/supplier/⚡hotel-images-manager.blade.php`, `⚡room-images-manager.blade.php`.
- Request/routing/UI: `app/Http/Requests/CreateBookingRequest.php`; `app/Providers/AppServiceProvider.php`; `routes/booking.php`, `routes/supplier.php`; `resources/views/booking/_deadline.blade.php`, `checkout.blade.php`, `manage.blade.php`, `success.blade.php`.
- Dependencies: `composer.lock`, `package.json`, `pnpm-lock.yaml`, `pnpm-workspace.yaml`.
- Tests/audit: `tests/Feature/ReadinessHardeningTest.php`, `DemoSeederIntegrityTest.php`, `BookingManagementConcurrencyTest.php`, `BookingManagementTest.php`; `tests/Support/booking-management-worker.php`, `readiness-profile.php`; `tests/Frontend/inventory.test.mjs`; `docs/PRODUCTION-READINESS.md`.

## Obavezne ljudske odluke pre pravog deployment-a

1. Pravi provider/webhook/refund/reconciliation i finansijska pravila; fake sistem ne može ući u realno naplaćivanje.
2. Potvrditi da staff sme potvrditi unpaid booking i time ukloniti hold; definisati 30-min rok, grace postojećim rezervacijama i hotelske vremenske zone.
3. Potvrditi poslovnu politiku za retention/anonymization ličnih podataka. Supplier business account removal već koristi deactivation/archive i restrictive history FK-ove; ta politika određuje koliko dugo se zadržavaju identitet i poslovni kontekst.
4. Guest link delivery/revocation, email verification i zaštita ličnih podataka; recovery sada postoji, ali bearer link i dalje zahteva retention/revocation politiku.
5. Nabaviti/odobriti image licence i source pack; demo nalozi/dataset moraju biti odvojeni od stvarne produkcije.
6. Izabrati deployment HTTPS/proxy/host/session/cache/scheduler politiku, monitoring, log retention, storage quota i backup/restore postupak. Testirati failover i stvarni HTTP workload na ciljnoj infrastrukturi.
7. Zakazati ručni desktop/mobile browser acceptance test kada browser bude dostupan, i razmotriti P2 galerije/room-config limite za veće pojedinačne hotele.

Rezultat je bolje proverljiv portfolio MVP na velikom datasetu. Nije tvrdnja da je aplikacija production-ready.
