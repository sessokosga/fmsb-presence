<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{

    // 👇 Ajoutez cette liste pour autoriser l'enregistrement
    protected $fillable = [
        'title',
        'name',
        'specialty',
        'phone',
        'email',
    ];

    // Optionnel : Relation inverse (Un enseignant a plusieurs sessions)
    public function sessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }
}
