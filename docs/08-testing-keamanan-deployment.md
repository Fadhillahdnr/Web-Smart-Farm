# 8. Testing, Keamanan, dan Deployment

## Automated testing

Project memakai PHPUnit melalui Laravel testing utilities.

Jalankan seluruh test:

```bash
php artisan test
```

Jalankan file tertentu:

```bash
php artisan test --filter=SoilMonitoringTest
```

Test memakai database terisolasi yang dikonfigurasi di `phpunit.xml`, bukan database production.

## Cakupan test

Test autentikasi memeriksa:

- Halaman login/register dapat dibuka.
- Login benar berhasil.
- Password salah gagal.
- Logout berhasil.
- Reset password.
- Konfirmasi dan update password.
- Verifikasi email.

Test profil memeriksa:

- Halaman profil.
- Update nama/email.
- Hapus akun dengan password benar.
- Penolakan password salah.

`SoilMonitoringTest` memeriksa:

- Membuat tanah.
- Token unik.
- API token dan API tanah aktif.
- Validasi rentang sensor.
- Penolakan tanpa tanah aktif.
- Perpindahan tanah aktif.
- Isolasi data antar-user.
- Histori per tanah.
- Response snapshot.

## Menulis test baru

Pola umum:

```php
public function test_sesuatu_terjadi(): void
{
    // Arrange: siapkan data
    // Act: lakukan request
    // Assert: periksa hasil
}
```

Gunakan `RefreshDatabase` agar setiap test mulai dari database bersih.

## Perlindungan keamanan yang tersedia

### Password hashing

Model User meng-cast password menjadi `hashed`, dan controller autentikasi menggunakan Hash Laravel.

### CSRF

Semua form web memiliki:

```blade
@csrf
```

CSRF melindungi aksi user agar tidak dipalsukan situs lain.

### Session regeneration

Session ID dibuat ulang setelah login untuk mencegah session fixation.

### Login rate limiting

Percobaan login dibatasi berdasarkan email dan IP.

### Ownership check

Controller memeriksa `soilPlot->user_id === user->id` sebelum membaca/mengubah data.

### Validasi input

Form dan payload ESP32 divalidasi sebelum masuk database.

### Mass assignment

Model hanya mengizinkan field yang tercantum dalam `$fillable`.

### Token disembunyikan

`sensor_token` berada di `$hidden`, sehingga tidak ikut pada serialisasi JSON normal.

## Keterbatasan keamanan yang harus dipahami

Endpoint `/api/sensor` menerima payload tanpa credential agar firmware lama tidak perlu diubah. Siapa pun yang mengetahui endpoint secara teori dapat mengirim data ke tanah aktif.

Untuk production multi-device yang lebih aman, gunakan token per alat atau autentikasi perangkat.

Firmware juga memakai `client.setInsecure()`. Gunakan CA certificate jika tingkat keamanan perangkat perlu ditingkatkan.

## Rahasia dan Git

Jangan commit:

- `.env`
- Password database
- Password Wi-Fi
- Token production
- API key

`.env.example` hanya berisi nama konfigurasi dan contoh aman.

Folder `docs/` tidak diabaikan dan memang harus ikut GitHub.

## Dependency audit

```bash
composer audit
npm audit
```

Jangan memperbarui dependency besar langsung di production. Update di branch, jalankan test, lalu deploy.

## Deployment Laravel Cloud

Alur umum:

```text
Push GitHub
  → Laravel Cloud mengambil commit
  → composer install
  → npm build
  → migration
  → aplikasi baru aktif
```

Perintah production yang lazim:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
```

Sesuaikan urutan dengan build command Laravel Cloud.

## Environment Laravel Cloud

Minimal:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smart-farm.laravel.cloud

DB_CONNECTION=pgsql
DB_HOST=...
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=...
DB_PASSWORD=...
DB_SSLMODE=require
DB_EMULATE_PREPARES=true
```

Konfigurasi mail harus diisi bila reset password harus sampai ke inbox.

## Checklist sebelum deploy

- [ ] Seluruh test lulus.
- [ ] `npm run build` berhasil.
- [ ] `composer audit` diperiksa.
- [ ] Migration baru sudah direview.
- [ ] Tidak ada credential di Git diff.
- [ ] `APP_DEBUG=false` di production.
- [ ] Database production memiliki backup bila migration berisiko.
- [ ] Firmware endpoint sesuai domain production.

## Checklist setelah deploy

- [ ] Buka register dan login.
- [ ] Buat tanah percobaan.
- [ ] Mulai dan hentikan rekaman.
- [ ] Pastikan ESP32 mendapat HTTP 201.
- [ ] Periksa snapshot di Network browser.
- [ ] Uji export Excel.
- [ ] Periksa log server.

