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
        Schema::create('attendance_session_monitor', function (Blueprint $table) {
            $table->id();
            // Le lien vers la session
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            // Le lien vers l'enseignant (qui joue le rôle de moniteur ici)
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_session_monitor');
    }
};
