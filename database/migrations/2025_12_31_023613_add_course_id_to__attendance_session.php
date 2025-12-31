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
            // Ajout de l'Année Académique si absente
            if (!Schema::hasColumn('attendance_sessions', 'academic_year_id')) {
                $table->foreignId('academic_year_id')->nullable()->constrained()->cascadeOnDelete();
            }

            // Ajout du Cours (UE) si absent
            if (!Schema::hasColumn('attendance_sessions', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            }

            // Ajout de la Date si absente
            if (!Schema::hasColumn('attendance_sessions', 'session_date')) {
                $table->date('session_date')->nullable();
            }

            // Ajout des Horaires si absents
            if (!Schema::hasColumn('attendance_sessions', 'start_time')) {
                $table->time('start_time')->nullable();
            }
            if (!Schema::hasColumn('attendance_sessions', 'end_time')) {
                $table->time('end_time')->nullable();
            }

            // Ajout du Lieu si absent
            if (!Schema::hasColumn('attendance_sessions', 'location')) {
                $table->string('location')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_attendance_session', function (Blueprint $table) {
            //
        });
    }
};
