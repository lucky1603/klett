<?php

namespace Database\Seeders;

use App\Imports\WordPressFreskaImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class RemoteUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Import municipalities from the excel file.
        Excel::import(new WordPressFreskaImport, 'UserExportFreska.xlsx', 'public');
    }
}
