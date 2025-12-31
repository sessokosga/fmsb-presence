<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // On ajoute les colonnes et on crée les liens (clés étrangères)
            if (!Schema::hasColumn('courses', 'level_id')) {
                $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            }

            if (!Schema::hasColumn('courses', 'semester_id')) {
                $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropForeign(['semester_id']);
            $table->dropColumn(['level_id', 'semester_id']);
        });
    }
};
