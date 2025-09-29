<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BukuImport;
use Intervention\Image\ImageManagerStatic as Image;
use Milon\Barcode\DNS1D;
use Exception;


class BukuController extends Controller
{
    public function index(Request $request)
    {
        $query = Buku::query();

        // jika ada pencarian
        if ($request->has('q') && !empty($request->q)) {
            $query->where('judul', 'like', '%' . $request->q . '%')
              ->orWhere('isbn', 'like', '%' . $request->q . '%')
              ->orWhere('pengarang', 'like', '%' . $request->q . '%')
              ->orWhere('penerbit', 'like', '%' . $request->q . '%');
        }

        $buku = $query->orderBy('id', 'desc')->paginate(10);
        $total_buku = $query->count();

        // agar parameter pencarian ikut di pagination
        $buku->appends($request->only('q'));

        return view('admin.buku.buku', compact('buku', 'total_buku'));
    }


    public function simpan_buku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isbn'        => 'nullable|string|unique:buku,isbn',
            'judul'       => 'required|string',
            'pengarang'   => 'required|string',
            'penerbit'    => 'required|string',
            'tahun_terbit'=> 'required|digits:4',
            'lokasi_rak'  => 'required|string',
            'status'      => 'required|in:Tersedia,Dipinjam',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Simpan buku baru dengan placeholder barcode
            $book = Buku::create([
                'isbn'        => $request->isbn ?? null,
                'judul'       => $request->judul,
                'pengarang'   => $request->pengarang,
                'penerbit'    => $request->penerbit,
                'tahun_terbit'=> $request->tahun_terbit,
                'lokasi_rak'  => $request->lokasi_rak,
                'status'      => $request->status,
                'no_barcode'  => 'temp',
            ]);

            // Generate barcode EAN13
            // $book->no_barcode = getEANBase($book->id);
            $book->no_barcode = generateEAN13($book->id);
            $book->save();

            DB::commit();

            return redirect()->route('admin.buku') // ✅ pakai route resource index
                         ->with('toast_success', 'Data berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('toast_error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }


    public function edit_buku(Request $request)
    {
        $id_buku = $request->id;

        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'isbn' => 'nullable|string',
            'pengarang' => 'nullable|string',
            'penerbit' => 'nullable|string',
            'tahun_terbit' => 'nullable|digits:4',
            'lokasi_rak' => 'nullable|string',
            'status' => 'required|in:Tersedia, Dipinjam',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $buku = Buku::findOrFail($id_buku);

            $data = [
                'isbn' => $request->isbn,
                'judul' => $request->judul,
                'pengarang' => $request->pengarang,
                'penerbit' => $request->penerbit,
                'tahun_terbit' => $request->tahun_terbit,
                'lokasi_rak' => $request->lokasi_rak,
                'status' => $request->status,
            ];

            $buku->update($data);

            DB::commit();

            return redirect()->route('admin.buku')
                         ->with('toast_success', 'Data buku berhasil diperbarui!')
                         ->with('activeTab', 'akun');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                         ->with('activeTab', 'akun');
        }
    }

    public function hapus_buku($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return redirect()->back()->with('toast_error', 'Data tidak ditemukan!');
        }

        $buku->delete();
        return redirect()->route('admin.buku')
                         ->with('toast_success', 'Data Berhasil Dihapus!');
    }

    // Print langsung
    public function cetak($id)
    {
        $book = Buku::findOrFail($id);
        return view('admin.buku.cetak', compact('book'));
    }

    // Download PDF
    public function cetakPdf($id)
    {
        $book = Buku::findOrFail($id);
        $pdf = Pdf::loadView('admin.buku.cetak_pdf', compact('book'))
                  ->setPaper('A7', 'portrait'); // kertas kecil label
        return $pdf->download('label-'.$book->no_barcode.'.pdf');
    }

    // public function downloadBarcode($id)
    // {
    //     $book = Buku::findOrFail($id);

    //     // Generate barcode base64
    //     $barcode = DNS1D::getBarcodePNG($book->no_barcode, 'EAN13', 3, 100);

    //     // Konversi base64 ke binary
    //     $imageData = base64_decode($barcode);

    //     // Nama file
    //     $fileName = 'barcode_' . $book->no_barcode . '.png';

    //     // Return response sebagai download gambar
    //     return response($imageData)
    //         ->header('Content-Type', 'image/png')
    //         ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    // }

    public function import_excel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new BukuImport, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->route('admin.buku')
                ->with('toast_error', 'Import gagal pada baris: ' . $failures[0]->row() . ' - ' . $failures[0]->errors()[0]);
        }
    }

    // public function generate($code)
    // {
    //     $barcode = DNS1D::getBarcodePNG($code, 'EAN13', 2, 50, [0,0,0], true);

    //     // Buat image dari base64 barcode
    //     $barcodeImage = Image::make(base64_decode($barcode));

    //     // Tambah canvas untuk teks
    //     $canvas = Image::canvas($barcodeImage->width(), $barcodeImage->height() + 30, '#ffffff');

    //     // Teks header
    //     $canvas->text('SMAN 1 CITEUREUP', $canvas->width() / 2, 20, function($font) {
    //         $font->file(public_path('fonts/arial.ttf')); // pastikan font ada
    //         $font->size(18);
    //         $font->color('#000000');
    //         $font->align('center');
    //         $font->valign('top');
    //     });

    //     // Tempelkan barcode di bawah teks
    //     $canvas->insert($barcodeImage, 'bottom');

    //     // Response PNG agar bisa langsung download
    //     return $canvas->response('png');
    // }


    public function downloadBarcode($no_barcode)
{
    $barcode = new DNS1D();
    $barcode->setStorPath(storage_path('barcodes/'));

    // Ambil barcode PNG dalam base64
    $barcodePng = $barcode->getBarcodePNG($no_barcode, 'EAN13', 2, 50, [0,0,0], true);
    $barcodeImg = Image::make(base64_decode($barcodePng));

    // Tentukan ruang tambahan
    $labelHeight = 30; // ruang untuk teks di atas
    $paddingTopBottom = 10;
    $paddingLeftRight = 30; // ruang kiri kanan

    $canvasWidth  = $barcodeImg->width() + ($paddingLeftRight * 2);
    $canvasHeight = $labelHeight + $barcodeImg->height() + $paddingTopBottom;

    // Buat canvas putih
    $canvas = Image::canvas($canvasWidth, $canvasHeight, '#ffffff');

    // Tambahkan teks di atas (center)
    $canvas->text('SMAN 1 CITEUREUP', $canvasWidth / 2, $labelHeight / 2, function ($font) {
        $font->file(public_path('fonts/ARIAL.ttf'));
        $font->size(18);
        $font->color('#000000');
        $font->align('center');
        $font->valign('center');
    });

    // Tempel barcode di bawah teks dengan padding kiri
    $canvas->insert($barcodeImg, 'top-left', $paddingLeftRight, $labelHeight);

    // Kembalikan sebagai file download
    return $canvas->response('png')->withHeaders([
        'Content-Disposition' => 'attachment; filename="barcode_'.$no_barcode.'.png"'
    ]);
}

}
