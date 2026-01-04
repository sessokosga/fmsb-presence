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
        Schema::table('students', function (Blueprint $table) {
            // On le met en nullable au début pour ne pas casser les étudiants existants
            $table->foreignId('filiere_id')->nullable()->constrained()->after('department_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('filiere_id')->nullable()->constrained()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students_and_courses', function (Blueprint $table) {
            //
        });
    }
};
