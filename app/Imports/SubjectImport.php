<?php

namespace App\Imports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToModel;

class SubjectImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Subject([
            'id' => $row[0],
            'name' => $row[1]
        ]);
    }
}
