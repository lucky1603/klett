<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\ToCollection;

class WordPressFreskaImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $response = Http::asForm()->post(env("KEYCLOAK_TOKEN_URL"), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => "BiloKoji12@",
            "grant_type" => "password"
        ]);

        $token = $response->json('access_token');

        /**
         * Gettting users from the excel file
         *
         * Column Mapping:
         *
         * Col(12)  - User roles
         * Col(11)  - User status
         * Col(9)   - First name
         * Col(10)  - LastName
         * Col(4)   - Username
         * Col(5)   - Email
         */

        for($i = 1; $i < 10; $i++) {
            $row = $rows[$i];

            if($row[12] == "administrator")
                continue;

            $role = "Unset";
            if(isset($row[12]) && $row[12] == "teacher") {
                $role = "Teacher";
            } else {
                $role = "Subscriber";
            }


            // echo "Username ".$row[4].", role=".$row[12].", ".$row[9]." ".$row[10]." - email: ".$row[5]."\n";
            echo "Username ".$row[4].", role=".$role.", ".$row[9]." ".$row[10]." - email: ".$row[5]."\n";
            Http::withToken($token)
                ->asJson()
                ->post(env("KEYCLOAK_API_URL"), [
                    "username" => $row[4],
                    "firstName" => $row[9],
                    "lastName" => $row[10],
                    "email" => $row[5],
                    "realmRoles" => [ $role ],
                    "enabled" => true,
                    "groups" => [$role],
                ]);
        }
    }
}
