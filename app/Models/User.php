<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // OPTION 1 : Autoriser tout le monde (Dangereux, mais utile pour tester 2 minutes)
        // return true;

        // OPTION 2 : Autoriser seulement votre email admin (Recommandé)
        $admins = [
            'micheekosga@gmail.com',
            'admin@test.com',
        ];

        return in_array($this->email, $admins);

        // OPTION 3 : Autoriser tous les emails qui finissent par votre domaine (Pro)
        // return str_ends_with($this->email, '@univ-fmsb.cm');
    }
}
