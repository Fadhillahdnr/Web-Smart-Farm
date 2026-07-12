# Dokumentasi Web Smart Farm

Selamat datang di dokumentasi **Web Smart Farm / Soil Classifier**. Dokumentasi ini ditulis dalam bahasa Indonesia dan dibuat agar orang yang baru mengenal pemrograman tetap dapat mengikuti cara kerja aplikasi secara bertahap.

## Gambaran singkat

Aplikasi ini menerima hasil pengukuran tanah dari ESP32, menyimpannya ke PostgreSQL/Supabase, lalu menampilkan data tersebut pada dashboard Laravel secara realtime.

```text
Sensor tanah, pH, warna, baterai
                │
                ▼
             ESP32
                │  POST /api/sensor
                ▼
        Laravel di Laravel Cloud
                │
                ▼
       PostgreSQL / Supabase
                │
                ▼
 Dashboard, grafik, histori, Excel
```

Setiap user dapat membuat beberapa kelompok tanah, misalnya **Tanah A** dan **Tanah B**. Data setiap tanah dipisahkan dan hanya dapat dilihat oleh pemiliknya.

## Urutan belajar yang disarankan

1. [Pengenalan Laravel dan struktur project](01-pengenalan-laravel.md)
2. [Arsitektur dan cara kerja aplikasi](02-arsitektur-aplikasi.md)
3. [Instalasi dan konfigurasi](03-instalasi-dan-konfigurasi.md)
4. [Database, migration, model, dan relasi](04-database-dan-model.md)
5. [Seluruh flow aplikasi](05-flow-aplikasi.md)
6. [API sensor dan integrasi ESP32](06-api-dan-esp32.md)
7. [Dashboard dan frontend realtime](07-dashboard-dan-frontend.md)
8. [Testing, keamanan, dan deployment](08-testing-keamanan-deployment.md)
9. [Troubleshooting dan daftar istilah](09-troubleshooting-dan-glosarium.md)

## Teknologi yang digunakan

| Teknologi | Fungsi |
|---|---|
| Laravel 12 | Framework utama backend dan web |
| PHP 8.2+ | Bahasa pemrograman backend |
| Laravel Breeze | Login, register, reset password, dan profil |
| Eloquent ORM | Mengakses database melalui model PHP |
| PostgreSQL / Supabase | Menyimpan user, tanah, session, dan data sensor |
| Laravel Cloud | Hosting aplikasi Laravel |
| Blade | Template HTML milik Laravel |
| Tailwind CSS | Styling tampilan |
| Alpine.js | Interaksi sederhana pada komponen profil |
| Chart.js | Grafik realtime |
| Vite | Build CSS dan JavaScript |
| Laravel Excel | Ekspor histori ke `.xlsx` |
| PHPUnit | Automated testing |
| ESP32 | Membaca sensor dan mengirim data ke API |

## Peta folder penting

```text
Web-Smart-Farm/
├── app/
│   ├── Exports/             # Logika ekspor Excel
│   ├── Http/Controllers/    # Penerima request dan pengatur flow
│   └── Models/              # Representasi tabel database
├── config/                  # Konfigurasi database, session, mail, dll.
├── database/migrations/     # Riwayat perubahan struktur database
├── docs/                    # Dokumentasi yang sedang Anda baca
├── resources/
│   ├── css/                 # Sumber CSS
│   ├── js/                  # Sumber JavaScript
│   └── views/               # Halaman Blade
├── routes/                  # Daftar URL aplikasi
├── tests/                   # Automated tests
├── .env.example             # Contoh konfigurasi tanpa rahasia
├── artisan                  # CLI Laravel
├── composer.json            # Dependency PHP
└── package.json             # Dependency frontend
```

## Prinsip penting project

- File `.env` berisi rahasia dan **tidak boleh** di-push ke GitHub.
- Folder `docs/` memang harus di-push agar dapat dipelajari publik.
- Satu data sensor selalu terhubung ke satu tanah melalui `soil_plot_id`.
- Satu tanah selalu dimiliki satu user melalui `user_id`.
- Karena firmware ESP32 lama tidak mengirim identitas, hanya satu tanah boleh menjadi tujuan rekaman tanpa token pada satu waktu.
- Token tanah tersedia untuk pengembangan beberapa alat di masa depan.

## Cara membaca diagram

Tanda panah berarti data atau proses bergerak ke tahap berikutnya:

```text
Browser → Route → Controller → Model → Database
```

Artinya browser mengirim request ke URL, Laravel mencocokkan URL melalui route, controller memprosesnya, model mengakses database, lalu hasil dikembalikan ke browser.

