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
        Schema::table('academic_years', function (Blueprint $table) {
            // On vérifie si les colonnes n'existent pas déjà avant de les ajouter
            if (!Schema::hasColumn('academic_years', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('academic_years', 'start_date')) {
                $table->date('start_date')->after('name');
            }
            if (!Schema::hasColumn('academic_years', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('academic_years', 'is_current')) {
                $table->boolean('is_current')->default(false)->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
