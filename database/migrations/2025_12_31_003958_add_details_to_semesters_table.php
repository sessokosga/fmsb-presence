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
        Schema::table('semesters', function (Blueprint $table) {
            // On vérifie si la colonne n'existe pas déjà avant de l'ajouter
            if (!Schema::hasColumn('semesters', 'code')) {
                $table->string('code', 10)->after('name')->nullable();
            }

            if (!Schema::hasColumn('semesters', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active']);
        });
    }


};
