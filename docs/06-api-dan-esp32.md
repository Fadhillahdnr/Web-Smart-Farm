# 6. API Sensor dan Integrasi ESP32

## Endpoint

```http
POST /api/sensor
Content-Type: application/json
```

Production:

```text
https://smart-farm.laravel.cloud/api/sensor
```

## Payload firmware saat ini

```json
{
  "moisture": 65,
  "ph": 6.80,
  "color": "COKLAT",
  "status": "SUBUR",
  "battery": 85
}
```

## Aturan validasi

| Field | Aturan |
|---|---|
| `moisture` | Wajib integer 0–100 |
| `ph` | Wajib angka 0–14 |
| `color` | Wajib string, maksimal 50 karakter |
| `status` | Wajib string, maksimal 50 karakter |
| `battery` | Wajib integer 0–100 |
| `soil_token` | Opsional, tepat 48 karakter |

Jika nilai tidak valid, Laravel membalas HTTP 422 beserta daftar error.

## Mode tanpa token: firmware yang sekarang

Firmware saat ini tidak mengirim identitas alat. Backend mencari tanah dengan `is_active IS TRUE`.

Sebelum menyalakan alat atau mulai mengukur:

1. Login dashboard.
2. Pilih tanah.
3. Tekan **Mulai Rekam**.
4. Pastikan label “Sedang merekam ke ...” terlihat.

Tidak ada perubahan kode Arduino yang diperlukan.

## Mode token: pengembangan masa depan

Token dapat dikirim melalui header:

```cpp
http.addHeader("X-Soil-Token", "TOKEN_48_KARAKTER");
```

Atau field JSON `soil_token`. Header lebih disarankan karena memisahkan credential dari data sensor.

Jika token tersedia, backend mengutamakan token dan tidak memakai tanah aktif.

## Response sukses

```json
{
  "success": true,
  "soil": "Tanah A",
  "data": {
    "id": 123,
    "soil_plot_id": 1,
    "moisture": 65,
    "ph": 6.8,
    "color": "COKLAT",
    "status": "SUBUR",
    "battery": 85
  }
}
```

HTTP status: `201 Created`.

## Response belum ada tanah aktif

```json
{
  "success": false,
  "message": "Belum ada tanah aktif. Pilih tanah dan tekan Mulai Rekam pada dashboard."
}
```

HTTP status: `409 Conflict`.

## Response token salah

HTTP status: `401 Unauthorized`.

## Frekuensi pengiriman

Firmware menjalankan `delay(5000)` setelah pengiriman. Jadi jeda minimum adalah 5 detik, tetapi satu siklus juga meliputi pembacaan pH, warna, baterai, fuzzy logic, OLED, Wi-Fi, dan HTTP.

Waktu nyata biasanya lebih dari 5 detik dan dapat sekitar 7–10 detik tergantung sensor dan jaringan.

## Menguji API dengan cURL

Aktifkan satu tanah dahulu, lalu:

```bash
curl -X POST https://smart-farm.laravel.cloud/api/sensor \
  -H "Content-Type: application/json" \
  -d '{
    "moisture": 65,
    "ph": 6.8,
    "color": "COKLAT",
    "status": "SUBUR",
    "battery": 85
  }'
```

Mode token:

```bash
curl -X POST https://smart-farm.laravel.cloud/api/sensor \
  -H "Content-Type: application/json" \
  -H "X-Soil-Token: TOKEN_48_KARAKTER" \
  -d '{"moisture":65,"ph":6.8,"color":"COKLAT","status":"SUBUR","battery":85}'
```

## Keterbatasan firmware saat ini

Saat `WiFi.status() != WL_CONNECTED`, fungsi pengiriman hanya berhenti dan kembali. Firmware belum menjalankan strategi reconnect eksplisit. Backend tidak dapat menyalakan kembali radio Wi-Fi ESP32 dari jarak jauh.

`client.setInsecure()` juga berarti sertifikat HTTPS tidak diverifikasi. Ini mempermudah koneksi, tetapi untuk perangkat production idealnya gunakan CA certificate.

## Membaca Serial Monitor

Firmware mencetak HTTP code:

```text
[API] HTTP CODE : 201
```

Interpretasi cepat:

- `201`: berhasil disimpan.
- `409`: belum menekan Mulai Rekam.
- `422`: nilai/field JSON tidak valid.
- `500`: masalah server, periksa Laravel Cloud log.
- Nilai negatif dari HTTPClient: kegagalan jaringan/TLS sebelum mendapat response server.

