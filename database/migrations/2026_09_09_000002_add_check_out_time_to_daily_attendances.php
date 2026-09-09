<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('daily_attendances', 'check_out_time')) {
            Schema::table('daily_attendances', function (Blueprint $table) {
                $table->time('check_out_time')->nullable()->after('check_in_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daily_attendances', 'check_out_time')) {
            Schema::table('daily_attendances', function (Blueprint $table) {
                $table->dropColumn('check_out_time');
            });
        }
    }
};
