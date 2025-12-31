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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // 1. Si on avait créé la colonne texte par erreur, on la supprime
            if (Schema::hasColumn('attendance_sessions', 'semester')) {
                $table->dropColumn('semester');
            }

            // 2. On ajoute la vraie relation vers votre table 'semesters'
            if (!Schema::hasColumn('attendance_sessions', 'semester_id')) {
                // nullable() est prudent si vous avez déjà des sessions créées
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            //
        });
    }
};
