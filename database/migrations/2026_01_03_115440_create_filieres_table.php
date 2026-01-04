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
        Schema::create('filieres', function (Blueprint $table) {
            $table->id();
            // Une filière appartient à un département (ex: Génie Info dépend du Dpt Informatique)
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Nom (ex: Génie Logiciel)
            $table->string('code')->nullable(); // Code (ex: GL)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filieres');
    }
};
