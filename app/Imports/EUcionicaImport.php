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
        if($row[7] == 'teacher') {
            $teacher = true;
        }

        return UserImport::create([
            'username' => $row[1],
            'ime' => $row[2],
            'prezime' => $row[3],
            'email' => $row[4],
            'is_teacher' => $teacher
        ]);
    }
}
