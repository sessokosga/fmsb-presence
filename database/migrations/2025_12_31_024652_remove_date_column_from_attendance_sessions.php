<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // On vérifie si la colonne existe avant de tenter de la supprimer
            if (Schema::hasColumn('attendance_sessions', 'date')) {
                $table->dropColumn('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // Au cas où vous auriez besoin de revenir en arrière
            $table->date('date')->nullable();
        });
    }
};
