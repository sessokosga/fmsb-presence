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
            // Pour "SEMAINE 1"
            $table->integer('week_number')->nullable()->after('session_date');

            // Pour "SEMESTRE 1" (On utilise un string 'S1', 'S2' ou un entier)
            $table->string('semester')->default('S1')->after('week_number');

            // Pour "Et Moniteurs" (Champ texte libre pour noter des noms supplémentaires)
            $table->string('assistants')->nullable()->after('teacher_id');
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
