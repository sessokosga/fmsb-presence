<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $guarded = [];
    public function course() { return $this->belongsTo(Course::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
// Relation vers les présences individuelles (à venir)
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    // Ajoutez cette méthode dans App\Models\AttendanceSession
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }



// Les moniteurs (Nouveau)
    public function monitors()
    {
        // 'attendance_session_monitor' est le nom de la table pivot créée ci-dessus
        return $this->belongsToMany(Teacher::class, 'attendance_session_monitor', 'attendance_session_id', 'teacher_id');
    }
}
