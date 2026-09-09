<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_pkl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete()
                ->unique();
            $table->string('tempat_pkl');
            $table->foreignId('pembimbing_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED'])
                ->default('PLANNED');
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('pkl_location_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_pkl_id')
                ->constrained('student_pkl')
                ->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['student_pkl_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_location_logs');
        Schema::dropIfExists('student_pkl');
    }
};
