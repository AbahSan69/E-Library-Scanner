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

                  
 <div id="siswa-info-display" class="block block-rounded block-mode-bordered bg-light" style="display:none;">
  <div class="block-header p-2 bg-success-light">
   <h6 class="block-title fs-sm text-success m-0"><i class="fa fa-user me-1"></i> SISWA TERPILIH</h6>
   <div class="block-options">
    <button type="button" class="btn-block-option text-danger" onclick="clearSiswa()">
     <i class="fa fa-trash me-1"></i> Clear
    </button>
   </div>
  </div>
  <div class="block-content fs-sm p-3">
   <p class="mb-1"><strong>Nama:</strong> <span id="display-nama"></span></p>
   <p class="mb-1"><strong>NISN:</strong> <span id="display-nisn"></span></p>
   <p class="mb-0"><strong>Kelas:</strong> <span id="display-kelas"></span></p>
  </div>
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

{{-- <script>
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
</script> --}}

<script>
    let daftarBarcode = [];
    let buffer = "";
    let scanTimer = null;
    const NISN_LENGTH = 10; // Asumsi panjang NISN (siswa barcode)
    const EAN13_LENGTH = 13; // Asumsi panjang EAN13 (buku barcode)
  
    // ✅ Fungsi hitung check digit EAN13 (TETAP)
    function hitungCheckDigitEAN13(kode12) {
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            let digit = parseInt(kode12.charAt(i));
            sum += (i % 2 === 0) ? digit : digit * 3;
        }
        return (10 - (sum % 10)) % 10;
    }
  
    // 🚀 LISTENER GLOBAL UNTUK SCANNER BARCODE BUKU
    document.addEventListener('keydown', function(e) {
        const activeElement = document.activeElement;
  
        // 🛑 KASUS 1 & 2: INPUT FOKUS (SISWA, TANGGAL, QTY)
        if (activeElement && activeElement.tagName === 'INPUT') {
            return;
        }
  
        // 🚀 KASUS 3: TIDAK ADA INPUT YANG FOKUS (ASUMSI INPUT SCANNER BUKU)
        if (e.key.length !== 1 && e.key !== 'Enter') return;
        e.preventDefault(); 
        
        if (e.key === 'Enter') {
            if (buffer.length >= 10) { 
                prosesScanInputBukuGlobal(buffer.trim());
            }
            buffer = "";
            return;
        }
        buffer += e.key;
        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => buffer = "", 300);
    });
  
    // ✅ Fungsi proses input dari scanner (DIMODIFIKASI untuk global scan)
    function prosesScanInputBukuGlobal(kode) {
        kode = kode.replace(/[^0-9]/g, '');
        
        if (kode.length === 12) {
            kode += hitungCheckDigitEAN13(kode.substring(0, 12));
            prosesScanBarcodeBuku(kode);
        } else if (kode.length === EAN13_LENGTH) {
            prosesScanBarcodeBuku(kode);
        } else {
            alert("Barcode tidak valid (harus 12/13 digit untuk Buku). Untuk Siswa, silakan klik input dan scan di sana.");
        }
    }
  
    // 🆕 Fungsi untuk menampilkan detail siswa
    function displaySiswaInfo(data) {
        const searchInput = document.getElementById('search');
        const infoDisplay = document.getElementById('siswa-info-display');
  
        if (data && data.id) {
            // Isi input tersembunyi
            document.getElementById('siswa_id').value = data.id;
            
            // Isi area display
            document.getElementById('display-nisn').textContent = data.nisn || '-';
            document.getElementById('display-nama').textContent = data.nama || '-';
            document.getElementById('display-kelas').textContent = data.kelas || '-'; // Membutuhkan kolom 'kelas' dari backend
            
            // Sembunyikan input search, tampilkan display info
            searchInput.style.display = 'none';
            infoDisplay.style.display = 'block';
            
            // Aktifkan submit jika ada buku
            const validBarcodes = daftarBarcode.filter(b => b.status === "✅ Valid");
            document.getElementById("btnSubmit").disabled = validBarcodes.length === 0;
  
            document.getElementById('tanggal_kembali').focus(); 
        } else {
            // Jika data tidak valid/kosong, bersihkan
            clearSiswa();
        }
        $('#result').empty();
    }
  
    // 🆕 Fungsi untuk membersihkan data siswa (Tombol Trash)
    window.clearSiswa = function() {
        document.getElementById('siswa_id').value = '';
        document.getElementById('search').value = '';
        document.getElementById('search').style.display = 'block';
        document.getElementById('siswa-info-display').style.display = 'none';
        document.getElementById("btnSubmit").disabled = true;
        $('#result').empty();
        document.getElementById('search').focus(); 
    };
  
    // ✅ Fungsi ambil data Siswa via fetch (DENGAN PENGECEKAN STATUS HTTP)
    function fetchSiswaByBarcode(nisn) {
        fetch(`/admin/get-siswa/${nisn}`)
            .then(res => {
                if (!res.ok) {
                    if (res.status === 404) {
                        throw new Error(`NISN ${nisn} tidak ditemukan di server (404).`);
                    }
                    throw new Error(`Server error: ${res.status} ${res.statusText}`);
                }
                return res.json();
            })
            .then(data => {
                if (data && data.id) {
                    alert(`Siswa ditemukan: ${data.nama} (NISN: ${data.nisn})`);
                    displaySiswaInfo(data); // ⬅️ Panggil fungsi baru
                } else {
                    alert(`Siswa dengan NISN ${nisn} tidak ditemukan!`);
                    clearSiswa();
                }
                $('#result').empty(); 
            })
            .catch(error => {
                console.error(error); 
                alert("Error koneksi atau server: " + error.message);
                clearSiswa();
            });
    }
  
    // ✅ Fungsi proses barcode buku (TETAP SAMA)
    function prosesScanBarcodeBuku(kode) {
        // ... (Logika proses buku tetap sama) ...
        const existing = daftarBarcode.find(b => b.kode === kode);
        if (existing) {
            if (existing.status === "✅ Valid" && existing.qty < existing.stok) {
                existing.qty++;
            } else if (existing.status.startsWith("❌")) {
                alert(`Barcode ${kode} sudah di-scan, namun statusnya "${existing.status}".`);
            } else {
                alert(`Stok buku ini hanya ${existing.stok}. Tidak bisa menambah qty lebih dari stok.`);
            }
            renderTable();
            return;
        }
  
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
                    daftarBarcode[index].stok = 0;
                }
                renderTable();
            })
            .catch(() => {
                daftarBarcode[index].status = "❌ Error koneksi";
                daftarBarcode[index].stok = 0;
                renderTable();
            });
    }
    
    // ✅ Render tabel daftar buku (TETAP SAMA)
    function renderTable() {
        const tbody = document.querySelector("#tabelBuku tbody");
        tbody.innerHTML = "";
  
        const validBarcodes = daftarBarcode.filter(b => b.status === "✅ Valid");
        const siswaTerpilih = document.getElementById('siswa_id').value; // Ambil nilai siswa terpilih
  
        if (daftarBarcode.length === 0) {
            tbody.innerHTML = `
                <tr class="text-center text-muted">
                    <td colspan="6">📚 Belum ada buku yang discan.</td>
                </tr>
            `;
        } else {
            // ... (Logika render tabel) ...
            daftarBarcode.forEach((b, i) => {
                const isInvalid = b.status.startsWith('❌');
                const rowClass = isInvalid ? 'table-danger' : '';
                
                tbody.innerHTML += `
                    <tr class="${rowClass}">
                        <td>${i + 1}</td>
                        <td>${b.kode}</td>
                        <td>${b.judul}</td>
                        <td>${b.status}</td>
                        <td style="width: 90px;">
                            <input type="number" min="1" max="${b.stok}" class="form-control form-control-sm" 
                                value="${b.qty || 1}" onchange="ubahQty(${i}, this.value)" ${isInvalid ? 'disabled' : ''}>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" onclick="hapusBarcode(${i})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
  
        // Logika tombol submit (harus ada siswa DAN buku valid)
        document.getElementById("btnSubmit").disabled = !siswaTerpilih || validBarcodes.length === 0;
  
        const finalBarcodes = validBarcodes.map(b => ({
            kode: b.kode,
            qty: b.qty,
        }));
        document.getElementById("barcodes").value = JSON.stringify(finalBarcodes);
    }
  
    // ✅ Ubah qty manual di tabel (TETAP SAMA)
    function ubahQty(i, value) {
        let qty = parseInt(value);
        if (qty > daftarBarcode[i].stok) {
            alert(`Qty tidak boleh lebih dari stok (${daftarBarcode[i].stok})`);
            qty = daftarBarcode[i].stok;
        } else if (qty < 1 || isNaN(qty)) {
            qty = 1;
        }
        daftarBarcode[i].qty = qty;
        renderTable();
    }
  
    // ✅ Hapus buku dari daftar (TETAP SAMA)
    function hapusBarcode(i) {
        daftarBarcode.splice(i, 1);
        renderTable();
    }
  
    // ===============================
    // 🔍 Live Search Siswa & Scan Barcode Siswa (JQUERY)
    // ===============================
    $(document).ready(function(){
        
        // 🚀 LOGIKA SCAN SISWA / INPUT MANUAL SISWA (TEKAN ENTER)
        $('#search').on('keydown', function(e){
            if (e.key === 'Enter') {
                e.preventDefault(); 
                const inputVal = $(this).val().trim();
                
                if (inputVal.length === NISN_LENGTH && /^\d+$/.test(inputVal)) {
                    fetchSiswaByBarcode(inputVal);
                } else if (inputVal.length > 0) {
                    const firstItem = $('#result li:first');
                    if (firstItem.length > 0 && !firstItem.hasClass('disabled')) {
                         firstItem.trigger('click');
                    } else {
                         alert("Silakan pilih siswa dari daftar hasil pencarian, atau masukkan/scan NISN 10 digit.");
                    }
                }
            }
        });
  
        // Live Search Siswa keyup AJAX
        $('#search').on('keyup', function(){
            let query = $(this).val();
  
            // Hanya lakukan live search jika input search sedang visible (belum ada siswa terpilih)
            if($('#siswa-info-display').css('display') === 'none') {
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
                                        '<li class="list-group-item list-group-item-action" data-id="'+siswa.id+'" data-nisn="'+siswa.nisn+'" data-nama="'+siswa.nama+'" data-kelas="'+(siswa.kelas || '-')+'">'+siswa.nisn+' - '+siswa.nama+'</li>'
                                    );
                                });
                            } else {
                                $('#result').append('<li class="list-group-item disabled">Tidak ada hasil</li>');
                            }
                        }
                    });
                } else {
                    $('#result').empty();
                    if(query.length === 0) {
                        clearSiswa(); // Panggil clearSiswa jika input dikosongkan saat live search
                    }
                }
            }
        });
  
        // Logika click pada hasil live search (diperbarui untuk display info)
        $(document).on('click', '#result li', function(){
            let id = $(this).data('id');
            let nisn = $(this).data('nisn');
            let nama = $(this).data('nama');
            let kelas = $(this).data('kelas');
            
            displaySiswaInfo({id: id, nisn: nisn, nama: nama, kelas: kelas}); // ⬅️ Panggil fungsi baru
        });
    });
  </script>

@endsection
