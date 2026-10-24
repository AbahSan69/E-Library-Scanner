<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use Illuminate\Http\Request;
use App\Exports\SiswaFormatExport;
use Exception;
use Milon\Barcode\DNS1D;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipStream\ZipStream;
use ZipStream\Option\Archive;
use ZipArchive;
use Illuminate\Support\Facades\Cache;

class SiswaController extends Controller
{
    public function index(Request $request)
{
    $query = Siswa::query();

    // filter pencarian
    if ($request->filled('q')) {
        $query->where(function ($q) use ($request) {
            $q->where('nama', 'like', '%' . $request->q . '%')
              ->orWhere('nisn', 'like', '%' . $request->q . '%')
              ->orWhere('kelas', 'like', '%' . $request->q . '%');
        });
    }

    // filter kelas
    if ($request->filled('kelas')) {
        $query->where('kelas', $request->kelas);
    }

    // daftar kelas untuk dropdown
    $kelasList = Siswa::select('kelas')
        ->distinct()
        ->orderBy('kelas')
        ->pluck('kelas');

    $total_siswa = $query->count();

    // 👉 JIKA filter kelas → TIDAK paginate
    if ($request->filled('kelas')) {
        $siswa = $query->orderBy('id', 'desc')->get();
    } 
    // 👉 JIKA TIDAK filter kelas → paginate
    else {
        $siswa = $query->orderBy('id', 'desc')->paginate(25);
        $siswa->appends($request->only('q'));
    }

    // Tambah QR code (base64)
    // foreach ($siswa as $s) {
    //     $s->qrcode = QrCode::size(80)->generate($s->nisn);
    // }

    return view('admin.siswa.siswa', compact('siswa', 'total_siswa', 'kelasList'));
}



    public function simpan_siswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn'      => 'required|string|unique:siswa,nisn',
            'nama'    => 'required',
            'kelas' => 'required',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            Siswa::create([
                'nisn'     => $request->nisn,
                'nama'    => $request->nama,
                'kelas' => $request->kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'status'   => 'Aktif',
            ]);

            DB::commit();

            return redirect()->route('admin.siswa')
                         ->with('toast_success', 'Data berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function import_excel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));

            // 🔥 Redirect setelah sukses
            return redirect()->route('admin.siswa')
                ->with('toast_success', 'Import siswa berhasil!');
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->route('admin.siswa')
                ->with('toast_error', 'Import gagal pada baris: ' . $failures[0]->row() . ' - ' . $failures[0]->errors()[0]);
        }
    }

    public function edit_siswa(Request $request)
    {
        $id_user = $request->id;

        $validator = Validator::make($request->all(), [
            'nisn'     => 'required',
            'nama'    => 'required',
            'kelas' => 'required',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'status'   => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $siswa = Siswa::findOrFail($id_user);

            $data = [
                'nisn'     => $request->nisn,
                'nama'    => $request->nama,
                'kelas' => $request->kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'status'   => $request->status,
            ];

            $siswa->update($data);

            DB::commit();

            return redirect()->route('admin.siswa')
                         ->with('toast_success', 'Data berhasil diperbarui!')
                         ->with('activeTab', 'akun');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                         ->with('activeTab', 'akun');
        }
    }

    public function hapus_siswa($id)
    {
        return redirect()->route('admin.keanggotaan.index')
        ->with('error', 'Data siswa tidak boleh dihapus. Ubah status keanggotaannya (lulus, mutasi, atau nonaktif).');
    }

    public function download_format()
    {
        return Excel::download(new SiswaFormatExport, 'format_import_siswa.xlsx');
    }

    //QR
    public function qrSiswa($id)
    {
        $siswa = Siswa::findOrFail($id);

        $qr = QrCode::format('svg')
            ->size(300)
            ->generate($siswa->nisn);

        return response($qr)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="'.$siswa->nisn.'.svg"');
    }


    public function qrBulk(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || count($ids) == 0) {
            return back()->with('error', 'Tidak ada data dipilih');
        }

        $zip = new \ZipArchive;
        $zipName = 'qr-siswa-' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

            foreach ($ids as $id) {
                $siswa = Siswa::find($id);
                if (!$siswa) continue;

                // GUNAKAN SVG AGAR TIDAK PERLU IMAGICK
                $qr = QrCode::format('svg')
                    ->size(300)
                    ->generate($siswa->nisn);

                $zip->addFromString($siswa->nisn . '.svg', $qr);
            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    //barcode siswa
    public function barcodeSiswa($id)
    {
        $siswa = Siswa::findOrFail($id);

        $width = 6 * 28.35;   // 6 cm
        $height = 3 * 28.35;  // 3 cm

        $customPaper = [0, 0, $width, $height];

        $pdf = PDF::loadView('admin.siswa.barcode', compact('siswa'))
            ->setPaper($customPaper, 'landscape');

            return $pdf->download($siswa->nisn.' - '.$siswa->nama.'.pdf');
    }

    // public function barcodeBulk(Request $request)
    // {
    //     $ids = $request->input('selected', []);
    
    //     if (empty($ids)) {
    //         return back()->with('error', 'Tidak ada siswa dipilih');
    //     }
    
    //     $siswas = Siswa::whereIn('id', $ids)->get();
    
    //     // File ZIP sementara di OS temp folder
    //     $tempZip = tempnam(sys_get_temp_dir(), 'zip');
    
    //     $zip = new \ZipArchive;
    //     if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
    //         return back()->with('error', 'Gagal membuka ZIP');
    //     }
    
    //     foreach ($siswas as $siswa) {
    
    //         $pdf = PDF::loadView('admin.siswa.barcode', compact('siswa'))
    //                   ->setPaper([0,0,160,90]);
    
    //         $pdfContent = $pdf->output();
    
    //         // Nama file aman (tanpa interpolasi)
    //         $fileName = $siswa->nisn . ' - ' . $siswa->nama . '.pdf';
    
    //         $zip->addFromString($fileName, $pdfContent);
    //     }
    
    //     $zip->close();
    
    //     $zipContent = file_get_contents($tempZip);
    //     unlink($tempZip);
    
    //     return response($zipContent, 200)
    //         ->header('Content-Type', 'application/zip')
    //         ->header('Content-Disposition', 'attachment; filename=barcode-siswa-' . time() . '.zip')
    //         ->header('Content-Length', strlen($zipContent));
    // }

    public function barcodeBulk(Request $request)
{
    $ids = $request->input('selected', []);

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada siswa dipilih');
    }

    $siswas = Siswa::whereIn('id', $ids)->get();

    // Folder kerja di storage
    $workDir = storage_path('app/barcode_tmp');
    if (!is_dir($workDir)) {
        mkdir($workDir, 0755, true);
    }

    $zipPath = $workDir . '/barcode-siswa-' . uniqid() . '.zip';

    $zip = new \ZipArchive;
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        return back()->with('error', 'Gagal membuat ZIP');
    }

    foreach ($siswas as $siswa) {

        $pdfPath = $workDir . '/' . $siswa->nisn . ' - ' . $siswa->nama . '.pdf';

        PDF::loadView('admin.siswa.barcode', compact('siswa'))
            ->setPaper([0, 0, 160, 90])
            ->save($pdfPath);

        if (file_exists($pdfPath)) {
            $zip->addFile($pdfPath, basename($pdfPath));
        }
    }

    $zip->close();

    // Hapus PDF setelah ZIP dibuat
    foreach (glob($workDir . '/*.pdf') as $file) {
        unlink($file);
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}


}
