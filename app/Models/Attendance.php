<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // 👇 C'est cette liste qui manquait pour autoriser l'enregistrement
    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'is_present',
        'status',      // Si vous l'avez dans votre migration
        'observation', // Si vous l'avez dans votre migration
    ];

    // Les relations (gardez-les si elles y sont déjà)
    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
