# 9. Troubleshooting dan Glosarium

## Cara mendiagnosis masalah

Pisahkan masalah menjadi empat lapisan:

```text
Perangkat → Jaringan/API → Laravel → Database
                         → Browser/frontend
```

Tanyakan:

1. Apakah ESP32 terhubung Wi-Fi?
2. HTTP code berapa di Serial Monitor?
3. Apakah request terlihat di Laravel log?
4. Apakah record masuk database?
5. Apakah endpoint snapshot berhasil?
6. Apakah browser console memiliki error JavaScript?

## Log Laravel

Lokal:

```text
storage/logs/laravel.log
```

Production: gunakan halaman log Laravel Cloud.

Jangan menyalakan `APP_DEBUG=true` di production karena stack trace dapat membocorkan detail internal.

## Error prepared statement tidak ada

Pesan:

```text
prepared statement "pdo_stmt_..." does not exist
```

Penyebab: Supabase Transaction Pooler port 6543 memindahkan koneksi, tetapi PDO mencoba memakai server-side statement dari koneksi lama.

Solusi:

```env
DB_EMULATE_PREPARES=true
```

Lalu:

```bash
php artisan optimize:clear
php artisan config:cache
```

## Error boolean = integer

Pesan:

```text
operator does not exist: boolean = integer
```

PostgreSQL tidak menyamakan boolean dengan angka 1/0. Project memakai literal `TRUE/FALSE` pada query tanah aktif. Pastikan versi controller/model terbaru sudah ter-deploy dan cache dibersihkan.

## HTTP 409 dari ESP32

Belum ada tanah aktif. Login, pilih tanah, lalu tekan **Mulai Rekam**.

## HTTP 422 dari ESP32

Payload tidak lolos validasi. Periksa:

- Semua field ada.
- Moisture dan battery 0–100.
- pH 0–14.
- JSON valid.

## HTTP 500

Jangan menebak hanya dari browser. Buka response Network dan Laravel Cloud log. Cari exception pertama dan SQL yang gagal.

## Sensor tidak mengirim

Dashboard menampilkan status ini jika data terakhir lebih lama dari 15 detik.

Periksa:

- Serial Monitor dan HTTP code.
- Wi-Fi ESP32.
- Tanah masih aktif.
- Domain endpoint benar.
- Laravel Cloud dan Supabase aktif.

Firmware saat ini tidak memiliki loop reconnect eksplisit. Jika Wi-Fi benar-benar hilang, restart alat atau tambahkan reconnect pada versi firmware mendatang.

## Dashboard tidak berubah

1. Buka DevTools → Network.
2. Cari request `/snapshot`.
3. Pastikan status 200.
4. Periksa JSON `latest` dan `history`.
5. Hard reload browser.
6. Jalankan `npm run build` saat asset berubah.
7. Bersihkan cache Laravel.

## Warning Tailwind CDN

Project tidak seharusnya memakai `cdn.tailwindcss.com` di production. Gunakan `@vite` dan jalankan build.

## Register/login gagal

Periksa:

- Migration tabel `users` dan `sessions` sudah berjalan.
- Koneksi Supabase benar.
- `DB_EMULATE_PREPARES=true` untuk port 6543.
- `APP_KEY` tersedia.
- Cookie/session domain benar.

## Reset password tidak mengirim email

Jika `MAIL_MAILER=log`, ini normal. Konfigurasikan SMTP/layanan mail production.

## Asset Vite tidak ditemukan

Pesan umum: manifest tidak ditemukan.

```bash
npm install
npm run build
```

## Class atau package tidak ditemukan

```bash
composer install
php artisan optimize:clear
```

## Migration gagal

Jalankan:

```bash
php artisan migrate:status
```

Baca migration yang belum dijalankan. Jangan menghapus migration yang sudah pernah dipakai production.

## Daftar istilah

| Istilah | Penjelasan sederhana |
|---|---|
| API | Pintu komunikasi antarprogram |
| Backend | Kode server, database, dan logika bisnis |
| Frontend | Tampilan dan kode yang berjalan di browser |
| Route | Pemetaan URL ke proses Laravel |
| Controller | Pengatur request dan response |
| Model | Representasi tabel dan data |
| View | Template halaman |
| Blade | Template engine Laravel |
| Middleware | Pemeriksaan sebelum request masuk controller |
| Migration | Riwayat perubahan struktur database |
| Eloquent | ORM Laravel untuk database |
| ORM | Cara mengakses tabel melalui object/model |
| Session | Data yang menandai user sedang login |
| Cookie | Data kecil yang disimpan browser |
| CSRF | Serangan pemalsuan request dari situs lain |
| Hash | Representasi satu arah untuk password |
| Validation | Pemeriksaan apakah input sesuai aturan |
| JSON | Format teks pertukaran data API |
| HTTP status | Kode hasil request seperti 200/404/500 |
| Polling | Browser meminta data secara berkala |
| Transaction | Sekumpulan query yang berhasil/gagal bersama |
| Foreign key | Penghubung antar tabel |
| Cascade delete | Menghapus data anak saat induk dihapus |
| Pooler | Pengelola kumpulan koneksi database |
| Environment | Konfigurasi berbeda untuk lokal/production |
| Vite | Tool build asset frontend |
| Dependency | Library pihak lain yang dipakai project |

## Jika ingin belajar lebih lanjut

Setelah memahami dokumentasi ini, coba latihan berikut:

1. Tambahkan catatan/deskripsi pada setiap tanah.
2. Tambahkan filter histori berdasarkan tanggal.
3. Tambahkan pagination untuk data yang sangat banyak.
4. Buat token khusus perangkat, bukan token tanah.
5. Tambahkan reconnect dan queue lokal pada ESP32.
6. Ganti polling dengan WebSocket bila benar-benar membutuhkan push realtime.

