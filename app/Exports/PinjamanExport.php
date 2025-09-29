<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PinjamanExport implements FromCollection, WithHeadings
{
    protected $data;

    // terima data lewat constructor
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($peminjaman) {
            return [
                'ID'              => $peminjaman->id,
                'Nama Peminjam'   => $peminjaman->siswa->nama ?? '-',
                'Judul Buku'      => $peminjaman->buku->judul ?? '-',
                'Tanggal Pinjam'  => $peminjaman->tanggal_pinjam,
                'Tanggal Kembali' => $peminjaman->tanggal_kembali,
                'Status'          => $peminjaman->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Peminjam',
            'Judul Buku',
            'Tanggal Pinjam',
            'Tanggal Kembali',
            'Status',
        ];
    }
}
