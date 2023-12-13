<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Ability;
use App\Imports\SubjectImport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MunicipalitiesImport;
use App\Imports\ProfessionalStatusImport;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        DB::table("role_user")->delete();
        DB::statement("ALTER TABLE role_user AUTO_INCREMENT = 0");
        DB::table("ability_role")->delete();
        DB::statement("ALTER TABLE ability_role AUTO_INCREMENT = 0");
        DB::table("users")->delete();
        DB::statement("ALTER TABLE users AUTO_INCREMENT = 0");
        DB::table("roles")->delete();
        DB::statement("ALTER TABLE roles AUTO_INCREMENT = 0");
        DB::table("abilities")->delete();
        DB::statement("ALTER TABLE abilities AUTO_INCREMENT = 0");



        // Abilities.
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

        if(Ability::whereName('analyze_dashboard')->first() == null) {  
            Ability::create(['name'=> 'analyze_dashboard', 'label'=> 'Pregledanje podataka kontrolne table']);
        }


        // Roles.
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

        $user = User::create([
            'name' => 'Super Administrator',
            'email' => 'sinisa.ristic@gmail.com',
            'password' => Hash::make('BiloKoji12@')
        ]);

        $user->assignRole('appadmin');


        // \App\Models\User::factory(10)->create();

    }
}
