<?php

namespace App\Services\Attendance;

class LocationRandomnessService
{
    /**
     * Analisis multi-sinyal untuk mendeteksi indikasi Fake GPS.
     *
     * Fake GPS modern bisa menambahkan variasi kecil sehingga lolos dari cek
     * exact-duplicate saja. Karena itu risiko dihitung dari 6 sinyal:
     *
     *  S1 static        - exact duplicate (proporsi grup identik terbesar)
     *  S2 linear        - arah gerak (bearing) terlalu konsisten / jalur lurus
     *  S3 uniform_steps - besar langkah antar sample terlalu seragam (CV rendah)
     *  S4 accuracy      - accuracy konstan persis, atau terlalu bagus
     *                     dibanding sebaran titik
     *  S5 timing        - interval timestamp terlalu sempurna/teratur
     *  S6 sensor        - speed/heading bawaan browser bertentangan dengan
     *                     perpindahan posisi yang teramati
     *
     * GPS asli yang diam: bearing acak, langkah tidak beraturan, accuracy
     * berfluktuasi, interval tidak persis. Satu sinyal TIDAK PERNAH cukup
     * untuk penolakan (bobot terbesar < reject_score).
     *
     * Setiap sample: ['latitude', 'longitude', 'accuracy'?, 'timestamp'?,
     *                 'speed'?, 'heading'?, 'altitude'?, 'altitude_accuracy'?]
     *
     * @param  array<int, array<string, mixed>>  $samples
     * @param  array<string, mixed>  $overrides  Override threshold config (opsional, untuk testing).
     * @return array{
     *     sample_count: int,
     *     unique_coordinates: int,
     *     duplicate_ratio: float,
     *     max_spread_meters: float,
     *     mean_spread_meters: float,
     *     variance_meters2: float,
     *     risk_level: string,
     *     is_suspicious: bool,
     *     flags: list<string>,
     *     risk_score: int,
     *     risk_action: string,
     *     signals: array<string, int>,
     *     net_displacement_meters: float,
     *     bearing_std_deg: ?float,
     *     consecutive_steps_cv: ?float,
     *     mean_accuracy: ?float
     * }
     */
    public function analyze(array $samples, array $overrides = []): array
    {
        $config = array_merge(config('attendance.gps_randomness', []), $overrides);

        $highRatio = (float) ($config['high_suspicion_duplicate_ratio'] ?? 0.999);
        $mediumRatio = (float) ($config['medium_suspicion_duplicate_ratio'] ?? 0.8);
        $lowSpread = (float) ($config['low_spread_meters'] ?? 1.0);
        $precision = (int) ($config['coordinate_precision'] ?? 6);

        $weights = $config['risk_weights'] ?? [];
        $wStatic = (int) ($weights['static'] ?? 50);
        $wLinear = (int) ($weights['linear'] ?? 20);
        $wUniform = (int) ($weights['uniform_steps'] ?? 15);
        $wAccuracy = (int) ($weights['accuracy'] ?? 15);
        $wTiming = (int) ($weights['timing'] ?? 5);
        $wSensor = (int) ($weights['sensor'] ?? 5);

        $rejectScore = (int) ($config['reject_score'] ?? 60);
        $flagScore = (int) ($config['flag_score'] ?? 25);
        $linearStdDeg = (float) ($config['linear_bearing_std_deg'] ?? 25.0);
        $minNet = (float) ($config['min_net_displacement_m'] ?? 2.0);
        $uniformCv = (float) ($config['uniform_steps_cv'] ?? 0.3);
        $minPlausibleAcc = (float) ($config['min_plausible_accuracy_m'] ?? 5.0);
        $regularTimingStd = (float) ($config['regular_timing_std_ms'] ?? 50.0);
        $minSamples = (int) ($config['min_samples'] ?? 5);

        $emptyResult = function (string $riskLevel, array $flags) {
            return [
                'sample_count' => 0,
                'unique_coordinates' => 0,
                'duplicate_ratio' => 0.0,
                'max_spread_meters' => 0.0,
                'mean_spread_meters' => 0.0,
                'variance_meters2' => 0.0,
                'risk_level' => $riskLevel,
                'is_suspicious' => false,
                'flags' => $flags,
                'risk_score' => 0,
                'risk_action' => 'allow',
                'signals' => [],
                'net_displacement_meters' => 0.0,
                'bearing_std_deg' => null,
                'consecutive_steps_cv' => null,
                'mean_accuracy' => null,
            ];
        };

        $normalized = $this->normalizeSamples($samples, $precision);
        $count = count($normalized);

        if ($count === 0) {
            return $emptyResult('unknown', ['insufficient_samples']);
        }

        // Urutkan kronologis bila semua timestamp tersedia.
        $allHaveTime = array_all($normalized, fn ($s) => $s['timestamp'] !== null);
        if ($allHaveTime) {
            usort($normalized, fn ($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        }

        // 1-3. Unique koordinat & duplicate ratio (proporsi grup terbesar).
        $keys = array_map(fn ($s) => $s['key'], $normalized);
        $unique = count(array_unique($keys));
        $frequencies = array_count_values($keys);
        $maxFrequency = $frequencies === [] ? 0 : max($frequencies);
        $duplicateRatio = $count > 0 ? round($maxFrequency / $count, 4) : 0.0;

        // 4-6. Jarak pairwise, max/mean spread, variance.
        $distances = $this->pairwiseDistances($normalized);
        $maxSpread = $distances === [] ? 0.0 : round(max($distances), 2);
        $meanSpread = $distances === [] ? 0.0 : round(array_sum($distances) / count($distances), 2);
        $variance = $distances === [] ? 0.0 : round($this->variance($distances), 4);

        // Metrik berurutan (berbasis waktu): langkah, bearing, net displacement.
        $steps = $this->consecutiveDistances($normalized);
        $netDisplacement = $count < 2 ? 0.0 : round($this->haversineMeters(
            $normalized[0]['latitude'], $normalized[0]['longitude'],
            $normalized[$count - 1]['latitude'], $normalized[$count - 1]['longitude']
        ), 2);
        $bearings = $this->consecutiveBearings($normalized);
        $bearingStd = $this->circularStdDeg($bearings);
        $stepsCv = $this->coefficientOfVariation($steps);

        $accuracies = array_values(array_filter(
            array_column($normalized, 'accuracy'), fn ($v) => $v !== null
        ));
        $meanAccuracy = $accuracies === []
            ? null
            : round(array_sum($accuracies) / count($accuracies), 2);

        $flags = [];
        $signals = [];

        if ($count < $minSamples) {
            $flags[] = 'insufficient_samples';
        }

        // Satu sample saja tidak bisa dinilai randomness-nya.
        if ($count < 2) {
            $result = $emptyResult($count < $minSamples ? 'unknown' : 'normal', $flags);
            $result['sample_count'] = $count;
            $result['unique_coordinates'] = $unique;
            $result['duplicate_ratio'] = $duplicateRatio;
            $result['mean_accuracy'] = $meanAccuracy;

            return $result;
        }

        // ---- S1: exact duplicate (sinyal lama, dipertahankan) ----
        if ($duplicateRatio >= $highRatio || $unique === 1) {
            $flags[] = 'coordinates_too_static';
            $flags[] = 'possible_location_spoofing';
            $signals['static'] = $wStatic;
        } elseif ($duplicateRatio >= $mediumRatio) {
            $flags[] = 'coordinates_too_static';
            $flags[] = 'possible_location_spoofing';
            $signals['static'] = (int) round($wStatic / 2);
        } elseif ($maxSpread <= $lowSpread) {
            // Tidak exact duplicate, tapi nyaris tidak bergerak sama sekali.
            $flags[] = 'low_movement_variance';
        }

        $movedEnough = $netDisplacement >= $minNet;

        // ---- S2: jalur terlalu lurus (bearing konsisten, bukan acak) ----
        // GPS diam asli menyebar ke segala arah (std bearing besar).
        if ($movedEnough && $bearingStd !== null && $bearingStd < $linearStdDeg) {
            $flags[] = 'movement_too_linear';
            $signals['linear'] = $wLinear;
        }

        // ---- S3: langkah terlalu seragam (CV rendah) ----
        // GPS asli: campuran langkah kecil + besar (CV tinggi).
        if ($movedEnough && $stepsCv !== null && $stepsCv < $uniformCv) {
            $flags[] = 'steps_too_uniform';
            $signals['uniform_steps'] = $wUniform;
        }

        // ---- S4: accuracy mencurigakan ----
        $accuracyHit = false;
        if (count($accuracies) >= 3 && count(array_unique($accuracies)) === 1) {
            // (a) accuracy sama persis di semua sample: GPS asli berfluktuasi.
            $accuracyHit = true;
        } elseif ($meanAccuracy !== null
            && $meanAccuracy < $minPlausibleAcc
            && $maxSpread > $meanAccuracy
        ) {
            // (b) klaim akurasi sangat bagus, tapi titik menyebar lebih lebar
            // dari akurasi yang diklaim: mustahil secara fisik.
            $accuracyHit = true;
        }
        if ($accuracyHit) {
            $flags[] = 'accuracy_suspicious';
            $signals['accuracy'] = $wAccuracy;
        }

        // ---- S5: interval timestamp terlalu sempurna ----
        $deltas = $this->consecutiveTimeDeltas($normalized);
        if ($movedEnough && count($deltas) >= 2 && $this->std($deltas) < $regularTimingStd) {
            $flags[] = 'timing_too_regular';
            $signals['timing'] = $wTiming;
        }

        // ---- S6: sensor browser bertentangan dengan posisi ----
        if ($this->sensorInconsistent($normalized, $netDisplacement)) {
            $flags[] = 'sensor_inconsistent';
            $signals['sensor'] = $wSensor;
        }

        // ---- Skor gabungan ----
        $score = array_sum($signals);
        if ($count < $minSamples) {
            // Sampel sedikit -> bukti lebih lemah, skala proporsional.
            $score = (int) round($score * $count / $minSamples);
        }
        $score = min(100, $score);

        if ($score >= $rejectScore) {
            $riskLevel = 'high';
            $action = 'reject';
            $isSuspicious = true;
        } elseif ($score >= $flagScore) {
            $riskLevel = 'medium';
            $action = 'flag';
            $isSuspicious = true;
        } elseif ($score > 0 || in_array('low_movement_variance', $flags, true)) {
            $riskLevel = 'low';
            $action = 'allow';
            $isSuspicious = false;
        } else {
            $riskLevel = 'normal';
            $action = 'allow';
            $isSuspicious = false;
        }

        return [
            'sample_count' => $count,
            'unique_coordinates' => $unique,
            'duplicate_ratio' => $duplicateRatio,
            'max_spread_meters' => $maxSpread,
            'mean_spread_meters' => $meanSpread,
            'variance_meters2' => $variance,
            'risk_level' => $riskLevel,
            'is_suspicious' => $isSuspicious,
            'flags' => array_values(array_unique($flags)),
            'risk_score' => $score,
            'risk_action' => $action,
            'signals' => $signals,
            'net_displacement_meters' => $netDisplacement,
            'bearing_std_deg' => $bearingStd === null ? null : round($bearingStd, 1),
            'consecutive_steps_cv' => $stepsCv === null ? null : round($stepsCv, 3),
            'mean_accuracy' => $meanAccuracy,
        ];
    }

    /**
     * Koordinat representatif (median) untuk cek radius sekolah.
     * Median lebih tahan outlier dibanding rata-rata.
     *
     * @return array{latitude: float, longitude: float, accuracy: ?float}|null
     */
    public function representativeCoordinate(array $samples): ?array
    {
        $normalized = $this->normalizeSamples($samples, 7);

        if ($normalized === []) {
            return null;
        }

        $lats = array_column($normalized, 'latitude');
        $lngs = array_column($normalized, 'longitude');
        sort($lats);
        sort($lngs);

        $mid = intdiv(count($lats), 2);

        $median = function (array $values) use ($mid): float {
            $n = count($values);
            if ($n % 2 === 1) {
                return (float) $values[$mid];
            }

            return ((float) $values[$mid - 1] + (float) $values[$mid]) / 2;
        };

        $accuracies = array_filter(array_column($normalized, 'accuracy'), fn ($v) => $v !== null);

        return [
            'latitude' => $median($lats),
            'longitude' => $median($lngs),
            'accuracy' => $accuracies === [] ? null : round(array_sum($accuracies) / count($accuracies), 2),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $samples
     * @return list<array{latitude: float, longitude: float, accuracy: ?float, timestamp: ?int, speed: ?float, heading: ?float, altitude: ?float, key: string}>
     */
    private function normalizeSamples(array $samples, int $precision): array
    {
        $result = [];

        foreach ($samples as $sample) {
            if (! is_array($sample)) {
                continue;
            }

            $lat = $sample['latitude'] ?? $sample['lat'] ?? null;
            $lng = $sample['longitude'] ?? $sample['lng'] ?? $sample['lon'] ?? null;

            if (! is_numeric($lat) || ! is_numeric($lng)) {
                continue;
            }

            $lat = (float) $lat;
            $lng = (float) $lng;

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }

            $accuracy = $sample['accuracy'] ?? null;
            $timestamp = $sample['timestamp'] ?? null;
            $speed = $sample['speed'] ?? null;
            $heading = $sample['heading'] ?? null;
            $altitude = $sample['altitude'] ?? $sample['alt'] ?? null;

            $result[] = [
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => is_numeric($accuracy) ? (float) $accuracy : null,
                'timestamp' => is_numeric($timestamp) ? (int) $timestamp : null,
                'speed' => is_numeric($speed) ? (float) $speed : null,
                'heading' => is_numeric($heading) ? (float) $heading : null,
                'altitude' => is_numeric($altitude) ? (float) $altitude : null,
                'key' => sprintf("%.{$precision}F:%.{$precision}F", $lat, $lng),
            ];
        }

        return $result;
    }

    /**
     * Jarak antar sample BERURUTAN (berbasis waktu), bukan pairwise.
     *
     * @param  list<array{latitude: float, longitude: float}>  $samples
     * @return list<float>
     */
    private function consecutiveDistances(array $samples): array
    {
        $distances = [];
        for ($i = 1; $i < count($samples); $i++) {
            $distances[] = $this->haversineMeters(
                $samples[$i - 1]['latitude'], $samples[$i - 1]['longitude'],
                $samples[$i]['latitude'], $samples[$i]['longitude'],
            );
        }

        return $distances;
    }

    /**
     * Bearing (derajat 0-360) antar sample berurutan. Segmen yang terlalu
     * pendek (< 5 cm) diabaikan karena bearing-nya hanya noise kuantisasi.
     *
     * @param  list<array{latitude: float, longitude: float}>  $samples
     * @return list<float>
     */
    private function consecutiveBearings(array $samples): array
    {
        $bearings = [];
        foreach ($this->consecutiveDistances($samples) as $i => $dist) {
            if ($dist < 0.05) {
                continue;
            }
            $bearings[] = $this->bearingDeg(
                $samples[$i]['latitude'], $samples[$i]['longitude'],
                $samples[$i + 1]['latitude'], $samples[$i + 1]['longitude'],
            );
        }

        return $bearings;
    }

    private function bearingDeg(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLon = deg2rad($lon2 - $lon1);

        $y = sin($deltaLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($deltaLon);

        return fmod(rad2deg(atan2($y, $x)) + 360, 360);
    }

    /**
     * Std-dev sirkular (derajat). 0 = semua arah sama (lurus),
     * besar (~100+) = arah acak. Null bila tak ada bearing valid.
     *
     * @param  list<float>  $bearingsDeg
     */
    private function circularStdDeg(array $bearingsDeg): ?float
    {
        $n = count($bearingsDeg);
        if ($n < 2) {
            return null;
        }

        $sinSum = 0.0;
        $cosSum = 0.0;
        foreach ($bearingsDeg as $b) {
            $sinSum += sin(deg2rad($b));
            $cosSum += cos(deg2rad($b));
        }

        $r = sqrt($sinSum ** 2 + $cosSum ** 2) / $n;

        if ($r >= 1) {
            return 0.0;
        }
        if ($r <= 0.000001) {
            return 180.0;
        }

        return rad2deg(sqrt(-2 * log($r)));
    }

    /**
     * Selisih timestamp berurutan (ms). Pasangan dengan delta <= 0
     * atau > 60 detik diabaikan (bukan sampling normal 10 detik).
     *
     * @param  list<array{timestamp: ?int}>  $samples
     * @return list<float>
     */
    private function consecutiveTimeDeltas(array $samples): array
    {
        $deltas = [];
        for ($i = 1; $i < count($samples); $i++) {
            $a = $samples[$i - 1]['timestamp'];
            $b = $samples[$i]['timestamp'];
            if ($a === null || $b === null) {
                continue;
            }
            $delta = $b - $a;
            if ($delta > 0 && $delta <= 60000) {
                $deltas[] = (float) $delta;
            }
        }

        return $deltas;
    }

    /**
     * S6: inkonsistensi positif antara sensor browser vs posisi teramati.
     * Bila data sensor kosong (umum di banyak device), ABSTAIN (false)
     * alih-alih dianggap mencurigakan.
     */
    private function sensorInconsistent(array $samples, float $netDisplacement): bool
    {
        // (i) Heading yang dilaporkan tidak cocok dengan bearing aktual.
        $diffs = [];
        $steps = $this->consecutiveDistances($samples);
        foreach ($steps as $i => $dist) {
            if ($dist < 0.5) {
                continue;
            }
            $heading = $samples[$i + 1]['heading'] ?? null;
            if ($heading === null || $heading < 0 || $heading > 360) {
                continue;
            }
            $bearing = $this->bearingDeg(
                $samples[$i]['latitude'], $samples[$i]['longitude'],
                $samples[$i + 1]['latitude'], $samples[$i + 1]['longitude'],
            );
            $diff = abs($bearing - $heading);
            $diffs[] = min($diff, 360 - $diff);
        }
        if (count($diffs) >= 2 && (array_sum($diffs) / count($diffs)) > 60) {
            return true;
        }

        // (ii) Browser mengklaim bergerak cepat tapi posisi tidak ke mana-mana.
        foreach ($samples as $s) {
            if ($s['speed'] !== null && $s['speed'] > 3 && $netDisplacement < 5) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array{latitude: float, longitude: float}>  $samples
     * @return list<float>
     */
    private function pairwiseDistances(array $samples): array
    {
        $distances = [];
        $n = count($samples);

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $distances[] = $this->haversineMeters(
                    $samples[$i]['latitude'],
                    $samples[$i]['longitude'],
                    $samples[$j]['latitude'],
                    $samples[$j]['longitude'],
                );
            }
        }

        return $distances;
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * asin(min(1, sqrt($a)));
    }

    /**
     * @param  list<float>  $values
     */
    private function variance(array $values): float
    {
        $n = count($values);
        if ($n === 0) {
            return 0.0;
        }

        $mean = array_sum($values) / $n;
        $sum = 0.0;
        foreach ($values as $v) {
            $sum += ($v - $mean) ** 2;
        }

        return $sum / $n;
    }

    /**
     * @param  list<float>  $values
     */
    private function std(array $values): float
    {
        return sqrt($this->variance($values));
    }

    /**
     * Coefficient of variation (std/mean). Null bila mean ~ 0
     * (tidak bergerak sama sekali -> ditangani sinyal S1).
     *
     * @param  list<float>  $values
     */
    private function coefficientOfVariation(array $values): ?float
    {
        $n = count($values);
        if ($n === 0) {
            return null;
        }

        $mean = array_sum($values) / $n;
        if ($mean < 0.000001) {
            return null;
        }

        return $this->std($values) / $mean;
    }
}
