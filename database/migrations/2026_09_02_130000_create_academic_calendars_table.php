<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->date('month')->unique();
            $table->timestamps();
        });

        Schema::create('academic_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_calendar_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->timestamps();
            $table->unique(['academic_calendar_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_holidays');
        Schema::dropIfExists('academic_calendars');
    }
};
