<?php

namespace App\Services;

class GpsValidationService
{
    protected const EARTH_RADIUS_METERS = 6371000;

    /**
     * Hitung jarak Haversine dalam meter antara dua titik koordinat.
     * Seluruh validasi jarak wajib memakai perhitungan server-side ini,
     * jangan percaya hasil perhitungan dari frontend.
     */
    public function distanceInMeters(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): float {
        $lat1 = deg2rad($latitude1);
        $lon1 = deg2rad($longitude1);
        $lat2 = deg2rad($latitude2);
        $lon2 = deg2rad($longitude2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2
            + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Cek apakah titik berada dalam radius (meter) dari titik pusat.
     */
    public function withinRadius(
        float $latitude,
        float $longitude,
        float $centerLatitude,
        float $centerLongitude,
        float $radiusMeters
    ): bool {
        return $this->distanceInMeters(
            $latitude,
            $longitude,
            $centerLatitude,
            $centerLongitude
        ) <= $radiusMeters;
    }
}
