@extends('petugas.layouts.main')
@section('content')
<div id="page-container" class="page-header-dark main-content-boxed">
    @include('petugas.layouts.header')
    
    <main id="main-container">

      <div class="bg-primary-darker">
        <div class="content py-3">
          <div class="d-lg-none">
            <button type="button" class="btn w-100 btn-alt-secondary d-flex justify-content-between align-items-center" data-toggle="class-toggle" data-target="#main-navigation" data-class="d-none">
              Menu
              <i class="fa fa-bars"></i>
            </button>
          </div>
          @include('petugas.layouts.navbar')
          <div class="content">

            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title">Detail Pengembalian</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="mb-4">
                        <label for="barcode" class="form-label">Scan Barcode <span class="text-danger">*</span></label>
                        <input type="text" name="barcode" id="barcode" class="form-control" autofocus required>
                        <small class="text-muted">Arahkan scanner ke barcode buku!</small>
                    </div>
                    <form action="{{ route('petugas.simpan_pengembalian') }}" method="POST">
                        @csrf
                    <!-- Tabel detail (awal hidden) -->
                    <div id="detail-peminjaman" style="display: none;">
                        <table class="table table-vcenter">
                            <tbody>
                                <input type="hidden" name="id" id="id">
                                <tr><td class="ps-0 fw-medium">NISN</td><td class="pe-0 fw-medium text-end" id="nisn">-</td></tr>
                                <tr><td class="ps-0 fw-medium">Nama Peminjam</td><td class="pe-0 fw-medium text-end" id="nama">-</td></tr>
                                <tr><td class="ps-0 fw-medium">ISBN</td><td class="pe-0 fw-medium text-end" id="isbn">-</td></tr>
                                <tr><td class="ps-0 fw-medium">Judul</td><td class="pe-0 fw-medium text-end" id="judul">-</td></tr>
                                <tr><td class="ps-0 fw-medium">Tanggal Pinjam</td><td class="pe-0 fw-medium text-end" id="tanggal_pinjam">-</td></tr>
                                <tr><td class="ps-0 fw-medium">Tanggal Kembali</td><td class="pe-0 fw-medium text-end" id="tanggal_kembali">-</td></tr>
                                <tr><td class="ps-0 fw-medium">Status</td><td class="pe-0 fw-medium text-end" id="status"></td>-</tr>
                                <tr>
                                    <td></td>
                                    <td class="pe-0 fw-bold text-end">
                                        <button type="submit" class="btn btn-sm btn-primary">Simpan Pengembalian</button>
                                    </td>
                                </tr>
                            </tbody>    
                        </table>
                    </div>
                    </form>
                </div>
            </div>
            
          </div>
        </div>
      </div>

    </main>
    @include('petugas.layouts.footer')
</div>
      {{-- <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script> --}}
      <script src="{{ asset('assets/oneui/js/plugins/select2/js/select2.full.min.js') }}""></script>
      {{-- <script src="{{ asset('assets/oneui/s/plugins/jquery-validation/jquery.validate.min.js') }}"></script> --}}
      {{-- <script src="{{ asset('assets/oneui/js/plugins/jquery-validation/additional-methods.js') }}"></script> --}}
      <script src="{{ asset('assets/oneui/js/pages/be_forms_validation.min.js') }}"></script>
      <script>
        document.addEventListener("DOMContentLoaded", function () {
        
            // --- Fungsi hitung check digit EAN-13 ---
            function calculateEAN13CheckDigit(code) {
                if (code.length !== 12) return null; // hanya untuk 12 digit
                let sum = 0;
                for (let i = 0; i < 12; i++) {
                    let digit = parseInt(code[i], 10);
                    sum += (i % 2 === 0) ? digit : digit * 3;
                }
                return (10 - (sum % 10)) % 10;
            }
        
            // --- Event saat input barcode berubah ---
            const barcodeInput = document.getElementById('barcode');
            barcodeInput.addEventListener('change', function () {
                let barcode = this.value.trim();
        
                // Kalau hasil scan 12 digit, tambahkan check digit biar jadi 13
                if (barcode.length === 12) {
                    let checkDigit = calculateEAN13CheckDigit(barcode);
                    barcode = barcode + checkDigit;
                    this.value = barcode; // update input agar jadi 13 digit
                }
        
                if (barcode !== "") {
                    fetch(`/petugas/cariBarcode/${barcode}`)
                        .then(response => response.json())
                        .then(res => {
                            if (res.success) {
                                // isi field hidden (id)
                                document.getElementById('id').value = res.data.id;
        
                                // isi informasi siswa & buku
                                document.getElementById('nisn').innerText = res.data.nisn;
                                document.getElementById('nama').innerText = res.data.nama;
                                document.getElementById('isbn').innerText = res.data.isbn;
                                document.getElementById('judul').innerText = res.data.judul;
                                document.getElementById('tanggal_pinjam').innerText = res.data.tanggal_pinjam;
                                document.getElementById('tanggal_kembali').innerText = res.data.tanggal_kembali;
        
                                // Hitung status keterlambatan
                                let statusText = res.data.status;
                                const today = new Date();
                                const kembali = new Date(res.data.tanggal_kembali);
        
                                if (statusText === "Dipinjam" && today > kembali) {
                                    statusText = "Terlambat";
                                }
        
                                document.getElementById('status').innerText = statusText;
                                document.getElementById('detail-peminjaman').style.display = "block";
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Data tidak ditemukan',
                                    text: res.message,
                                    confirmButtonText: 'OK'
                                });
                                document.getElementById('detail-peminjaman').style.display = "none";
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan saat mengambil data!',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        
        });
        </script>        
</div>
@endsection