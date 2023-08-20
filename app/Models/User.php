<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    /**
     * Get assigned roles.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }


    /**
     * Assign user role.
     * @param $role
     */
    public function assignRole($role) {
        if(is_string($role)) {
            $role = Role::whereName($role)->firstOrFail();
        }

        $this->roles()->sync($role, false);
    }

    /**
     * Get all abilities assigned by the attached roles.
     */
    public function abilities() {
        return $this->roles->map->abilities->flatten()->pluck('name');
    }

    /**
     * Checks whether the current user is administrator or not.
     * @return bool
     */
    public function isAdmin() {
        return $this->roles()->whereName('superadmin')->count() != 0;
    }

    /**
     * Checks whether the current user has the particular role.
     * @param $role
     * @return bool
     */
    public function isRole($role) {
        return $this->roles()->whereName($role)->count() != 0;
    }
}
