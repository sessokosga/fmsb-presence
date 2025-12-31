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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // Titre (Dr, Pr, M., Mme)
            $table->string('title')->default('Dr');

            // Nom complet (ex: ARABO)
            $table->string('name');

            // Spécialité (ex: Cardiologue, optionnel)
            $table->string('specialty')->nullable();

            // Coordonnées (Utile pour les notifications)
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
