<?php

namespace App\Exports;

use App\Models\AppUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AppUserExport implements FromCollection, WithStyles, WithHeadings, WithColumnWidths
{
    private $appUsers;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if($this->appUsers == null) {
            $this->appUsers = $this->getAppUsers();
        }

        return $this->appUsers->map(function($appUser) {
            return [
                'ime' => $appUser->ime,
                'prezime' => $appUser->prezime,
                'email' => $appUser->email,
                'lozinka' => $appUser->password,
                'adresa' => $appUser->adresa,
                'pb' => $appUser->pb,
                'mesto' => $appUser->mesto,
                'drzava' => $appUser->country->name,
                'telefon(1)' => $appUser->tel1,
                'telefon(2)' => $appUser->tel2,
                'nastavnik' => $appUser->is_teacher == 1 ? 'DA' : 'NE',
                'enabled' => $appUser->enabled ? "DA" : "NE"
            ];
        });
    }

    public function headings() : array {
        return [
            "Ime",
            "Prezime",
            "Email",
            "Lozinka",
            "Adresa",
            "Postanski broj",
            "Mesto",
            "Država",
            "Telefon #1",
            "Telefon #2",
            "Jel nastavnik",
            "Aktivan"
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 35,
            'D' => 65,
            'E' => 35,
            'F' => 10,
            'G' => 25,
            'H' => 20,
            'I' => 25,
            'J' => 25,
            'K' => 10
        ];
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

    private function getAppUsers() {
        return AppUser::all()->load('country');
    }
}
