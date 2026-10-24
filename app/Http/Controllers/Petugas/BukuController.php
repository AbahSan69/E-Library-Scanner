<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BukuImport;
use Intervention\Image\ImageManagerStatic as Image;
use Milon\Barcode\DNS1D;
use App\Exports\BukuFormatExport;
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
        
        $total_buku = $query->count();
        $buku = $query->orderBy('id', 'desc')->paginate(25);

        // agar parameter pencarian ikut di pagination
        $buku->appends($request->only('q'));

        return view('petugas.buku.buku', compact('buku', 'total_buku'));
    }


    public function simpan_buku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isbn'        => 'nullable|string|unique:buku,isbn',
            'judul'       => 'required|string',
            'pengarang'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 🔎 Cek kombinasi judul + pengarang unik
        $exists = Buku::where('judul', $request->judul)
            ->where('pengarang', $request->pengarang)
            ->exists();

        if ($exists) {
            return back()
                ->with('toast_error', "Buku '{$request->judul}' oleh '{$request->pengarang}' sudah ada.")
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Simpan buku baru dengan placeholder barcode
            $book = Buku::create([
                'isbn'         => $request->isbn ?? null,
                'judul'        => $request->judul,
                'pengarang'    => $request->pengarang,
                'penerbit'     => $request->penerbit,
                'tahun_terbit' => $request->tahun_terbit,
                'lokasi_rak'   => $request->lokasi_rak,
                'status'       => $request->status,
                'stok'         => $request->stok,
                'no_barcode'   => 'temp',
            ]);

            // Generate barcode EAN13
            $book->no_barcode = generateEAN13($book->id);
            $book->save();

            DB::commit();

            return redirect()->route('petugas.buku')
                ->with('toast_success', 'Data berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function edit_buku(Request $request)
    {
        $id_buku = $request->id;

        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'isbn' => 'nullable|string',
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
                'stok' =>$request->stok
            ];

            $buku->update($data);

            DB::commit();

            return redirect()->route('petugas.buku')
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
        return redirect()->route('petugas.buku')
                         ->with('toast_success', 'Data Berhasil Dihapus!');
    }

    // Print langsung
    public function cetak($id)
    {
        $book = Buku::findOrFail($id);
        return view('petugas.buku.cetak', compact('book'));
    }

    // Download PDF
    public function cetakPdf($id)
    {
        $book = Buku::findOrFail($id);
        $pdf = Pdf::loadView('petugas.buku.cetak_pdf', compact('book'))
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

            return redirect()->route('petugas.buku')
                ->with('toast_success', 'Import buku berhasil!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->route('petugas.buku')
                ->with('toast_error', 'Import gagal pada baris: ' .
                    $failures[0]->row() . ' - ' . $failures[0]->errors()[0]);
        } catch (Exception $e) {
            return redirect()->route('petugas.buku')
                ->with('toast_error', 'Kesalahan: ' . $e->getMessage());
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

    public function downloadBulkBarcode(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada buku dipilih');
        }

        $books = Buku::whereIn('id', $ids)->get();

        $pdf = PDF::loadView('petugas.buku.bulk_barcode', compact('books'))
            ->setPaper('a4', 'portrait'); // tetap A4 agar muat 3 label

        return $pdf->download('barcode_buku.pdf');
    }

    public function downloadBarcodeSatuan($id)
    {
        if (empty($id)) {
            return back()->with('error', 'Tidak ada buku dipilih');
        }

        $book = Buku::findOrFail($id);

        // Ukuran kertas custom 14x7 cm (dalam points)
        $customPaper = [0, 0, 14 * 28.35, 7 * 28.35];

        $pdf = PDF::loadView('petugas.buku.barcode', compact('book'))
            ->setPaper($customPaper, 'portrait');

        return $pdf->download('barcode_buku_' . $book->no_barcode . '.pdf');
    }



    public function download_format()
    {
        return Excel::download(new BukuFormatExport, 'format_import_buku.xlsx');
    }

}
