<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {



        Schema::disableForeignKeyConstraints();
        // 1. Structure Académique
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "L1", "L2", "M1"
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Semestre 1"
            $table->string('code')->unique(); // e.g., "SM1"
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "2025-2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        // 2. Gestion des Cours (Time-table)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('credits');
            $table->timestamps();
        });

        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Un cours ne peut être offert qu'une seule fois pour une année et un semestre donnés
            $table->unique(['course_id', 'academic_year_id', 'semester_id'], 'course_offering_unique');
        });

        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "L2 SBM Grp A"
            $table->timestamps();
        });

        // 3. Étudiants & Parents
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            // Il est recommandé de caster ce champ vers un Enum PHP natif dans le modèle Student
            $table->string('gender'); // e.g., 'male', 'female'
            $table->string('avatar_url')->nullable();
            $table->date('birth_date');

            // Informations sur le parent/tuteur
            $table->string('parent_name')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_phone')->nullable();

            $table->timestamps();
        });

        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Un étudiant ne peut être inscrit qu'une seule fois par année académique
            $table->unique(['student_id', 'academic_year_id']);
        });

        // 4. Présence (Core Feature)
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            // Assurez-vous que votre table 'users' existe
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            // Le champ 'status' doit être backé par un Enum PHP natif dans le modèle Attendance.
            // Ex: enum AttendanceStatus: string { case PRESENT = 'present'; ... }
            $table->string('status'); // 'present', 'absent', 'late', 'excused'
            $table->text('justification')->nullable(); // Contient une note ou un chemin vers un fichier
            $table->timestamps();

            // Un étudiant ne peut avoir qu'un seul statut de présence par session
            $table->unique(['attendance_session_id', 'student_id']);
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("users");
        Schema::dropIfExists("password_reset_tokens");
        Schema::dropIfExists("sessions");
    }
};
