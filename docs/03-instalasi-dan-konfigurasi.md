# 3. Instalasi dan Konfigurasi

## Persyaratan

Siapkan:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- Extension PHP PostgreSQL (`pdo_pgsql`)
- Extension yang diperlukan Laravel Excel seperti XML, GD, dan ZIP
- Database PostgreSQL/Supabase

Periksa versi:

```bash
php -v
composer --version
node -v
npm -v
```

## Instalasi project

```bash
git clone <alamat-repository>
cd Web-Smart-Farm
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Konfigurasi `.env`

`.env` hanya untuk mesin lokal/server dan sudah masuk `.gitignore`. Jangan pernah menaruh password database asli dalam dokumentasi atau commit.

Contoh Supabase Transaction Pooler:

```env
APP_NAME="Web Smart Farm"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=alamat-pooler-supabase
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=user-dari-supabase
DB_PASSWORD=password-rahasia
DB_SSLMODE=require
DB_EMULATE_PREPARES=true

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

`DB_EMULATE_PREPARES=true` penting untuk Supabase port 6543 agar tidak muncul error `prepared statement does not exist`.

## Migration

Migration membuat struktur database:

```bash
php artisan migrate
```

Lihat status migration:

```bash
php artisan migrate:status
```

Jangan memakai `migrate:fresh` pada production karena perintah tersebut menghapus seluruh tabel dan data.

## Menjalankan aplikasi lokal

Cara sederhana dengan dua terminal:

```bash
php artisan serve
```

```bash
npm run dev
```

Atau gunakan script Composer:

```bash
composer run dev
```

Buka `http://localhost:8000`.

## Build frontend production

```bash
npm run build
```

Hasil build berada di `public/build`, tetapi folder ini diabaikan Git karena seharusnya dibuat saat deployment.

## Konfigurasi email

Default lokal menggunakan:

```env
MAIL_MAILER=log
```

Pada mode ini, link reset password ditulis ke `storage/logs/laravel.log`, bukan dikirim ke inbox.

Untuk production, isi SMTP atau layanan email yang dipilih:

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Web Smart Farm"
```

## Konfigurasi production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-aplikasi
DB_SSLMODE=require
DB_EMULATE_PREPARES=true
SESSION_SECURE_COOKIE=true
```

Setelah mengubah environment server:

```bash
php artisan optimize:clear
php artisan config:cache
```

## Perintah sehari-hari

| Perintah | Fungsi |
|---|---|
| `php artisan route:list` | Melihat semua route |
| `php artisan migrate` | Menjalankan migration baru |
| `php artisan test` | Menjalankan test |
| `php artisan optimize:clear` | Membersihkan cache Laravel |
| `php artisan config:cache` | Membuat cache config production |
| `npm run dev` | Menjalankan Vite development |
| `npm run build` | Build asset production |

