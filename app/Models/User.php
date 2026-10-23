<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public function adminlte_image()
    {
        return 'https://picsum.photos/300/300';
    }
    public function adminlte_desc()
    {
        return $this->isAdministrator() ? 'Administrador' : 'Usuario';
    }
    public function adminlte_profile_url()
    {
        return route('profile.edit', [], false);
    }
    protected $fillable = [
        'name',
        'email',
        'password',
        'city',
        'ci',
        'email_verified_at',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function isAdministrator(): bool
    {
        return (bool) $this->is_admin;
    }

    public function deactivateUnlessLastAdministrator(): bool
    {
        return $this->getConnection()->transaction(function (): bool {
            // Serialize administrator removals in a consistent order, using current locked rows.
            $administrators = $this->newQuery()->where('is_admin', true)
                ->orderBy('id')->lockForUpdate()->get(['id']);

            if ($administrators->count() === 1 && $administrators->first()->is($this)) {
                return false;
            }

            return (bool) $this->delete();
        });
    }
}
