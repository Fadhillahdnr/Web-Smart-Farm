# Web Smart Farm — Soil Classifier

Web Smart Farm adalah aplikasi Laravel untuk menerima data sensor tanah dari ESP32, menyimpan data ke PostgreSQL/Supabase, dan menampilkannya melalui dashboard realtime.

## Dokumentasi

Dokumentasi lengkap berbahasa Indonesia tersedia di folder [`docs/`](docs/README.md):

- Pengenalan Laravel untuk pemula
- Arsitektur aplikasi
- Instalasi dan konfigurasi
- Database, migration, model, dan relasi
- Flow login, register, profil, tanah, sensor, dan export
- Integrasi API dan ESP32
- Dashboard realtime dan frontend
- Testing, keamanan, dan deployment
- Troubleshooting dan glosarium

**[Mulai membaca dokumentasi](docs/README.md)**

## Fitur utama

- Login, register, reset password, dan profil
- Kepemilikan tanah per user
- Pengelompokan histori menjadi Tanah A, Tanah B, dan seterusnya
- Mode tanah aktif untuk firmware ESP32 tanpa token
- Token tanah opsional untuk pengembangan beberapa perangkat
- Monitoring kelembapan, pH, warna, status, dan baterai
- Grafik serta histori realtime
- Ekspor Excel per tanah
- Isolasi data antar-user
- Automated testing

## Mulai cepat

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Baca [panduan instalasi](docs/03-instalasi-dan-konfigurasi.md) sebelum menghubungkan Supabase dan ESP32.

## Testing

```bash
php artisan test
npm run build
```

## Keamanan

Jangan commit `.env`, password database, password Wi-Fi, atau token production. Gunakan `.env.example` sebagai daftar konfigurasi yang diperlukan.

## Lisensi

Project dibangun menggunakan Laravel yang berlisensi MIT.
