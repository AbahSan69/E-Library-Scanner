@extends('admin.layouts.main')
@section('content')
<div id="page-container" class="page-header-dark main-content-boxed">
  @include('admin.layouts.header')
  
  <main id="main-container">
    <div class="bg-primary-darker">
      <div class="content py-3">
        <div class="d-lg-none">
          <button type="button" class="btn w-100 btn-alt-secondary d-flex justify-content-between align-items-center"
                  data-toggle="class-toggle" data-target="#main-navigation" data-class="d-none">
            Menu
            <i class="fa fa-bars"></i>
          </button>
        </div>
        @include('admin.layouts.navbar')

        <div class="content">
          <div class="block block-rounded">
            <div class="block-header">
              <h3 class="block-title">Tambah Data Peminjaman</h3>
            </div>
            <div class="block-content block-content-full">

              <form action="{{ route('admin.simpan_pengembalian') }}" method="POST" id="formPeminjaman">
                @csrf

                <div class="row">
                  {{-- === KOLOM KIRI === --}}
                  <div class="col-md-5">
                    <div class="mb-4">
                      <label for="siswa" class="form-label">Siswa <span class="text-danger">*</span></label>
                      <input type="text" id="search" class="form-control" placeholder="Ketik nama atau NISN..." required>
                      <ul id="result" class="list-group mt-2"></ul>
                      <input type="hidden" id="siswa_id" name="siswa_id">
                    </div>

                    <div class="mb-4" hidden>
                      <label for="barcode" class="form-label">Scan Barcode Buku <span class="text-danger">*</span></label>
                      <input type="text" name="barcode" id="barcode" class="form-control" autofocus>
                      <small class="text-muted">Arahkan scanner ke barcode buku</small>
                    </div>
                  </div>

                  {{-- === KOLOM KANAN === --}}
                  <div class="col-md-7">
                    <div class="table-responsive">
                      <table class="table table-vcenter" id="tabelBuku">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th style="width: 120px;">Barcode</th>
                            <th>Judul</th>
                            <th style="width: 120px;">Status</th>
                            <th>Qty</th>
                            <th style="width: 80px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr class="text-center text-muted" id="emptyRow">
                            <td colspan="5">📚 Belum ada buku yang discan.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <input type="hidden" name="barcodes" id="barcodes">

                    <div class="text-end mt-3">
                      <button type="submit" id="btnSubmit" class="btn btn-primary btn-sm" disabled>
                        <i class="fa fa-save me-1"></i> Simpan Pengembalian
                      </button>
                    </div>
                  </div>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  @include('admin.layouts.footer')
</div>

{{-- Scripts --}}
<script src="{{ asset('assets/oneui/js/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/oneui/js/pages/be_forms_validation.min.js') }}"></script>

