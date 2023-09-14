<?php

namespace App\Imports;

use App\Models\UserImport;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;

class EUcionicaImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        $teacher = false;
        if($row[10] == 'teacher') {
            $teacher = true;
        }

        // var_dump($row);
        if($row[0] == NULL)
            return NULL;

        return UserImport::create([
            'username' => $row[2],
            'ime' => $row[8],
            'prezime' => $row[9],
            'email' => $row[3],
            'is_teacher' => $teacher
        ]);
    }
}
