<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScheduledChangesExport implements FromCollection, WithTitle, WithStyles, WithHeadings, WithColumnWidths, WithEvents
{
    private $name;
    private $collection;
    public function __construct($name, $collection)
    {
        $this->name = $name;
        $this->collection = $collection;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() 
    {
        $returnCollection = ([]);
        return $this->collection->map(function($row) {
            return [
                $row->id,
                $row->user_id,
                $row->username,
                $row->email,
                $row->firstName,
                $row->lastName,
                $row->source,
                $row->role,
                $row->klf_korisnik == 1 ? true : false,
                $row->pedagoska_sveska == 1 ? true : false,
                $row->testomat == 1 ? true : false,
                date('d.m.Y', strtotime($row->created_at))
            ];
        });

        
    }

    public function title() : string
    {
        return $this->name;
    }


    public function styles(Worksheet $sheet) {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function headings() : array {
        return [
            "ID",
            "Korisnik ID",
            "Korisničko ime",
            "E-Mail",
            "Ime",
            "Prezime",
            "Izvor",
            "Rola",
            "KLF član",
            "P. Sveska",
            "Testomat",
            "Kreiran",
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 40, /* User ID */
            'C' => 15, /* username */
            'D' => 30, /* email */
            'E' => 15, /* firstName */
            'F' => 15, /* lastName */
            'G' => 10, /* source */
            'H' => 10, /* role */
            'I' => 10,   /* KLF */
            'J' => 10, /* P.Sveska */
            'K' => 10, /* testomat */
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}
