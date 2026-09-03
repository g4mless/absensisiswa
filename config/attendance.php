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

        // Duplicate ratio = proporsi grup identik terbesar (max_freq / total).
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

        /*
        |--------------------------------------------------------------------------
        | Skor gabungan multi-sinyal (Fake GPS dengan noise buatan)
        |--------------------------------------------------------------------------
        |
        | Fake GPS modern bisa menambahkan variasi kecil sehingga lolos dari
        | cek exact-duplicate. Karena itu risiko dihitung dari 6 sinyal
        | independen. Satu sinyal TIDAK PERNAH cukup untuk penolakan:
        | bobot terbesar (50) selalu di bawah reject_score (60).
        |
        */
        'risk_weights' => [
            'static' => 50,        // S1: exact duplicate (penuh jika high, setengah jika medium)
            'linear' => 20,        // S2: jalur terlalu lurus
            'uniform_steps' => 15, // S3: langkah terlalu seragam
            'accuracy' => 15,      // S4: accuracy konstan / terlalu bagus
            'timing' => 5,         // S5: interval timestamp terlalu sempurna
            'sensor' => 5,         // S6: speed/heading browser inkonsisten
        ],

        // >= reject_score -> check-in DITOLAK (422).
        'reject_score' => 60,
        // >= flag_score -> check-in lolos tetapi diberi flag + warning.
        'flag_score' => 25,

        // S2: jalur dianggap "terlalu lurus" jika std-dev bearing (derajat)
        // di bawah nilai ini DAN net displacement di atas batas minimum.
        // GPS diam asli: bearing acak ke segala arah (std besar).
        'linear_bearing_std_deg' => 25.0,

        // S2 & S3 & S5 hanya dinilai jika perpindahan bersih (jarak sample
        // pertama ke terakhir) di atas nilai ini (meter). Melindungi pengguna
        // asli yang benar-benar diam (mis. di dalam gedung, langkah ~0).
        'min_net_displacement_m' => 2.0,

        // S3: langkah dianggap "terlalu seragam" jika coefficient of
        // variation (std/mean) jarak antar sample berurutan di bawah ini.
        // GPS asli: campuran langkah kecil + besar (CV tinggi).
        'uniform_steps_cv' => 0.3,

        // S4b: accuracy rata-rata di bawah nilai ini (meter) dianggap
        // "terlalu bagus" jika spread sample justru lebih besar.
        'min_plausible_accuracy_m' => 5.0,

        // S5: interval timestamp dianggap "terlalu sempurna" jika std-dev
        // selisih timestamp berurutan (ms) di bawah nilai ini.
        'regular_timing_std_ms' => 50.0,
    ],

];
