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

              <form action="{{ route('admin.simpan_peminjaman') }}" method="POST" id="formPeminjaman">
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

                    <div class="mb-4">
                      <label for="tanggal_kembali" class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                      <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control" required>
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
                        <i class="fa fa-save me-1"></i> Simpan Peminjaman
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

  // ✅ Listener global untuk menangkap input scanner
  document.addEventListener('keydown', function(e) {
    // Abaikan tombol kontrol (Shift, Tab, dll)
    if (e.key.length !== 1 && e.key !== 'Enter') return;

    // Scanner biasanya diakhiri dengan "Enter"
    if (e.key === 'Enter') {
      if (buffer.length >= 12) {
        prosesScanBarcode(buffer.trim());
      }
      buffer = "";
      return;
    }

    // Tambahkan karakter ke buffer
    buffer += e.key;

    // Reset otomatis jika jeda terlalu lama (scanner cepat)
    clearTimeout(scanTimer);
    scanTimer = setTimeout(() => buffer = "", 300);
  });

  // ✅ Fungsi proses barcode setelah selesai scan
  function prosesScanBarcode(kode) {
    // Jika 12 digit → hitung check digit ke-13
    if (/^\d{12}$/.test(kode)) {
      kode += hitungCheckDigitEAN13(kode);
    }

    // Validasi kode
    if (!/^\d{13}$/.test(kode)) {
      alert("Barcode tidak valid (harus 12/13 digit angka)!");
      return;
    }

    // Cek apakah barcode sudah ada
    const existing = daftarBarcode.find(b => b.kode === kode);
    if (existing) {
      if (existing.qty < existing.stok) {
        existing.qty++;
      } else {
        alert(`Stok buku ini hanya ${existing.stok}. Tidak bisa menambah qty lebih dari stok.`);
      }
      renderTable();
      return;
    }

    // Jika belum ada, tambahkan sementara dengan status "Loading..."
    let index = daftarBarcode.length;
    daftarBarcode.push({ kode: kode, judul: "-", status: "Loading...", qty: 1, stok: 1 });
    renderTable();

    // Ambil data buku via fetch
    fetch(`/admin/get-buku/${kode}`)
      .then(res => res.json())
      .then(data => {
        if (data && data.judul) {
          daftarBarcode[index].judul = data.judul;
          daftarBarcode[index].stok = data.stok || 1;
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

  // ✅ Render tabel daftar buku
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
              <input type="number" min="1" max="${b.stok}" class="form-control form-control-sm" 
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

    // Simpan data ke input hidden untuk dikirim ke server
    document.getElementById("barcodes").value = JSON.stringify(daftarBarcode);
  }

  // ✅ Ubah qty manual di tabel
  function ubahQty(i, value) {
    let qty = parseInt(value);
    if (qty > daftarBarcode[i].stok) {
      alert(`Qty tidak boleh lebih dari stok (${daftarBarcode[i].stok})`);
      qty = daftarBarcode[i].stok;
    } else if (qty < 1) {
      qty = 1;
    }
    daftarBarcode[i].qty = qty;
    renderTable();
  }

  // ✅ Hapus buku dari daftar
  function hapusBarcode(i) {
    daftarBarcode.splice(i, 1);
    renderTable();
  }

  // ===============================
  // 🔍 Live Search Siswa (tetap seperti aslinya)
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
