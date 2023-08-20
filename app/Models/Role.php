<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];

    public function abilities() {
        return $this->belongsToMany(Ability::class)->withTimestamps();
    }

    public function allowTo($ability) {
        if(is_string($ability)) {
            $ability = Ability::whereName($ability)->firstOrFail();
        }

        $this->abilities()->sync($ability, false);
    }

    public static function initValues() {
        if(Role::whereName('appadmin')->first() == null) {
            $role = Role::create(['name' => 'appadmin', 'Label' => 'Administrator aplikacije']);
            $abilities = Ability::all();
            foreach($abilities as $ability) {
                $role->allowTo($ability);
            }
        }

        if(Role::whereName('platformadmin')->first() == null) {
            $role = Role::create(['name' => 'platformadmin', 'label' => 'Administrator platforme']);
            $availableAbilities = collect([
                Ability::whereName('add_platform_user')->firstOrFail(),
                Ability::whereName('change_platform_user_data')->firstOrFail(),
                Ability::whereName('delete_platform_user')->firstOrFail(),

            ]);

            foreach($availableAbilities as $ability) {
                $role->allowTo($ability);
            }
        }

    }

    public function users() {
        return $this->belongsToMany(User::class);
    }
}
