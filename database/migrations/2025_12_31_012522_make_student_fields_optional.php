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
            $table->date('birth_date')->nullable()->change();
            $table->string('parent_name')->nullable()->change();
            $table->string('parent_phone')->nullable()->change();
            // Ajoutez ici tout autre champ qui pourrait bloquer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
