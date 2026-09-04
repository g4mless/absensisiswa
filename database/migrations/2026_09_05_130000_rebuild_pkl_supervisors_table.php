<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pkl_supervisors');

        Schema::create('pkl_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_supervisors');

        Schema::create('pkl_supervisors', function (Blueprint $table) {
            $table->id();
            $table->string('supervisor_name');
            $table->string('company_name');
            $table->text('company_address');
            $table->string('contact_phone', 30)->nullable();
            $table->timestamps();
        });
    }
};
