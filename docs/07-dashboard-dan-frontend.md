# 7. Dashboard dan Frontend Realtime

## Teknologi tampilan

- Blade menghasilkan HTML awal di server.
- Tailwind CSS menyediakan utility class.
- Chart.js menggambar grafik.
- JavaScript browser melakukan polling dan manipulasi DOM.
- Alpine.js dipakai oleh komponen Breeze seperti dropdown dan modal profil.
- Vite melakukan build asset CSS/JavaScript.

## Render awal

Saat `GET /dashboard`, controller mengambil:

- Semua tanah milik user.
- Tanah yang dipilih dari query `?soil=id`, atau tanah pertama.
- Maksimal 20 histori terbaru.

Variabel tersebut diberikan ke Blade:

```php
return view('dashboard', compact(
    'soilPlots',
    'selectedSoil',
    'history'
));
```

Blade membentuk dropdown, tombol, card, tabel, dan data awal grafik.

## Asset CSS dan JavaScript

Layout memuat asset hasil Vite:

```blade
@vite('resources/css/app.css')
```

Layout aplikasi Breeze memuat CSS dan `resources/js/app.js`. File `app.js` menyalakan Alpine. Script dashboard khusus berada pada Blade karena membutuhkan URL dan data yang dibuat Laravel.

## Polling snapshot

Dashboard membuat URL aman dari named route:

```javascript
const snapshotUrl = @json(route('dashboard.snapshot', $selectedSoil));
```

Setiap tiga detik:

```javascript
setInterval(refresh, 3000);
```

`refresh()` meminta JSON dengan `cache: 'no-store'`, kemudian memperbarui tampilan.

## Mengapa satu endpoint snapshot?

Versi awal meminta `latest` dan `history` secara paralel. Itu berarti dua request, dua proses session, dan lebih banyak koneksi database setiap tiga detik.

Snapshot menggabungkannya:

```json
{
  "latest": { "...": "..." },
  "history": ["..."],
  "recording": true
}
```

Hasilnya lebih ringan untuk Laravel Cloud dan Supabase pooler.

## Pembaruan card dan progress bar

`latest` dipakai untuk:

- `soil`: moisture + `%`
- `ph`: nilai pH
- `color`: klasifikasi warna
- `battery`: baterai + `%`
- `status`: hasil fuzzy

Lebar progress pH dihitung dari skala 0–14:

```text
lebar = ph / 14 × 100%
```

## Grafik

Grafik memiliki dua dataset:

- Kelembapan
- pH

Histori dari backend berurutan terbaru ke lama. Sebelum diberikan ke grafik, array dibalik agar waktu bergerak dari kiri ke kanan.

Grafik hanya diganti ketika ID data terbaru berubah, sehingga polling yang tidak menemukan data baru tidak menambahkan titik duplikat.

## Tabel histori dan keamanan XSS

JavaScript membuat elemen tabel dan mengisi nilai menggunakan `textContent`, bukan menyisipkan string mentah dengan `innerHTML`.

Ini penting karena nilai seperti `color` dan `status` berasal dari request perangkat. `textContent` membuat karakter HTML dianggap teks, bukan kode yang dijalankan.

## Status koneksi

Dashboard membedakan:

- **Sensor online**: `created_at` data terbaru berumur maksimal 15 detik.
- **Sensor tidak mengirim**: data terakhir lebih lama dari 15 detik.
- **Terputus**: browser gagal meminta snapshot backend.

Batas 15 detik memberi toleransi karena satu siklus ESP32 lebih lama dari `delay(5000)`.

## Empty state

Jika user belum memiliki tanah, dashboard tidak mencoba membuat grafik atau polling. User diarahkan untuk membuat tanah pertama.

## Responsive design

Tailwind mengubah layout berdasarkan ukuran layar:

- Mobile: satu kolom.
- Tablet: beberapa card per baris.
- Desktop: lima card dan grid grafik/tabel.

Contoh class:

```text
grid-cols-1 sm:grid-cols-2 lg:grid-cols-5
```

## Mengubah tampilan dengan aman

Setelah mengubah Blade atau CSS:

```bash
npm run build
```

Periksa browser console dan tab Network. Jangan memakai Tailwind CDN di production karena project sudah memiliki pipeline Vite.

