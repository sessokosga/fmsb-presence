<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $guarded = [];
    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'department_id', // <-- Ne pas oublier
        'filiere_id',    // <-- Ne pas oublier
        'level_id',
    ];

    /**
     * Un étudiant appartient à un département.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Un étudiant appartient à un niveau (L1, L2, etc.).
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    // Sa filière principale
    public function filiere() {
        return $this->belongsTo(Filiere::class);
    }

// Ses cours de rattrapage
    public function coursRattrapage() {
        return $this->belongsToMany(Course::class, 'course_student');
    }
}
