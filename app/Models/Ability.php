<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    /**
     * We are free to add anything alone.
     * @var array
     */
    protected $guarded = [];

    /**
     * Roles
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public static function initValues() {
        // Administrator
        if(Ability::whereName('add_platform_user')->first() == null) {
            Ability::create(['name' => 'add_platform_user', 'label' => 'Dodavanje korisnika platforme']);
        }

        if(Ability::whereName('change_platform_user_data')->first() == null) {
            Ability::create(['name' => 'change_platform_user_data', 'label' => 'Promena podataka korisnika platforme']);
        }

        if(Ability::whereName('delete_platform_user')->first() == null) {
            Ability::create(['name' => 'delete_platform_user', 'label' => 'Brisanje korisnika platforme']);
        }

        // Super administrators
        if(Ability::whereName('manage_app_users')->first() == null) {
            Ability::create(['name' => 'manage_app_users', 'label' => 'Upravljanje korisnicima aplikacije']);
        }

    }
}
