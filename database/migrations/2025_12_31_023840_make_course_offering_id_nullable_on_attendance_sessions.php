<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // On rend la colonne optionnelle
            $table->unsignedBigInteger('course_offering_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // Au cas où vous voudriez revenir en arrière (attention aux données existantes)
            $table->unsignedBigInteger('course_offering_id')->nullable(false)->change();
        });
    }
};
