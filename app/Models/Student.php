<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $guarded = [];


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
}
