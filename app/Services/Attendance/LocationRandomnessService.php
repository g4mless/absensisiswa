<?php

namespace App\Services\Attendance;

class LocationRandomnessService
{
    /**
     * Analisis seberapa random koordinat GPS selama sampling.
     *
     * Setiap sample: ['latitude' => float, 'longitude' => float, 'accuracy' => ?float, 'timestamp' => ?int]
     *
     * @param  array<int, array{latitude: mixed, longitude: mixed, accuracy?: mixed, timestamp?: mixed}>  $samples
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
     *     flags: list<string>
     * }
     */
    public function analyze(array $samples, array $overrides = []): array
    {
        $config = array_merge(config('attendance.gps_randomness', []), $overrides);

        $highRatio = (float) ($config['high_suspicion_duplicate_ratio'] ?? 0.999);
        $mediumRatio = (float) ($config['medium_suspicion_duplicate_ratio'] ?? 0.8);
        $lowSpread = (float) ($config['low_spread_meters'] ?? 1.0);
        $precision = (int) ($config['coordinate_precision'] ?? 6);

        $normalized = $this->normalizeSamples($samples, $precision);
        $count = count($normalized);

        if ($count === 0) {
            return [
                'sample_count' => 0,
                'unique_coordinates' => 0,
                'duplicate_ratio' => 0.0,
                'max_spread_meters' => 0.0,
                'mean_spread_meters' => 0.0,
                'variance_meters2' => 0.0,
                'risk_level' => 'unknown',
                'is_suspicious' => false,
                'flags' => ['insufficient_samples'],
            ];
        }

        // 1-3. Hitung unique koordinat & duplicate ratio.
        // duplicate_ratio = proporsi grup identik terbesar (max_freq / total),
        // sehingga 5 sample persis sama => 1.0 dan 4 dari 5 sama => 0.8.
        $keys = array_map(fn ($s) => $s['key'], $normalized);
        $unique = count(array_unique($keys));
        $frequencies = array_count_values($keys);
        $maxFrequency = $frequencies === [] ? 0 : max($frequencies);
        $duplicateRatio = $count > 0 ? round($maxFrequency / $count, 4) : 0.0;

        // 4-6. Jarak antar sample (haversine), max spread, variance/spread.
        $distances = $this->pairwiseDistances($normalized);
        $maxSpread = $distances === [] ? 0.0 : round(max($distances), 2);
        $meanSpread = $distances === [] ? 0.0 : round(array_sum($distances) / count($distances), 2);
        $variance = $distances === [] ? 0.0 : round($this->variance($distances), 4);

        $flags = [];
        $riskLevel = 'normal';
        $isSuspicious = false;

        $minSamples = (int) ($config['min_samples'] ?? 5);
        if ($count < $minSamples) {
            $flags[] = 'insufficient_samples';
        }

        // Satu sample saja tidak bisa dinilai randomness-nya.
        if ($count < 2) {
            return [
                'sample_count' => $count,
                'unique_coordinates' => $unique,
                'duplicate_ratio' => $duplicateRatio,
                'max_spread_meters' => $maxSpread,
                'mean_spread_meters' => $meanSpread,
                'variance_meters2' => $variance,
                'risk_level' => $count < $minSamples ? 'unknown' : 'normal',
                'is_suspicious' => false,
                'flags' => $flags,
            ];
        }

        /*
        | Logic indikasi (bukan kepastian):
        | - 100% exact duplicate          -> suspicious tinggi
        | - 80%+ exact duplicate          -> suspicious sedang
        | - beberapa titik beda bbrp mtr  -> normal (GPS jitter)
        | - spread sangat kecil tapi tidak exact duplicate -> risk signal saja
        */
        if ($duplicateRatio >= $highRatio || $unique === 1) {
            $flags[] = 'coordinates_too_static';
            $flags[] = 'possible_location_spoofing';
            $riskLevel = 'high';
            $isSuspicious = true;
        } elseif ($duplicateRatio >= $mediumRatio) {
            $flags[] = 'coordinates_too_static';
            $flags[] = 'possible_location_spoofing';
            $riskLevel = 'medium';
            $isSuspicious = true;
        } elseif ($maxSpread <= $lowSpread) {
            // Tidak exact duplicate, tapi nyaris tidak bergerak sama sekali.
            $flags[] = 'low_movement_variance';
            $riskLevel = 'low';
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
     * @return list<array{latitude: float, longitude: float, accuracy: ?float, key: string}>
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

            $result[] = [
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => is_numeric($accuracy) ? (float) $accuracy : null,
                'key' => sprintf("%.{$precision}F:%.{$precision}F", $lat, $lng),
            ];
        }

        return $result;
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
}
