<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('classes', 'major_id')) {
            return;
        }

        Schema::table('classes', function (Blueprint $table) {
            $table->string('major')->nullable()->after('name');
        });

        DB::statement('UPDATE classes SET major = (SELECT name FROM majors WHERE majors.id = classes.major_id)');

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->dropColumn('major_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('classes', 'major')) {
            return;
        }

        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('major_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::statement('UPDATE classes SET major_id = (SELECT id FROM majors WHERE majors.name = classes.major)');

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('major');
        });
    }
};
