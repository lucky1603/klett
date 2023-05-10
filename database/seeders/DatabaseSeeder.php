<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MunicipalitiesImport;
use App\Imports\ProfessionalStatusImport;
use App\Imports\SubjectImport;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $countries = [
            'SRB' => 'Srbija',
            'MNE' => 'Crna Gora',
            'BIH' => 'Bosna i Hercegovina',
            'CRO' => 'Hrvatska',
            'SLO' => 'Slovenija'
        ];

        foreach($countries as $key=>$value) {
            DB::table('countries')->insert([
                'code' => $key,
                'name' => $value
            ]);
        }

        $instTypes = [
            'Gimnazija',
            'Muzička škola',
            "Osnovna i srednja škola",
            "Osnovna škola",
            "Predškolska ustanova",
            "Srednja stručna škola"
        ];

        foreach($instTypes as $instType) {
            DB::table('institution_types')->insert([
                'name' => $instType
            ]);
        }

        // Import municipalities from the excel file.
        Excel::import(new MunicipalitiesImport, 'opstine-lat.xlsx', 'public');

        // Import subjects from excel file.
        Excel::import(new SubjectImport, 'predmeti.xlsx', 'public');

        // Import Professional statuses from excel file.
        Excel::import(new ProfessionalStatusImport, 'prof_statusi.xlsx', 'public');

    }
}
