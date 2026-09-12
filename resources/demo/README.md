# Lokalni demo image pack

Ovde nema preuzetih fotografija. Dodajte 30–40 svojih/licenciranih JPG, PNG ili WebP fajlova u `resources/demo/images/<category>/` (3–4 po kategoriji). Ne dodavati stvarne hotelske logotipe. Fajlovi nisu obavezni: bez njih se ne kreiraju image redovi ni placeholder URL-ovi; postojeći UI prikazuje empty image state.

U `manifest.json` dodajte zapis tek kada proverite prava korišćenja:

```json
{
  "filename": "exterior/my-original-photo.webp",
  "category": "exterior",
  "source": "local original",
  "author": "ime autora",
  "source_url": null,
  "license_note": "opis licence/dozvole",
  "approved": true
}
```

Ne izmišljati attribution ili source URL. `source_url` je samo metapodatak, nikada download instrukcija. Import odbija path traversal/symlink escape, nedozvoljene kategorije, neodobrene i prevelike/nevalidne slike. Source asset se kopira u zaseban hotel/room storage prostor, pa brisanje kod jednog supplier-a ne utiče na drugi hotel.

Posle `php artisan demo:seed`, pokrenite `php artisan demo:images`. Može se ponoviti i kasnije nakon dodavanja pack-a; ne menja bookings/inventory. Seed command takođe pokušava import prisutnog pack-a. Pokrenite `php artisan storage:link` ako public storage link ne postoji.
