<?php

namespace Database\Seeders;

use App\Models\AppUser;
use Illuminate\Database\Seeder;

class AppUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i < 200; $i++) {
            $user = AppUser::factory()->make();
            $user->save();
        }
    }
}
