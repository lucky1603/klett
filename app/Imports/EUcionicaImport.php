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

        // var_dump($row);
        if($row[0] == null && $row[1] == null)
            return null;

        $source = $row[6];


        $email = $row[4];
        if(!str_contains($row[4], '@')) {
            $email = $row[7];
        }

        return UserImport::create([
            'username' => $row[1],
            'ime' => $row[2],
            'prezime' => $row[3],
            'email' => $email,
            'source' => $source,
            'rola' => $row[5],
            'password' => isset($row[8]) ? $row[8] : null
        ]);
    }
}
