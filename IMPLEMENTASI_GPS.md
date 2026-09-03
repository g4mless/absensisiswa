Implementasikan **GPS movement randomness detection** untuk fitur absensi Laravel.

### Tujuan

Saat user melakukan absensi, browser harus mengambil beberapa sample geolocation secara berurutan menggunakan:

`navigator.geolocation.watchPosition()`

Kumpulkan sekitar **5–10 sample dalam 5–10 detik**.

Setiap sample simpan:

* latitude
* longitude
* accuracy
* timestamp

### Deteksi GPS terlalu statis

Buat service Laravel untuk menganalisis seluruh sample.

Hitung:

1. Berapa banyak sample yang memiliki koordinat latitude + longitude **persis sama**.
2. Berapa jumlah koordinat unik.
3. Duplicate ratio.
4. Jarak antar sample dalam meter.
5. Jarak maksimum antar sample.
6. Variance/spread posisi.

Tujuan utama adalah mendeteksi pola seperti:

```text
sample 1 → -6.200123, 106.816742
sample 2 → -6.200123, 106.816742
sample 3 → -6.200123, 106.816742
sample 4 → -6.200123, 106.816742
sample 5 → -6.200123, 106.816742
```

Pola seperti ini harus diberi flag:

`possible_location_spoofing`

Sebaliknya, pola seperti:

```text
sample 1 → -6.20012, 106.81674
sample 2 → -6.20010, 106.81673
sample 3 → -6.20013, 106.81676
sample 4 → -6.20011, 106.81675
sample 5 → -6.20012, 106.81672
```

harus dianggap sebagai **normal GPS jitter**, selama perbedaannya masih dalam batas yang wajar.

### Penting

Jangan membuat rule:

```text
koordinat tidak berubah = pasti Fake GPS
```

Gunakan sebagai **indikasi**, bukan kepastian.

Contoh logic awal:

* 100% sample exact duplicate → suspicious tinggi
* 80%+ exact duplicate → suspicious sedang
* beberapa titik berbeda beberapa meter → normal
* spread sangat kecil tetapi tidak exact duplicate → hanya risk signal
* GPS jitter normal jangan dianggap spoofing

Threshold harus configurable di:

```text
config/attendance.php
```

### Backend

Buat service terpisah:

```text
app/Services/Attendance/LocationRandomnessService.php
```

Service mengembalikan hasil seperti:

```php
[
    'sample_count' => 5,
    'unique_coordinates' => 1,
    'duplicate_ratio' => 1.0,
    'max_spread_meters' => 0.0,
    'is_suspicious' => true,
    'flags' => [
        'coordinates_too_static',
    ],
]
```

### Frontend

Gunakan `watchPosition()` dengan:

```js
{
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 10000
}
```

Kumpulkan sample selama maksimal 10 detik atau sampai jumlah sample yang ditentukan tercapai.

Kirim **seluruh sample**, bukan hanya koordinat terakhir, ke Laravel.

### Catatan

Jangan menggunakan IP geolocation, VPN detection, device fingerprint, atau mekanisme lain untuk requirement ini.

Fokus hanya pada:

**seberapa random / berubah-ubah koordinat GPS selama sampling.**

Tujuan sistem adalah mendeteksi **GPS yang terlalu sempurna/statis sebagai indikasi Fake GPS**, sambil tetap mentoleransi GPS jitter normal.
