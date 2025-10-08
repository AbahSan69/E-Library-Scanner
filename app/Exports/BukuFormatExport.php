<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BukuFormatExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                '9786020321234',     // isbn
                'Pemrograman Web Dasar', // judul
                'Ahmad Fauzi',       // pengarang
                'Erlangga',          // penerbit
                '2020',              // tahun_terbit
                'RAK-01',            // lokasi_rak
                '10',                // stok
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'isbn',
            'judul',
            'pengarang',
            'penerbit',
            'tahun_terbit',
            'lokasi_rak',
            'stok',
        ];
    }
}
