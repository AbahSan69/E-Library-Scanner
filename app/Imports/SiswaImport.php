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
            // 'tanggal_lahir' => is_numeric($row['tanggal_lahir'])
            //     ? Carbon::instance(Date::excelToDateTimeObject($row['tanggal_lahir']))->format('Y-m-d')
            //     : Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'),
            'tanggal_lahir' => $row['tanggal_lahir'],
            'alamat'        => $row['alamat'],
            'no_hp'         => $row['no_hp'],
            'status'        => $row['status'] ?? 'Aktif',
        ]);
    }

    public function rules(): array
    {
        return [
            // NISN: Wajib ada (required), harus unik, dan berupa angka
            '*.nisn'          => 'required|unique:siswa,nisn|numeric', 
            
            // Nama, Kelas, Jenis Kelamin: Wajib ada
            '*.nama'          => 'required|string|max:150',
            '*.kelas'         => 'required|string|max:50',
            '*.jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan', // Contoh validasi nilai spesifik
            
            // Tanggal Lahir: Opsional (nullable)
            '*.tanggal_lahir' => 'nullable|date', 
            
            // Alamat: Opsional (nullable)
            '*.alamat'        => 'nullable|string',
            
            // Nomor HP: Opsional (nullable)
            '*.no_hp'         => 'nullable|string|max:15',
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