<script>
  let daftarBarcode = [];
  let buffer = "";
  let scanTimer = null;

  // ✅ Fungsi hitung check digit EAN13
  function hitungCheckDigitEAN13(kode12) {
    let sum = 0;
    for (let i = 0; i < 12; i++) {
      let digit = parseInt(kode12.charAt(i));
      sum += (i % 2 === 0) ? digit : digit * 3;
    }
    return (10 - (sum % 10)) % 10;
  }

  // ✅ Listener global — tangkap semua input dari scanner
  document.addEventListener('keydown', function(e) {
    // Abaikan tombol kontrol
    if (e.key.length !== 1 && e.key !== 'Enter') return;

    if (e.key === 'Enter') {
      if (buffer.length >= 12) {
        prosesScanBarcode(buffer.trim());
      }
      buffer = "";
      return;
    }

    buffer += e.key;
    clearTimeout(scanTimer);
    scanTimer = setTimeout(() => buffer = "", 300);
  });

  // ✅ Proses barcode saat scanner selesai
  function prosesScanBarcode(kode) {
    // Tambahkan check digit kalau hanya 12 angka
    if (/^\d{12}$/.test(kode)) {
      kode += hitungCheckDigitEAN13(kode);
    }

    if (!/^\d{13}$/.test(kode)) {
      alert("Barcode tidak valid (harus 12/13 digit angka)!");
      return;
    }

    // Jika buku sudah ada → tambah qty
    const existing = daftarBarcode.find(b => b.kode === kode);
    if (existing) {
      existing.qty = (existing.qty || 1) + 1;
      renderTable();
      return;
    }

    // Tambahkan data sementara
    let index = daftarBarcode.length;
    daftarBarcode.push({ kode: kode, judul: "-", status: "Loading...", qty: 1 });
    renderTable();

    // Ambil data buku dari endpoint (pengembalian)
    fetch(`/admin/get-buku-kembali/${kode}`)
      .then(res => res.json())
      .then(data => {
        if (data && data.judul) {
          daftarBarcode[index].judul = data.judul;
          daftarBarcode[index].status = "✅ Valid";
        } else {
          daftarBarcode[index].judul = "-";
          daftarBarcode[index].status = "❌ Tidak ditemukan";
        }
        renderTable();
      })
      .catch(() => {
        daftarBarcode[index].status = "❌ Error koneksi";
        renderTable();
      });
  }

  // ✅ Render tabel
  function renderTable() {
    const tbody = document.querySelector("#tabelBuku tbody");
    tbody.innerHTML = "";

    if (daftarBarcode.length === 0) {
      tbody.innerHTML = `
        <tr class="text-center text-muted">
          <td colspan="6">📚 Belum ada buku yang discan.</td>
        </tr>
      `;
      document.getElementById("btnSubmit").disabled = true;
    } else {
      daftarBarcode.forEach((b, i) => {
        tbody.innerHTML += `
          <tr>
            <td>${i + 1}</td>
            <td>${b.kode}</td>
            <td>${b.judul}</td>
            <td>${b.status}</td>
            <td style="width: 90px;">
              <input type="number" min="1" class="form-control form-control-sm"
                value="${b.qty || 1}" onchange="ubahQty(${i}, this.value)">
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" onclick="hapusBarcode(${i})">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      });
      document.getElementById("btnSubmit").disabled = false;
    }

    // Simpan data ke hidden input
    document.getElementById("barcodes").value = JSON.stringify(daftarBarcode);
  }

  // ✅ Ubah qty manual
  function ubahQty(i, value) {
    const val = parseInt(value);
    daftarBarcode[i].qty = val > 0 ? val : 1;
    renderTable();
  }

  // ✅ Hapus item
  function hapusBarcode(i) {
    daftarBarcode.splice(i, 1);
    renderTable();
  }

  // ===============================
  // 🔍 Live Search Siswa (tetap)
  // ===============================
  $(document).ready(function(){
    $('#search').on('keyup', function(){
      let query = $(this).val();

      if(query.length > 1){
        $.ajax({
          url: "{{ route('admin.siswa.search') }}",
          type: "GET",
          data: {'q': query},
          success: function(data){
            $('#result').empty();
            if(data.length > 0){
              $.each(data, function(index, siswa){
                $('#result').append(
                  '<li class="list-group-item list-group-item-action" data-id="'+siswa.id+'">'+siswa.nisn+' - '+siswa.nama+'</li>'
                );
              });
            } else {
              $('#result').append('<li class="list-group-item disabled">Tidak ada hasil</li>');
            }
          }
        });
      } else {
        $('#result').empty();
      }
    });

    $(document).on('click', '#result li', function(){
      let id = $(this).data('id');
      let text = $(this).text();

      $('#search').val(text);
      $('#siswa_id').val(id);
      $('#result').empty();
    });

    $('#btnPilih').on('click', function(){
      let siswaText = $('#search').val();
      let siswaId   = $('#siswa_id').val();

      if(siswaId){
        $('#siswa_terpilih').val(siswaText); 
        $('#siswa_terpilih_id').val(siswaId);
        $('#siswaModal').modal('hide');
      } else {
        alert("Silakan pilih siswa dulu!");
      }
    });
  });
</script>


@endsection
