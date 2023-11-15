<?php

namespace App\Exports;

use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RemoteUsersExport implements FromCollection,WithHeadings,WithStyles,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $response = Http::asForm()->post(env('KEYCLOAK_TOKEN_URL'), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => env('KEYCLOAK_AUTH_PASSWORD'),
            "grant_type" => "password"
        ]);

        $accessToken = $response->json("access_token");
        $response = Http::withToken($accessToken)->get(env('KEYCLOAK_API_USERS_URL'));
        $rows = json_decode($response->body());


        $output = collect([]);
        foreach($rows as $row) {
            $output->add([
                'id' => $row->id,
                'username' => $row->username,
                'firstname' => $row->firstName ?? '',
                'lastname' => $row->lastName ?? '',
                'email' => $row->email,
            ]);
        }

        return $output;

    }

    public function headings(): array
    {
        return ["ID", "Korisničko ime", "Ime", "Prezime", "E-Mail"];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 30,
            'C' => 60,
            'D' => 60,
            'E' => 50
        ];
    }

}
