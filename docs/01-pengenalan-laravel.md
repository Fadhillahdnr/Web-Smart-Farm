# 1. Pengenalan Laravel dan Struktur Project

## Apa itu Laravel?

Laravel adalah framework PHP. Framework dapat dianggap sebagai kerangka kerja yang menyediakan aturan, folder, dan alat siap pakai agar developer tidak perlu membangun semuanya dari nol.

Tanpa framework, developer harus membuat sendiri sistem route, koneksi database, session login, validasi, keamanan form, dan banyak hal lain. Laravel sudah menyediakan fondasi tersebut.

## Siklus sebuah request

Ketika user membuka dashboard, proses sederhananya adalah:

```text
1. Browser meminta GET /dashboard
2. public/index.php menerima request
3. Laravel melakukan bootstrap aplikasi
4. routes/web.php mencari route /dashboard
5. Middleware auth memeriksa login
6. DashboardController menjalankan method index()
7. Model mengambil data dari database
8. dashboard.blade.php dirender menjadi HTML
9. HTML dikirim kembali ke browser
```

Untuk request API ESP32:

```text
ESP32
  → POST /api/sensor
  → routes/api.php
  → SensorController::store()
  → validasi payload
  → cari tanah aktif/token
  → simpan melalui relasi Eloquent
  → balasan JSON HTTP 201
```

## Konsep MVC

Laravel sering dijelaskan dengan pola **MVC**:

### Model

Model mewakili data dan tabel database.

- `User` mewakili tabel `users`.
- `SoilPlot` mewakili tabel `soil_plots`.
- `SensorData` mewakili tabel `sensor_data`.

Model berada di `app/Models`.

### View

View adalah tampilan yang dilihat user. Project ini memakai Blade, yaitu template HTML milik Laravel.

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/profile/edit.blade.php`

### Controller

Controller menerima request, mengatur proses, dan menentukan response.

- `DashboardController` menyiapkan dashboard dan snapshot realtime.
- `SoilPlotController` mengelola tanah.
- `SensorController` menerima data ESP32.
- Controller di folder `Auth` mengelola autentikasi.

## Route

Route menghubungkan URL dengan controller.

Contoh konsep:

```php
Route::get('/dashboard', [DashboardController::class, 'index']);
```

Artinya request `GET /dashboard` menjalankan method `index()` pada `DashboardController`.

Project ini memiliki dua file route utama:

- `routes/web.php`: halaman browser yang memakai cookie, session, dan CSRF.
- `routes/api.php`: endpoint perangkat/API yang menghasilkan JSON.

## Middleware

Middleware adalah pemeriksaan yang berjalan sebelum controller.

Route dashboard berada dalam middleware `auth`. Jika belum login, user diarahkan ke halaman login. Route login/register memakai middleware `guest`, sehingga user yang sudah login tidak perlu membuka form autentikasi lagi.

## Request dan response

Request adalah data masuk ke aplikasi. Response adalah balasan aplikasi.

Contoh response web:

```php
return view('dashboard', $data);
```

Contoh response JSON:

```php
return response()->json([
    'success' => true,
], 201);
```

Angka `201` adalah HTTP status yang berarti data berhasil dibuat.

## Blade

Blade memungkinkan PHP ditulis dengan sintaks yang lebih mudah di HTML:

```blade
{{ $selectedSoil->name }}

@if($selectedSoil->is_active)
    <p>Sedang merekam</p>
@endif
```

`{{ ... }}` secara otomatis melakukan escaping HTML untuk membantu mencegah XSS.

## Artisan, Composer, npm, dan Vite

- `php artisan` adalah CLI Laravel.
- `composer` mengelola library PHP.
- `npm` mengelola library frontend.
- `Vite` mengubah sumber CSS/JS menjadi asset production.

Contoh:

```bash
php artisan migrate
php artisan test
composer install
npm install
npm run build
```

## Berkas konfigurasi

Laravel membaca konfigurasi dari folder `config/`. Nilai yang berbeda untuk setiap komputer/server diletakkan di `.env`.

Contoh:

```env
DB_CONNECTION=pgsql
DB_PORT=6543
```

Kode membacanya melalui:

```php
env('DB_PORT', '5432')
```

Jangan memanggil `env()` langsung dari controller. Gunakan `env()` di file konfigurasi, lalu controller membaca `config()` bila diperlukan.

