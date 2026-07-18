# Alur Lengkap Program Web Smart Farm

Dokumen ini menjelaskan bagaimana aplikasi ini berjalan dari pengaturan environment hingga data sensor ditampilkan di dashboard.

## 1. Pengaturan Environment

Aplikasi Laravel ini menggunakan file `.env` untuk konfigurasi utama.

Yang penting:
- `APP_URL=http://localhost` (alamat aplikasi yang dijalankan)
- `DB_CONNECTION=pgsql` (menggunakan PostgreSQL)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` diisi dari layanan database, dalam repo ini terhubung ke Supabase.
- `DB_SSLMODE=require` untuk koneksi aman ke database.
- `SESSION_DRIVER=database` agar session disimpan di database.
- `CACHE_STORE=database` dan `QUEUE_CONNECTION=database` untuk menggunakan database sebagai store.

Jenis konfigurasi ini memastikan Laravel dapat:
- terhubung ke database
- menyimpan session pengguna
- menjalankan otentikasi

## 2. Struktur Data dan Relasi Model

Terdapat tiga model utama terkait alur ini:

### 2.1 `App\Models\User`
- Relasi: `user()->soilPlots()`
- Setiap pengguna memiliki banyak `SoilPlot`

### 2.2 `App\Models\SoilPlot`
- Kolom penting: `user_id`, `name`, `sensor_token`, `is_active`
- Relasi: `soilPlot()->user()` dan `soilPlot()->sensorData()`
- `sensor_token` disembunyikan saat serialisasi
- Scope `active()` untuk memfilter tanah yang sedang aktif merekam

### 2.3 `App\Models\SensorData`
- Kolom penting: `moisture`, `ph`, `color`, `status`, `battery`, `soil_plot_id`
- Relasi: `sensorData()->soilPlot()`
- Menyimpan riwayat data sensor untuk setiap tanah

### 2.4 Migrasi database
- `soil_plots` berisi data tanah dan token sensor
- `sensor_data` berisi riwayat data dengan relasi `soil_plot_id`

## 3. Routing dan Autentikasi

File route utama: `routes/web.php`

Alur utama:
- `/` langsung redirect ke `/login`
- Auth route berasal dari `routes/auth.php`
- Semua route dashboard dan pengelolaan `soil-plots` berada dalam middleware `auth`

Route penting:
- `GET /dashboard` → `DashboardController@index`
- `GET /dashboard/soil/{soilPlot}/snapshot` → `DashboardController@snapshot`
- `POST /soil-plots` → `SoilPlotController@store`
- `PATCH /soil-plots/{soilPlot}` → `SoilPlotController@update`
- `PATCH /soil-plots/{soilPlot}/token` → `SoilPlotController@regenerateToken`
- `PATCH /soil-plots/{soilPlot}/activate` → `SoilPlotController@activate`
- `PATCH /soil-plots/{soilPlot}/deactivate` → `SoilPlotController@deactivate`
- `DELETE /soil-plots/{soilPlot}` → `SoilPlotController@destroy`
- `GET /soil-plots/{soilPlot}/export-excel` → download export via `SensorDataExport`

## 4. Alur Kontroler Dashboard

### 4.1 `DashboardController@index`
- Mengambil semua `soilPlots` milik pengguna yang login.
- Memilih `selectedSoil` berdasarkan parameter query `soil` jika ada, atau tanah pertama sebagai fallback.
- Mengambil `history` 20 data sensor terbaru dari tanah yang dipilih.
- Mengirim data ke view `resources/views/dashboard.blade.php` dengan variabel:
  - `soilPlots`
  - `selectedSoil`
  - `history`

### 4.2 `DashboardController@latest` dan `history`
- `latest`: mengembalikan data sensor terbaru dalam format JSON.
- `history`: mengembalikan 20 data sensor terbaru dalam format JSON.
- Keduanya memanggil `authorizeOwner()` untuk memastikan pengguna hanya mengakses tanah miliknya.

### 4.3 `DashboardController@snapshot`
- Mengambil 20 data sensor terbaru sekaligus.
- Mengembalikan JSON dengan struktur:
  - `latest` : data sensor terbaru
  - `history` : 20 data terbaru
  - `recording` : status aktif tanah
- Endpoint ini digunakan oleh JavaScript dashboard untuk menyegarkan tampilan tanpa reload halaman.

## 5. Alur Kontroler SoilPlot

### 5.1 `SoilPlotController@store`
- Validasi nama tanah wajib, maksimum 100 karakter, unik untuk user.
- Membuat `SoilPlot` baru dengan token sensor acak 48 karakter.
- Redirect kembali ke dashboard dengan `soil` query parameter dan pesan sukses.

### 5.2 `SoilPlotController@update`
- Validasi nama baru dan ubah nama tanah.
- Redirect ke dashboard dengan pesan sukses.

### 5.3 `SoilPlotController@regenerateToken`
- Mengganti token sensor dengan string acak baru.
- Berguna saat perangkat ESP32 menggunakan token lama harus diganti.

### 5.4 `SoilPlotController@activate`
- Aktifkan tanah terpilih untuk menerima data dari perangkat.
- Dalam transaction:
  - `lockForUpdate()` untuk mencegah race condition
  - matikan semua tanah aktif dulu
  - aktifkan tanah yang dipilih
- Endpoint ini mengatur bahwa hanya satu tanah bisa menerima data dari perangkat lama pada satu waktu.

### 5.5 `SoilPlotController@deactivate`
- Menonaktifkan tanah terpilih sehingga data sensor tidak lagi direkam ke tanah itu.

### 5.6 `SoilPlotController@destroy`
- Hapus tanah dan seluruh data sensor terkait karena `cascadeOnDelete()` di migrasi.

## 6. Tampilan Dashboard dan Flow JavaScript

File utama tampilan: `resources/views/dashboard.blade.php`

### 6.1 Flow tampilan statis
- Bagian header menampilkan nama aplikasi dan tombol logout.
- Form `+ Tambah Tanah` memanggil `POST /soil-plots`.
- Dropdown `Tanah yang dipantau` memilih tanah aktif dan mengubah URL dashboard.
- Form tombol `Mulai Rekam`, `Hentikan Rekam`, `Ubah nama`, `Hapus tanah`, dan `Buat ulang token` memanggil route `soil-plots` terkait.
- Token perangkat ditampilkan dalam field baca-saja.

### 6.2 Flow data sensor
- Ketika `selectedSoil` ada, halaman menampilkan:
  - Kartu metrik: kelembapan, pH, warna, baterai, status
  - Grafik Chart.js untuk kelembapan dan pH
  - Tabel histori 20 data terbaru
  - Indikator bar untuk kelembapan, pH, baterai

### 6.3 JavaScript refresh data
- `snapshotUrl` dibentuk dari route `dashboard.snapshot` untuk tanah terpilih.
- Data awal `initialHistory` diinject dari controller.
- `refresh()` memanggil `fetch(snapshotUrl)` secara async.
- Setelah data diterima, fungsi JavaScript:
  - memperbarui kartu metrik
  - memperbarui tabel histori
  - memperbarui grafik dengan data terbaru
  - mengubah badge koneksi sensor berdasarkan usia data terakhir
- Jika request gagal, indikator koneksi menjadi `Terputus`.

## 7. Export Excel

File export `app/Exports/SensorDataExport.php`:
- Menerima `SoilPlot` di konstruktor.
- `collection()` mengambil data sensor dengan kolom: `created_at`, `moisture`, `ph`, `color`, `battery`, `status`.
- `headings()` menentukan judul kolom Excel.
- Route `GET /soil-plots/{soilPlot}/export-excel` memeriksa bahwa tanah milik pengguna login sebelum mengunduh.

## 8. Alur Eksekusi Ringkas

1. User membuka `/` dan diarahkan ke `/login`.
2. Proses login melalui route auth bawaan Laravel.
3. Setelah login, user membuka `/dashboard`.
4. `DashboardController@index` mengambil tanah dan riwayat sensor.
5. View menampilkan form, token, status rekaman, dan grafik.
6. JavaScript memuat data awal dan memanggil endpoint `snapshot` untuk update realtime.
7. User bisa membuat tanah baru, mengganti nama, mengaktifkan/mematikan rekaman, mengganti token, atau menghapus tanah.
8. Data sensor disimpan pada tabel `sensor_data` dan terkait ke `soil_plots`.
9. User dapat mengekspor riwayat sebagai file Excel.

## 9. Catatan Khusus

- Semua operasi manajemen tanah (`soil-plots`) hanya boleh dilakukan oleh pemilik tanah.
- `activate()` memastikan hanya satu tanah aktif, karena perangkat lama hanya mengirim data ke satu tujuan.
- `sensor_token` dipakai sebagai identitas perangkat sensor jika diperlukan, tetapi tombol aktivasi/deaktivasi juga mengendalikan rekaman langsung.
- `DashboardController@snapshot` mengurangi jumlah request API dengan mengirim card, grafik, dan tabel dalam satu response.

---

Dokumen ini menjelaskan jalur lengkap dari konfigurasi `.env`, model-relasi, routing, controller, view, hingga data tampilan dashboard.