<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SelectedUsersExport implements FromCollection,WithHeadings,WithStyles,WithColumnWidths
{
    public $selected;

    public function __construct(Collection $selected) {
        $this->selected = $selected;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $output = collect([]);

        foreach($this->selected as $user) {
            $output->add([
                "id"=> $user['id'],
                "username"=> $user['username'],
                "firstName" => $user['firstName'],
                "lastName"=> $user['lastName'],
                "email"=> $user['email'],
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
