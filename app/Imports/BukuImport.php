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
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Throwable;

class BukuImport implements ToModel, WithHeadingRow, WithValidation,  WithCalculatedFormulas, SkipsOnFailure
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
            'stok'        => $row['stok'] ?? 1,
            'status'      => $row['status'] ?? 'Tersedia',
        ]);
    }

    public function rules(): array
    {
        return [
            '*.isbn'  => 'nullable|distinct|unique:buku,isbn',
            '*.judul' => 'required|string|distinct|unique:buku,judul',
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
