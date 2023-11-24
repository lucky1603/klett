<?php

namespace App\Imports;

use App\Models\UserImport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CustomUserImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $collection->each(function($row) {
            $teacher = false;
            if($row[5] == 'teacher') {
                $teacher = true;
            }

            $source = $row[6];

            UserImport::create([
                'username' => $row[1],
                'ime' => $row[2],
                'prezime' => $row[3],
                'email' => $row[4],
                'is_teacher' => $teacher,
                'source' => $source,
            ]);
        });
    }
}
