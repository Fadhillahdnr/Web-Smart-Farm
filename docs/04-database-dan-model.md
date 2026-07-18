# 4. Database, Migration, Model, dan Relasi

## Mengapa database diperlukan?

Database menyimpan data secara permanen. Jika server restart, data user dan sensor tetap ada.

Project ini menggunakan PostgreSQL melalui Supabase.

## Diagram relasi

```text
users
  id (PK)
   │
   │ 1 user memiliki banyak tanah
   ▼
soil_plots
  id (PK)
  user_id (FK → users.id)
   │
   │ 1 tanah memiliki banyak data sensor
   ▼
sensor_data
  id (PK)
  soil_plot_id (FK → soil_plots.id)
```

Keterangan:

- **PK / Primary Key**: identitas unik record.
- **FK / Foreign Key**: kolom penghubung ke tabel lain.
- Relasi ini disebut `hasMany` dari arah parent dan `belongsTo` dari arah child.

## Tabel `users`

Kolom penting:

| Kolom | Fungsi |
|---|---|
| `id` | ID unik user |
| `name` | Nama user |
| `email` | Email unik untuk login |
| `password` | Password yang sudah di-hash |
| `email_verified_at` | Waktu verifikasi email |
| `remember_token` | Mendukung fitur remember me |
| `created_at`, `updated_at` | Waktu pembuatan/perubahan |

Password tidak pernah disimpan sebagai teks asli. Laravel menyimpannya dalam bentuk hash satu arah.

## Tabel `soil_plots`

| Kolom | Fungsi |
|---|---|
| `id` | ID tanah |
| `user_id` | Pemilik tanah |
| `name` | Nama seperti Tanah A |
| `sensor_token` | Token unik opsional untuk alat |
| `is_active` | Tujuan rekaman ESP32 tanpa token |
| timestamps | Waktu pembuatan/perubahan |

Constraint penting:

- `sensor_token` harus unik.
- Kombinasi `user_id + name` harus unik. Dua user boleh sama-sama memiliki “Tanah A”, tetapi satu user tidak boleh membuat nama yang sama dua kali.
- Menghapus user akan menghapus tanahnya melalui cascade.

## Tabel `sensor_data`

| Kolom | Tipe | Fungsi |
|---|---|---|
| `id` | bigint | ID pembacaan |
| `soil_plot_id` | foreign key | Tanah tujuan |
| `moisture` | integer | Kelembapan 0–100% |
| `ph` | float | pH 0–14 |
| `color` | string | HITAM/COKLAT/MERAH/dll. |
| `status` | string | SUBUR/CUKUP SUBUR/TIDAK SUBUR |
| `battery` | integer | Baterai 0–100% |
| timestamps | timestamp | Waktu data diterima server |

Menghapus tanah akan menghapus seluruh `sensor_data` milik tanah tersebut melalui cascade.

## Tabel framework

Laravel juga memakai:

- `sessions`: session login bila `SESSION_DRIVER=database`.
- `password_reset_tokens`: token reset password.
- `cache` dan `cache_locks`: cache berbasis database.
- `jobs`, `job_batches`, `failed_jobs`: antrean pekerjaan.
- `migrations`: daftar migration yang sudah dijalankan.

## Apa itu migration?

Migration adalah version control untuk struktur database. Daripada mengubah tabel manual tanpa catatan, perubahan ditulis sebagai file PHP.

Urutan migration project:

1. Membuat users, session, reset password.
2. Membuat cache.
3. Membuat jobs.
4. Membuat sensor data.
5. Menambah baterai.
6. Membuat soil plots dan menghubungkan data sensor.
7. Menambah status tanah aktif.

Data sensor versi lama dipindahkan ke kelompok **Data Lama** milik user pertama agar tidak hilang.

## Model Eloquent

Relasi user:

```php
public function soilPlots(): HasMany
{
    return $this->hasMany(SoilPlot::class);
}
```

Pemakaian:

```php
$user->soilPlots;
$user->soilPlots()->create([...]);
```

Relasi tanah:

```php
public function sensorData(): HasMany
{
    return $this->hasMany(SensorData::class);
}
```

Relasi sensor:

```php
public function soilPlot(): BelongsTo
{
    return $this->belongsTo(SoilPlot::class);
}
```

## Fillable, hidden, dan casts

`$fillable` menentukan field yang boleh diisi secara massal. Ini membantu mencegah mass-assignment yang tidak diinginkan.

`$hidden = ['sensor_token']` mencegah token ikut tampil saat model otomatis diubah menjadi JSON.

`casts()` mengubah tipe database menjadi tipe PHP yang benar:

```php
'is_active' => 'boolean'
'moisture' => 'integer'
'ph' => 'float'
```

## Mengapa data terbaru diurutkan berdasarkan ID?

Data sensor dikirim berurutan dan ID selalu bertambah. Query memakai:

```php
orderByDesc('id')
```

Cara ini deterministik meskipun dua record mempunyai `created_at` yang sama hingga satuan detik.

