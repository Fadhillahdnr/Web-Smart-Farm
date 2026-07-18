# 2. Arsitektur dan Cara Kerja Aplikasi

## Tujuan sistem

Web Smart Farm mengubah pembacaan sensor fisik menjadi informasi yang bisa dipantau dan dikelompokkan melalui web.

Input sistem:

- Kelembapan tanah (`moisture`)
- pH tanah (`ph`)
- Klasifikasi warna (`color`)
- Status kesuburan (`status`)
- Baterai alat (`battery`)

Output sistem:

- Card nilai terbaru
- Grafik kelembapan dan pH
- Histori per tanah
- Status sensor online/tidak mengirim
- File Excel per tanah

## Arsitektur tingkat tinggi

```text
┌───────────────────┐
│ Sensor + ESP32    │
│ Arduino IDE       │
└─────────┬─────────┘
          │ HTTPS + JSON setiap ±5 detik
          ▼
┌───────────────────┐
│ Laravel Cloud     │
│ /api/sensor       │
└─────────┬─────────┘
          │ Eloquent ORM
          ▼
┌───────────────────┐
│ Supabase          │
│ PostgreSQL        │
└─────────┬─────────┘
          │ query histori
          ▼
┌───────────────────┐
│ Browser dashboard │
│ polling 3 detik   │
└───────────────────┘
```

## Komponen backend

### Autentikasi

Laravel Breeze menyediakan controller, request, route, view, session, hashing password, dan reset password.

### Manajemen tanah

`SoilPlotController` menangani:

- Membuat tanah
- Mengubah nama
- Menghapus tanah beserta historinya
- Memulai rekaman
- Menghentikan rekaman
- Membuat ulang token sensor

### Penerimaan sensor

`SensorController::store()`:

1. Memvalidasi JSON.
2. Memeriksa `X-Soil-Token` bila tersedia.
3. Jika tidak ada token, mencari satu tanah aktif.
4. Mengembalikan error bila tujuan tidak ditemukan.
5. Membuat record sensor melalui relasi tanah.

### Penyedia data dashboard

`DashboardController::snapshot()` mengambil maksimal 20 data terbaru dengan satu query. Satu response berisi:

```json
{
  "latest": {},
  "history": [],
  "recording": true
}
```

Penggabungan ini mengurangi jumlah request dan koneksi ke Supabase.

## Komponen frontend

Dashboard dirender awal oleh Blade. Setelah halaman tampil, JavaScript meminta endpoint snapshot setiap 3 detik.

Data snapshot dipakai untuk memperbarui:

- Lima card
- Progress bar
- Grafik Chart.js
- Tabel histori
- Waktu pembaruan terakhir
- Badge koneksi sensor

## Dua mode penentuan tanah

### Mode tanah aktif

Digunakan oleh firmware ESP32 saat ini karena payload tidak mengirim token.

```text
User pilih Tanah A → Mulai Rekam → is_active = TRUE
ESP32 POST tanpa token → backend mencari tanah aktif → simpan ke Tanah A
```

Hanya satu tanah boleh aktif secara global. Saat Tanah B diaktifkan, tanah aktif sebelumnya dimatikan dalam transaction.

### Mode token

Disediakan untuk beberapa perangkat di masa depan.

```text
ESP32 mengirim X-Soil-Token
      → backend mencari soil_plots.sensor_token
      → data langsung masuk ke tanah pemilik token
```

Mode token tidak bergantung pada tanah aktif.

## Kepemilikan dan isolasi data

Setiap `soil_plots` memiliki `user_id`. Controller selalu memeriksa bahwa `soilPlot->user_id` sama dengan ID user yang login.

Jika user mencoba membuka tanah milik orang lain, aplikasi mengembalikan HTTP 404. Pemakaian 404 tidak membocorkan apakah resource tersebut benar-benar ada.

## Mengapa PostgreSQL memakai TRUE/FALSE literal?

Project memakai Supabase Transaction Pooler port 6543 dan `PDO::ATTR_EMULATE_PREPARES`. PostgreSQL memiliki tipe boolean asli, sehingga query status aktif ditulis dengan:

```sql
is_active IS TRUE
is_active = TRUE
is_active = FALSE
```

Ini mencegah error `operator does not exist: boolean = integer`.

