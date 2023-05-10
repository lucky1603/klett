<?php

namespace App\Imports;

use App\Models\ProfessionalStatus;
use Maatwebsite\Excel\Concerns\ToModel;

class ProfessionalStatusImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProfessionalStatus([
            'id' => $row[0],
            'name' => $row[1]
        ]);
    }
}
