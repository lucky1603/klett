<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchoolExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return School::all()->load('municipality', 'institution_type')->map(function($school) {
            return [
                'municipality' => $school->municipality->name,
                'institution_type' => $school->institution_type->name,
                'name' => $school->name
            ];
        });
    }

    public function headings(): array
    {
        return ["Opština", "Tip ustanove", "Ime škole"];
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
            'A' => 30,
            'B' => 40,
            'C' => 50
        ];
    }



}
