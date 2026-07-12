# 5. Seluruh Flow Aplikasi

## Flow pengunjung

```text
Buka /
  → redirect /login
  → pilih Login atau Create Account
```

Route autentikasi dilindungi middleware `guest`. User yang sudah login akan diarahkan kembali ke dashboard.

## Flow register

```text
GET /register
  → tampilkan form
POST /register
  → validasi nama, email, password
  → pastikan email unik
  → hash password
  → simpan User
  → login otomatis
  → redirect /dashboard
```

Password harus memiliki konfirmasi yang sama. Jika validasi gagal, Laravel kembali ke form membawa error dan input lama yang aman.

## Flow login

```text
GET /login
  → tampilkan form
POST /login
  → LoginRequest melakukan validasi
  → Auth::attempt(email, password)
  → regenerate session ID
  → redirect ke tujuan/dashboard
```

Login dibatasi lima percobaan berdasarkan kombinasi email dan IP untuk mengurangi brute force.

## Remember me

Checkbox `remember` membuat login dapat bertahan lebih lama menggunakan remember token. Token disimpan pada tabel users dan cookie aman Laravel.

## Logout

```text
POST /logout
  → logout guard web
  → invalidate session
  → regenerate CSRF token
  → redirect /login
```

Logout wajib menggunakan POST, bukan GET, agar situs lain tidak dapat memaksa user logout hanya dengan gambar/link.

## Lupa dan reset password

```text
GET /forgot-password
POST /forgot-password
  → buat token
  → kirim link melalui mailer
GET /reset-password/{token}
POST /reset-password
  → validasi token, email, password baru
  → update hash password
  → redirect login
```

Fitur baru benar-benar mengirim email bila mailer production sudah dikonfigurasi. Dengan `MAIL_MAILER=log`, email hanya masuk log.

## Flow profil

User dapat:

- Mengubah nama dan email.
- Mengubah password dengan memasukkan password lama.
- Menghapus akun dengan konfirmasi password.

Jika akun dihapus, foreign key cascade menghapus seluruh tanah dan data sensornya.

## Flow dashboard pertama kali

```text
User baru login
  → belum ada tanah
  → dashboard menampilkan empty state
  → isi "Tanah A"
  → POST /soil-plots
  → tanah dibuat dengan user_id dan token unik
  → redirect dashboard?soil={id}
```

## Flow memilih tanah

Dropdown memindahkan browser ke:

```text
/dashboard?soil=1
```

`DashboardController` hanya mencari ID tersebut dari relasi tanah milik user. ID milik user lain menghasilkan 404.

Memilih tanah berarti **melihat** data tanah tersebut. Memilih belum otomatis berarti merekam.

## Flow mulai rekaman

```text
Klik Mulai Rekam
  → PATCH /soil-plots/{id}/activate
  → cek kepemilikan
  → mulai database transaction
  → lock semua soil plot
  → ubah tanah aktif lama menjadi FALSE
  → ubah tanah terpilih menjadi TRUE
  → commit
  → redirect dengan pesan sukses
```

Lock dan transaction mencegah dua aktivasi bersamaan menghasilkan dua tanah aktif.

## Flow berhenti rekaman

```text
Klik Hentikan Rekaman
  → PATCH /soil-plots/{id}/deactivate
  → is_active = FALSE
```

Setelah dihentikan, data ESP32 tanpa token mendapat HTTP 409 dan tidak disimpan sampai ada tanah aktif.

## Flow data ESP32

```text
ESP32 membaca sensor
  → membuat JSON
  → POST /api/sensor
  → validasi
  → cari tanah aktif
  → INSERT sensor_data
  → response HTTP 201
  → tunggu delay dan ulangi
```

## Flow realtime browser

```text
Dashboard terbuka
  → render 20 data awal
  → setiap 3 detik GET snapshot
  → response latest + history
  → update card/grafik/tabel/status
```

Browser tidak menerima data melalui WebSocket. Istilah realtime di project ini menggunakan **polling**, yaitu browser bertanya berkala.

## Flow rename dan hapus tanah

Rename memvalidasi agar nama tidak kosong, maksimal 100 karakter, dan unik per user.

Delete memakai HTTP DELETE dan konfirmasi browser. Penghapusan bersifat permanen karena histori ikut terhapus melalui cascade.

## Flow ekspor Excel

```text
Klik Export
  → GET /soil-plots/{id}/export-excel
  → cek kepemilikan
  → SensorDataExport query seluruh data tanah
  → buat file .xlsx
  → browser mengunduh file
```

Kolom Excel: waktu, kelembapan, pH, warna, baterai, dan status.

## Ringkasan status HTTP

| Status | Arti dalam project |
|---|---|
| 200 | Request baca berhasil |
| 201 | Data sensor berhasil dibuat |
| 302 | Redirect, misalnya selesai login |
| 401 | Token tanah salah |
| 404 | Route/resource tidak ditemukan atau bukan milik user |
| 409 | Belum ada tanah aktif |
| 422 | Validasi input gagal |
| 500 | Error internal yang harus diperiksa di log |

