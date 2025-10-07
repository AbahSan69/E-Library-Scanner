<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Throwable;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    public function model(array $row)
    {
        return new Siswa([
            'nisn'          => $row['nisn'],
            'nama'          => $row['nama'],
            'kelas'         => $row['kelas'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'tanggal_lahir' => is_numeric($row['tanggal_lahir'])
                ? Carbon::instance(Date::excelToDateTimeObject($row['tanggal_lahir']))->format('Y-m-d')
                : Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'),
            'alamat'        => $row['alamat'],
            'no_hp'         => $row['no_hp'],
            'status'        => $row['status'] ?? 'Aktif',
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nisn'            => 'unique:siswa,nisn',
            '*.nama'            => 'required|string',
            '*.kelas'           => 'required|string',
            '*.jenis_kelamin'   => 'required|string',
            '*.tanggal_lahir'   => 'required',
            '*.alamat'          => 'required|string',
            '*.no_hp'          => 'required',
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
