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
        Schema::table('attendances', function (Blueprint $table) {
            // La colonne pour le Switch (Vrai/Faux)
            if (!Schema::hasColumn('attendances', 'is_present')) {
                $table->boolean('is_present')->default(false)->after('student_id');
            }

            // La colonne pour le statut (Présent/Absent/Retard)
            if (!Schema::hasColumn('attendances', 'status')) {
                $table->string('status')->default('absent')->after('is_present');
            }

            // La colonne pour les notes
            if (!Schema::hasColumn('attendances', 'observation')) {
                $table->string('observation')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_present', 'status', 'observation']);
        });
    }


};
