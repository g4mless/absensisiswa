<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->string('selfie_path')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropColumn('selfie_path');
        });
    }
};
