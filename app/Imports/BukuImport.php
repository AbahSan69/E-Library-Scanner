<?php

namespace App\Imports;

use App\Models\Buku;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class BukuImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function model(array $row)
    {
        return new Buku([
            'isbn'        => $row['isbn'] ?? null,
            'judul'       => $row['judul'],
            'pengarang'   => $row['pengarang'],
            'penerbit'    => $row['penerbit'],
            'tahun_terbit'=> $row['tahun_terbit'],
            'lokasi_rak'  => $row['lokasi_rak'],
            'status'      => $row['status'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.isbn'        => 'nullable|unique:buku,isbn',
            '*.judul'       => 'required|string',
            '*.pengarang'   => 'required|string',
            '*.penerbit'    => 'required|string',
            '*.tahun_terbit'=> 'required|digits:4',
            '*.lokasi_rak'  => 'required|string',
            '*.status'      => 'required|in:Tersedia,Dipinjam',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        // Kalau mau simpan error ke log atau tampilkan ke user
        // dd($failures);
    }

    public function onError(Throwable $e)
    {
        // Bisa tulis ke log
    }
}
