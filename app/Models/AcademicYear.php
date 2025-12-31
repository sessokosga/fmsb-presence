<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $guarded = [];

    public function students() { return $this->hasMany(Student::class); }

    // Optionnel : Désactiver les autres années si on en définit une comme actuelle
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->is_current) {
                static::where('id', '!=', $model->id)->update(['is_current' => false]);
            }
        });
    }
}
