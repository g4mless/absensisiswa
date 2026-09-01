<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['classes', 'homeroom_teachers', 'program_heads'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'academic_year_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            });
        }

        Schema::dropIfExists('academic_years');
    }

    public function down(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        foreach (['classes', 'homeroom_teachers', 'program_heads'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'academic_year_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }
};
