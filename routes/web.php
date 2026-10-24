<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\DendaController;
use App\Http\Controllers\PengaturanDendaController;
use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\KeanggotaanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalDashboardController;
//Petugas
use App\Http\Controllers\Petugas\BukuController as PetugasBukuController;
use App\Http\Controllers\Petugas\SiswaController as PetugasSiswaController;
use App\Http\Controllers\Petugas\PeminjamanController as PetugasPeminjamanController;
use App\Http\Controllers\Petugas\PengembalianController as PetugasPengembalianController;
use App\Http\Controllers\Petugas\DendaController as PetugasDendaController;
use App\Http\Controllers\Petugas\PengaturanDendaController as PetugasPengaturanDendaController;
use App\Http\Controllers\Petugas\LaporanBulananController as PetugasLaporanBulananController;
use App\Http\Controllers\Petugas\KeanggotaanController as PetugasKeanggotaanController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Models\Peminjaman;

Route::get('/', [AuthController::class, 'index'])->name('halaman_login');
Route::post('/', [AuthController::class, 'login'])->name('login');
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [GlobalDashboardController::class, 'dashboard'])->name('dashboard');
    Route::middleware(['role:1'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        //Buku
        Route::get('/buku', [BukuController::class, 'index'])->name('buku');
        Route::post('/simpan_buku', [BukuController::class, 'simpan_buku'])->name('simpan_buku');
        Route::post('/edit_buku', [BukuController::class, 'edit_buku'])->name('edit_buku');
        Route::get('/hapus_buku/{id}', [BukuController::class, 'hapus_buku'])->name('hapus_buku');
        Route::get('/cetak/{id}', [BukuController::class, 'cetak'])->name('cetak');
        Route::get('/cetakPdf/{id}', [BukuController::class, 'cetakPdf'])->name('cetakPdf');
        Route::post('/import', [BukuController::class, 'import_excel'])->name('import');
        Route::get('/downloadBarcode/{no_barcode}', [BukuController::class, 'downloadBarcode'])->name('downloadBarcode');
        Route::post('/admin/download-barcode', [BukuController::class, 'downloadBulkBarcode'])->name('downloadBulkBarcode');
        Route::get('downloadBarcodeSatuan/{id}', [BukuController::class, 'downloadBarcodeSatuan'])->name('downloadBarcodeSatuan');
        Route::get('/format_buku', [BukuController::class, 'download_format'])->name('format_buku');

        //Siswa
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
        Route::post('/simpan_siswa', [SiswaController::class, 'simpan_siswa'])->name('simpan_siswa');
        Route::post('/import_siswa', [SiswaController::class, 'import_excel'])->name('import_siswa');
        Route::post('/edit_siswa', [SiswaController::class, 'edit_siswa'])->name('edit_siswa');
        Route::get('/hapus_siswa/{id}', [SiswaController::class, 'hapus_siswa'])->name('hapus_siswa');
        Route::get('/download_format_siswa', [SiswaController::class, 'download_format'])->name('download_format_siswa');
        Route::get('/qr-siswa/{id}', [SiswaController::class, 'qrSiswa'])->name('qr');
        Route::post('/qr-siswa-bulk', [SiswaController::class, 'qrBulk'])->name('qr_bulk');
        //barocde
        Route::get('/barcode-siswa/{id}', [SiswaController::class, 'barcodeSiswa'])->name('barcode');
        Route::post('/barcode-siswa-bulk', [SiswaController::class, 'barcodeBulk'])->name('barcode_bulk');
        
        //Peminjaman
        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman');
        Route::post('/simpan_peminjaman', [PeminjamanController::class, 'simpan_peminjaman'])->name('simpan_peminjaman');
        Route::post('/edit_peminjaman', [PeminjamanController::class, 'edit_peminjaman'])->name('edit_peminjaman');
        Route::get('/hapus_peminjaman/{id}', [PeminjamanController::class, 'hapus_peminjaman'])->name('hapus_peminjaman');
        Route::get('/cariSiswa', [PeminjamanController::class, 'cariSiswa'])->name('cariSiswa');
        Route::post('/export', [PeminjamanController::class, 'export_pinjaman'])->name('export');
        Route::get('/get-buku/{kode}', [PeminjamanController::class, 'show'])->name('get-buku');
        Route::get('/get-siswa/{nisn}', [PeminjamanController::class, 'show_siswa'])->name('get-siswa'); 
        // Sesuaikan nama controller dan method
        Route::get('/tambah_peminjaman', [PeminjamanController::class, 'coba'])->name('coba');


        // web.php
        Route::get('/siswa/search', [PeminjamanController::class, 'search'])->name('siswa.search');

        //Pengembalian
        Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian');
        Route::get('/cariBarcode/{barcode}', [PengembalianController::class, 'cariBarcode'])->name('cariBarcode');
        Route::post('/simpan_pengembalian', [PengembalianController::class, 'simpan_pengembalian'])->name('simpan_pengembalian');
        Route::get('/get-buku-kembali/{kode}', [PengembalianController::class, 'show'])->name('get-buku-kembali');
        Route::get('/get-siswa-by-buku/{kodeBuku}', [PengembalianController::class, 'show_siswa_by_buku'])->name('get-siswa-by-buku');

        //Denda
        Route::get('/denda', [DendaController::class, 'index'])->name('denda.index');
        Route::post('/denda/{denda}/bayar', [DendaController::class, 'bayar'])->name('denda.bayar');
        Route::post('/denda/{denda}/dispensasi', [DendaController::class, 'dispensasi'])->name('denda.dispensasi');
        //Denda Pengaturan
        Route::get('/denda-pengaturan', [PengaturanDendaController::class, 'index'])->name('denda.pengaturan');
        Route::put('/denda-pengaturan', [PengaturanDendaController::class, 'update'])->name('denda.pengaturan.update');
        Route::post('/denda-pengaturan/libur', [PengaturanDendaController::class, 'tambahLibur'])->name('denda.libur.store');
        Route::delete('/denda-pengaturan/libur/{hariLibur}', [PengaturanDendaController::class, 'hapusLibur'])->name('denda.libur.destroy');

        //Laporan
        Route::get('laporan-bulanan', [LaporanBulananController::class, 'index'])->name('laporan.index');
        Route::get('laporan-bulanan/pdf', [LaporanBulananController::class, 'pdf'])->name('laporan.pdf');
        Route::get('laporan-bulanan/csv', [LaporanBulananController::class, 'csv'])->name('laporan.csv');

        //Laporan
        Route::get('keanggotaan', [KeanggotaanController::class, 'index'])->name('keanggotaan.index');
        Route::get('keanggotaan/kenaikan-kelas', [KeanggotaanController::class, 'kenaikan'])->name('keanggotaan.kenaikan');
        Route::post('keanggotaan/kenaikan-kelas/naik', [KeanggotaanController::class, 'naik'])->name('keanggotaan.naik');
        Route::post('keanggotaan/kenaikan-kelas/lulus', [KeanggotaanController::class, 'lulus'])->name('keanggotaan.lulus');
        Route::post('keanggotaan/{siswa}/status', [KeanggotaanController::class, 'ubahStatus'])->name('keanggotaan.status');
        Route::post('keanggotaan/{siswa}/kelas', [KeanggotaanController::class, 'ubahKelas'])->name('keanggotaan.kelas');
        Route::get('keanggotaan/{siswa}/bebas-pustaka', [KeanggotaanController::class, 'bebasPustaka'])->name('keanggotaan.bebas_pustaka');
    
        //Akun
        Route::get('/akun', [AkunController::class, 'index'])->name('akun');
        Route::post('/simpan_akun', [AkunController::class, 'simpan_akun'])->name('simpan_akun');
        Route::post('/edit_akun', [AkunController::class, 'edit_akun'])->name('edit_akun');
        Route::get('/hapus_akun/{id}', [AkunController::class, 'hapus_akun'])->name('hapus_akun');
    });

    Route::middleware(['role:2'])->prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
        //Buku
        Route::get('/buku', [PetugasBukuController::class, 'index'])->name('buku');
        Route::post('/simpan_buku', [PetugasBukuController::class, 'simpan_buku'])->name('simpan_buku');
        Route::post('/edit_buku', [PetugasBukuController::class, 'edit_buku'])->name('edit_buku');
        Route::get('/hapus_buku/{id}', [PetugasBukuController::class, 'hapus_buku'])->name('hapus_buku');
        Route::get('/cetak/{id}', [PetugasBukuController::class, 'cetak'])->name('cetak');
        Route::get('/cetakPdf/{id}', [PetugasBukuController::class, 'cetakPdf'])->name('cetakPdf');
        Route::post('/import', [PetugasBukuController::class, 'import_excel'])->name('import');
        Route::get('/downloadBarcode/{no_barcode}', [PetugasBukuController::class, 'downloadBarcode'])->name('downloadBarcode');
        Route::post('/admin/download-barcode', [PetugasBukuController::class, 'downloadBulkBarcode'])->name('downloadBulkBarcode');
        Route::get('downloadBarcodeSatuan/{id}', [PetugasBukuController::class, 'downloadBarcodeSatuan'])->name('downloadBarcodeSatuan');
        Route::get('/format_buku', [PetugasBukuController::class, 'download_format'])->name('format_buku');

        //Siswa
        Route::get('/siswa', [PetugasSiswaController::class, 'index'])->name('siswa');
        Route::post('/simpan_siswa', [PetugasSiswaController::class, 'simpan_siswa'])->name('simpan_siswa');
        Route::post('/import_siswa', [PetugasSiswaController::class, 'import_excel'])->name('import_siswa');
        Route::post('/edit_siswa', [PetugasSiswaController::class, 'edit_siswa'])->name('edit_siswa');
        Route::get('/hapus_siswa/{id}', [PetugasSiswaController::class, 'hapus_siswa'])->name('hapus_siswa');
        Route::get('/download_format_siswa', [PetugasSiswaController::class, 'download_format'])->name('download_format_siswa');
        Route::get('/qr-siswa/{id}', [PetugasSiswaController::class, 'qrSiswa'])->name('qr');
        Route::post('/qr-siswa-bulk', [PetugasSiswaController::class, 'qrBulk'])->name('qr_bulk');

        //barcode
        Route::get('/barcode-siswa/{id}', [PetugasSiswaController::class, 'barcodeSiswa'])->name('barcode');
        Route::post('/barcode-siswa-bulk', [PetugasSiswaController::class, 'barcodeBulk'])->name('barcode_bulk');
        
        //Peminjaman
        Route::get('/peminjaman', [PetugasPeminjamanController::class, 'index'])->name('peminjaman');
        Route::post('/simpan_peminjaman', [PetugasPeminjamanController::class, 'simpan_peminjaman'])->name('simpan_peminjaman');
        Route::post('/edit_peminjaman', [PetugasPeminjamanController::class, 'edit_peminjaman'])->name('edit_peminjaman');
        Route::get('/hapus_peminjaman/{id}', [PetugasPeminjamanController::class, 'hapus_peminjaman'])->name('hapus_peminjaman');
        Route::get('/cariSiswa', [PetugasPeminjamanController::class, 'cariSiswa'])->name('cariSiswa');
        Route::post('/export', [PetugasPeminjamanController::class, 'export_pinjaman'])->name('export');
        Route::get('/get-buku/{kode}', [PetugasPeminjamanController::class, 'show'])->name('get-buku');
        Route::get('/get-siswa/{nisn}', [PetugasPeminjamanController::class, 'show_siswa'])->name('get-siswa'); 
        // Sesuaikan nama controller dan method
        Route::get('/tambah_peminjaman', [PetugasPeminjamanController::class, 'coba'])->name('coba');


        // web.php
        Route::get('/siswa/search', [PetugasPeminjamanController::class, 'search'])->name('siswa.search');

       //Pengembalian
        Route::get('/pengembalian', [PetugasPengembalianController::class, 'index'])->name('pengembalian');
        Route::get('/cariBarcode/{barcode}', [PetugasPengembalianController::class, 'cariBarcode'])->name('cariBarcode');
        Route::post('/simpan_pengembalian', [PetugasPengembalianController::class, 'simpan_pengembalian'])->name('simpan_pengembalian');
        Route::get('/get-buku-kembali/{kode}', [PetugasPengembalianController::class, 'show'])->name('get-buku-kembali');
        Route::get('/get-siswa-by-buku/{kodeBuku}', [PetugasPengembalianController::class, 'show_siswa_by_buku'])->name('get-siswa-by-buku');

        //Denda
        Route::get('/denda', [PetugasDendaController::class, 'index'])->name('denda.index');
        Route::post('/denda/{denda}/bayar', [PetugasDendaController::class, 'bayar'])->name('denda.bayar');
        Route::post('/denda/{denda}/dispensasi', [PetugasDendaController::class, 'dispensasi'])->name('denda.dispensasi');
        //Denda Pengaturan
        Route::get('/denda-pengaturan', [PetugasPengaturanDendaController::class, 'index'])->name('denda.pengaturan');
        Route::put('/denda-pengaturan', [PetugasPengaturanDendaController::class, 'update'])->name('denda.pengaturan.update');
        Route::post('/denda-pengaturan/libur', [PetugasPengaturanDendaController::class, 'tambahLibur'])->name('denda.libur.store');
        Route::delete('/denda-pengaturan/libur/{hariLibur}', [PetugasPengaturanDendaController::class, 'hapusLibur'])->name('denda.libur.destroy');

        //Laporan
        Route::get('laporan-bulanan', [PetugasLaporanBulananController::class, 'index'])->name('laporan.index');
        Route::get('laporan-bulanan/pdf', [PetugasLaporanBulananController::class, 'pdf'])->name('laporan.pdf');
        Route::get('laporan-bulanan/csv', [PetugasLaporanBulananController::class, 'csv'])->name('laporan.csv');

        //Keanggotaan
        Route::get('keanggotaan', [PetugasKeanggotaanController::class, 'index'])->name('keanggotaan.index');
        Route::get('keanggotaan/kenaikan-kelas', [PetugasKeanggotaanController::class, 'kenaikan'])->name('keanggotaan.kenaikan');
        Route::post('keanggotaan/kenaikan-kelas/naik', [PetugasKeanggotaanController::class, 'naik'])->name('keanggotaan.naik');
        Route::post('keanggotaan/kenaikan-kelas/lulus', [PetugasKeanggotaanController::class, 'lulus'])->name('keanggotaan.lulus');
        Route::post('keanggotaan/{siswa}/status', [PetugasKeanggotaanController::class, 'ubahStatus'])->name('keanggotaan.status');
        Route::post('keanggotaan/{siswa}/kelas', [PetugasKeanggotaanController::class, 'ubahKelas'])->name('keanggotaan.kelas');
        Route::get('keanggotaan/{siswa}/bebas-pustaka', [PetugasKeanggotaanController::class, 'bebasPustaka'])->name('keanggotaan.bebas_pustaka');
    });

});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
