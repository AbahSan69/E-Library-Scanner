<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaFormatExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Baris contoh agar pengguna tahu formatnya
        return collect([
            [
                '1234567890',
                'Budi Santoso',
                '10A',
                'L',
                '2008-05-20',
                'Jl. Merdeka No. 1',
                '081234567890',
                'Aktif',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'nisn',
            'nama',
            'kelas',
            'jenis_kelamin',
            'tanggal_lahir',
            'alamat',
            'no_hp',
            'status',
        ];
    }
}
