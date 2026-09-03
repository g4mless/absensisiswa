<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->json('gps_samples')->nullable()->after('source');
            $table->unsignedTinyInteger('sample_count')->nullable()->after('gps_samples');
            $table->unsignedTinyInteger('unique_coordinates')->nullable()->after('sample_count');
            $table->decimal('duplicate_ratio', 5, 4)->nullable()->after('unique_coordinates');
            $table->decimal('max_spread_meters', 10, 2)->nullable()->after('duplicate_ratio');
            $table->boolean('is_location_suspicious')->default(false)->after('max_spread_meters');
            $table->json('location_flags')->nullable()->after('is_location_suspicious');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'gps_samples',
                'sample_count',
                'unique_coordinates',
                'duplicate_ratio',
                'max_spread_meters',
                'is_location_suspicious',
                'location_flags',
            ]);
        });
    }
};
