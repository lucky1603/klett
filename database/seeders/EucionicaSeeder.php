<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\EUcionicaImport;
use Maatwebsite\Excel\Facades\Excel;

class EucionicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Excel::import(new EUcionicaImport, 'finaexport-short.xlsx', 'public');
    }
}
