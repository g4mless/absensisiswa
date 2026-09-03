<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GPS Movement Randomness Detection
    |--------------------------------------------------------------------------
    |
    | Menganalisis seberapa random/berubah-ubah koordinat GPS selama sampling
    | untuk memberi indikasi (bukan kepastian) Fake GPS / location spoofing.
    |
    | GPS asli selalu punya jitter alami (beberapa meter), sedangkan Fake GPS
    | sering terlalu sempurna/statis (exact duplicate 100%).
    |
    */

    'gps_randomness' => [
        // Jumlah sample yang diharapkan dari frontend (watchPosition).
        'min_samples' => 5,
        'target_samples' => 8,
        'max_samples' => 10,

        // Durasi sampling maksimum (ms) di frontend.
        'max_sampling_duration_ms' => 10000,

        // Duplicate ratio = 1 - (unique / total).
        // 100% exact duplicate -> suspicious tinggi.
        'high_suspicion_duplicate_ratio' => 0.999, // >= ini => semua sample identik
        // 80%+ exact duplicate -> suspicious sedang.
        'medium_suspicion_duplicate_ratio' => 0.8,

        // Jika max spread antar sample di bawah nilai ini (meter) tetapi
        // TIDAK exact duplicate, anggap hanya risk signal (low spread).
        'low_spread_meters' => 1.0,

        // Spread normal GPS jitter yang masih ditoleransi (meter).
        // Selama titik-titik berbeda dalam batas wajar -> normal.
        'normal_jitter_meters' => 15.0,

        // Presisi pembulatan untuk menentukan "exact duplicate".
        // 6 desimal ~ 0.11 meter. Jangan dibulatkan kasar agar jitter
        // normal tidak salah dianggap duplicate.
        'coordinate_precision' => 6,
    ],

];
