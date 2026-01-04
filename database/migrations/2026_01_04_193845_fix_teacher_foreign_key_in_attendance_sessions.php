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
            // 1. On supprime la mauvaise contrainte (celle qui lie à 'users')
            // Le nom exact est dans votre message d'erreur
            $table->dropForeign('attendance_sessions_teacher_id_foreign');

            // 2. On ajoute la bonne contrainte (qui lie à 'teachers')
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->nullOnDelete(); // Ou cascadeOnDelete() selon votre préférence
        });
    }

    public function down(): void
    {
        // Pour revenir en arrière si besoin
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->foreign('teacher_id')->references('id')->on('users');
        });
    }


};
