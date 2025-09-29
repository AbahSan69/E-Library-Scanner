@extends('petugas.layouts.main')
@section('content')
<style>
  #result .list-group-item {
      cursor: pointer; /* tangan */
  }

  #overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 9999;
  }
</style>

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
            <form action="{{ route('petugas.peminjaman') }}" method="GET">
                <div class="input-group">
                  <input type="text" class="form-control" name="q" placeholder="Cari Peminjaman" value="{{ request()->q }}">
                  <!-- Search Icon as Submit Button -->
                  <button class="input-group-text btn btn-primary" type="submit">
                    <i class="fa fa-fw fa-search"></i>
                  </button>
                </div>
            </form>
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                  {{-- <h3 class="block-title">List Peminjaman (Total : {{ $total_peminjaman }}) </h3> --}}
                  <div class="block-options">
                    <div class="dropdown d-inline-block">
                      <button type="button" class="btn btn-sm btn-alt-secondary space-x-1" id="dropdown-analytics-overview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Filter Data">
                        <i class="fa fa-fw fa-list opacity-50"></i>
                        <i class="fa fa-fw fa-angle-down"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end fs-sm" aria-labelledby="dropdown-analytics-overview">
                        <!-- Range Tanggal (pakai modal) -->
                        <button type="button" 
        class="dropdown-item fw-medium d-flex align-items-center justify-content-between" 
        data-bs-toggle="modal" 
        data-bs-target="#modal-block-vcenter-extra-large-filterTanggal">
    <span>Tanggal</span>
    @if(request('start_date') && request('end_date'))
        <i class="fa fa-check"></i>
    @endif
</button>

                        <!-- Hari Ini -->
                        <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between" href="{{ route('petugas.peminjaman', ['today' => 1]) }}">
                          <span>Hari Ini</span>
                          @if(request('today'))
                            <i class="fa fa-check"></i>
                          @endif
                        </a>

                        <!-- Status Dipinjam -->
                        <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between" href="{{ route('petugas.peminjaman', ['status' => 'Dipinjam']) }}">
                          <span>Status Dipinjam</span>
                          @if(request('status') === 'Dipinjam')
                            <i class="fa fa-check"></i>
                          @endif
                        </a>

                        <!-- Status Dikembalikan -->
                        <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between" href="{{ route('petugas.peminjaman', ['status' => 'Dikembalikan']) }}">
                          <span>Status Dikembalikan</span>
                          @if(request('status') === 'Dikembalikan')
                            <i class="fa fa-check"></i>
                          @endif
                        </a>

                        <!-- Semua -->
                        <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between" href="{{ route('petugas.peminjaman', ['status' => 'Semua']) }}">
                          <span>Semua</span>
                          @if(request('status') === 'Semua' || (!request('today') && !request('start_date') && !request('status')))
                            <i class="fa fa-check"></i>
                          @endif
                        </a>
                      </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-alt-success js-bs-tooltip-enabled me-1" title="Export Data" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-export">
                      <i class="fa fa-fw fa-file-excel"></i>
                      <span>Export</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-alt-primary js-bs-tooltip-enabled me-1" title="Tambah Data" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-tambah">
                        <i class="fa fa-fw fa-plus"></i>
                        <span>Tambah Data</span>
                    </button>
                  </div>
                </div>
                <div class="block-content">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr>
                          <th class="text-center" style="width: 100px;">
                            No
                          </th>
                          <th style="width: 100px;">NISN</th>
                          <th>Nama peminjaman</th>
                          <th style="width: 100px;">ISBN</th>
                          <th >Judul Buku</th>
                          <th style="width: 100px;">Tanggal Pinjam</th>
                          <th style="width: 100px;">Tanggal Kembali</th>
                          <th style="width: 100px;">Status</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                      </thead>
                      @if ($peminjaman->count() > 0)
                      @php
                      $no = 0;
                    @endphp
                    @foreach ($peminjaman as $list_peminjaman)
                    <tbody>
                      <tr>
                        <td class="text-center">
                          {{ $no = $no + 1 }}
                        </td>
                        <td class="fw-semibold fs-sm">
                            {{ $list_peminjaman->siswa->nisn }}
                        </td>
                        <td class="fw-semibold fs-sm">
                            {{ $list_peminjaman->siswa->nama }}
                        </td>
                        <td class="fw-semibold fs-sm">
                          {{ $list_peminjaman->buku->isbn }}
                        </td>
                        <td class="fw-semibold fs-sm">
                          {{ $list_peminjaman->buku->judul }}
                        </td>
                        <td class="fw-semibold fs-sm">
                          {{ $list_peminjaman->tanggal_pinjam }}
                        </td>
                        <td class="fw-semibold fs-sm">
                          {{ $list_peminjaman->tanggal_kembali }}
                        </td>
                        <td class="fw-semibold fs-sm">
                          @switch($list_peminjaman->status_aktif)
                              @case('Dipinjam')
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success">
                                      {{ $list_peminjaman->status_aktif }}
                                  </span>
                                  @break
                      
                              @case('Dikembalikan')
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-info-light text-primary">
                                      {{ $list_peminjaman->status_aktif }}
                                  </span>
                                  @break
                      
                              @case('Terlambat')
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-warning-light text-warning">
                                      {{ $list_peminjaman->status_aktif }}
                                  </span>
                                  @break
                      
                              @default
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-secondary-light text-secondary">
                                      {{ $list_peminjaman->status_aktif }}
                                  </span>
                          @endswitch
                      </td>                       
                        <td class="text-center">
                          <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-alt-warning js-bs-tooltip-enabled me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-tambah-{{ $list_peminjaman->id }}">
                              <i class="fa fa-fw fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-alt-danger js-bs-tooltip-enabled" data-bs-toggle="tooltip" aria-label="Delete" data-bs-original-title="Delete" title="Hapus" onclick="confirmationHapusData('{{ route('petugas.hapus_peminjaman', $list_peminjaman->id) }}')">
                              <i class="fa fa-fw fa-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>

                    <!-- modal Edit Data -->
                    <div class="modal fade" id="modal-block-vcenter-extra-large-tambah-{{ $list_peminjaman->id }}" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="block block-rounded block-transparent mb-0">
                            <div class="block-header block-header-default">
                              <h3 class="block-title">Modal Edit Peminjaman</h3>
                              <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                  <i class="fa fa-fw fa-times"></i>
                                </button>
                              </div>
                            </div>
                          <div class="block-content fs-sm">
                          <form class="space-y-4" action="{{ route('petugas.edit_peminjaman') }}" method="POST">
                          @csrf
                            <input type="hidden" name="id" id="id" value="{{ $list_peminjaman->id }}">
                              <div class="row">
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-nama">NISN <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="nisn" name="nisn"  placeholder="Masukan nisn ..." value="{{ $list_peminjaman->siswa->nisn }}" readonly required>
                                </div>
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-nama">Nama <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="nama" name="nama"  placeholder="Masukan nama ..." value="{{ $list_peminjaman->siswa->nama }}" readonly required>
                                </div>
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-isbn">ISBN <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="isbn" name="isbn"  placeholder="Masukan isbn ..." value="{{ $list_peminjaman->buku->isbn }}" readonly required>
                                </div>
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-judul">ISBN <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="judul" name="judul"  placeholder="Masukan judul ..." value="{{ $list_peminjaman->buku->judul }}" readonly required>
                                </div>
                                <div class="mb-4">
                                  <label for="tanggal-pinjam" class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                                  <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="form-control" value="{{ $list_peminjaman->tanggal_pinjam }}" required>
                                </div>
                                <div class="mb-4">
                                  <label for="tanggal-kembali" class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                                  <input type="date" id="tanggal_kembali" name="tanggal_kembali" class="form-control" value="{{ $list_peminjaman->tanggal_kembali }}" required>
                                </div>
                              </div>
                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                              <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Close</button>
                              <button type="submit" id="btn-register" class="btn btn-sm btn-primary"">Tambah</button>
                            </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                    @endforeach
                      @else
                      <tr>
                        <td colspan="9" class="text-center text-danger fw-semibold fs-sm">Peminjaman kosong atau tidak ditemukan</td>
                      </tr>
                      @endif
                    </table>
                    <div class="mt-3">
                      {{ $peminjaman->links() }}
                  </div>
                  </div>
                </div>
              </div>
        </div>

        <!-- modal Tamnbah Data -->
        <div class="modal fade" id="modal-block-vcenter-extra-large-tambah" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true" data-bs-focus="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="block block-rounded block-transparent mb-0">
                  <div class="block-header block-header-default">
                    <h3 class="block-title">Modal Tambah peminjaman</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-fw fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content fs-sm">
                    <form class="space-y-4" action="{{ route('petugas.simpan_peminjaman') }}" method="POST">
                      @csrf
                      <div class="row">
                        <div class="mb-4">
                            <label for="barcode" class="form-label">Scan Barcode <span class="text-danger">*</span></label>
                            <input type="text" name="barcode" id="barcode" class="form-control" autofocus required>
                            <small class="text-muted">Arahkan scanner ke barcode buku, lalu tekan scan.</small>
                        </div>
                        <div class="mb-4">
                            <label for="siswa_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                            {{-- <select id="siswa_id" name="siswa_id" class="form-select select-search instructor" required>
                               <option value="" selected disabled>Pilih Siswa</option>
                                @foreach ($siswa as $s)
                                    <option value="{{ $s->id }}">{{ $s->nisn }} - {{ $s->nama }}</option>
                                @endforeach
                            </select> --}}

                            <input type="text" id="search" class="form-control" placeholder="Ketik nama atau NISN...">
                            <ul id="result" class="list-group mt-2"></ul>
                    
                            <!-- Hidden untuk simpan ID siswa -->
                            <input type="hidden" id="siswa_id" name="siswa_id">
                            
                        </div>
                        <div class="mb-4">
                            <label for="tanggal_kembali" class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control" required>
                        </div>  
                      </div>
                </div>
                <div class="block-content block-content-full text-end bg-body">
                  <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Close</button>
                  <button type="submit" id="btn-register" class="btn btn-sm btn-primary"">Tambah</button>
                </div>
              </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- modal Export Data -->
      <div class="modal fade" id="modal-block-vcenter-extra-large-export" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true" data-bs-focus="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="block block-rounded block-transparent mb-0">
              <div class="block-header block-header-default">
                <h3 class="block-title">Modal Export Peminjaman</h3>
                <div class="block-options">
                  <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-fw fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="block-content fs-sm">
                <form class="space-y-4" action="{{ route('petugas.export') }}" method="POST">
                  @csrf
                  <div class="row">
                    <!-- Hari Ini -->
                    <div class="mb-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="todayCheck" name="todayCheck">
                        <label class="form-check-label" for="todayCheck">Hari Ini</label>
                      </div>
                    </div>

                    <!-- Range Tanggal -->
                    <div id="dateRange">
                      <div class="mb-3">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" name="start_date" id="start_date">
                      </div>
                      <div class="mb-3">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="end_date" id="end_date">
                      </div>
                    </div>
            
                    <!-- Pilih Status -->
                    <div class="mb-3">
                      <label for="status" class="form-label">Status</label>
                      <select class="form-select" id="status" name="status" required>
                        <option selected="">Pilih Status Peminjaman</option>
                        <option value="Semua">Semua</option>
                        <option value="Dipinjam">Dipinjam</option>
                        <option value="Dikembalikan">Dikembalikan</option>
                      </select>
                    </div>
                  </div>
                  </div>
            </div>
            <div class="block-content block-content-full text-end bg-body">
              <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="btn-register" class="btn btn-sm btn-primary"">Export Data</button>
            </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- modal filter tanggal -->
  <div class="modal fade" id="modal-block-vcenter-extra-large-filterTanggal" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="block block-rounded block-transparent mb-0">
          <div class="block-header block-header-default">
            <h3 class="block-title">Modal Filter Tanggal Peminjaman</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                <i class="fa fa-fw fa-times"></i>
              </button>
            </div>
          </div>
          <div class="block-content fs-sm">
            <form class="space-y-4" action="{{ route('petugas.peminjaman') }}" method="GET">
              @csrf
              <div class="row">
                <!-- Range Tanggal -->
                <div id="dateRange">
                  <div class="mb-3">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="start_date" id="start_date">
                  </div>
                  <div class="mb-3">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="end_date" id="end_date">
                  </div>
                </div>
              </div>
              </div>
        </div>
        <div class="block-content block-content-full text-end bg-body">
          <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Close</button>
          <button type="submit" id="btn-register" class="btn btn-sm btn-primary"">Filter</button>
        </div>
      </form>
      </div>
    </div>
  </div>
</div>
</div>

<div id="overlay"></div>
<input type="text" id="search" placeholder="Ketik nama atau NIM..." autocomplete="off">
    <ul id="result"></ul>

    <input type="hidden" id="mahasiswa_id" name="mahasiswa_id">
    </main>
    @include('petugas.layouts.footer')
</div>
      <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/select2/js/select2.full.min.js') }}""></script>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script>
        document.getElementById('modal-block-vcenter-extra-large-tambah')
            .addEventListener('shown.bs.modal', function () {
                document.getElementById('barcode').focus();
            });

            function confirmationHapusData(url) {
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: 'Data yang dipilih akan dihapus dari sistem!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#B22222',
                cancelButtonColor: '#A9A9A9',
                confirmButtonText: 'Ya',
                closeOnConfirm: false
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        }

        // fungsi hitung check digit EAN-13
        function calculateEAN13CheckDigit(code) {
            if (code.length !== 12) return null;
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                let digit = parseInt(code[i], 10);
                sum += (i % 2 === 0) ? digit : digit * 3;
            }
            return (10 - (sum % 10)) % 10;
        }
    
        // handle input barcode di modal tambah peminjaman
        document.getElementById('barcode').addEventListener('change', function() {
            let barcode = this.value.trim();
    
            if (barcode.length === 12) {
                let checkDigit = calculateEAN13CheckDigit(barcode);
                barcode = barcode + checkDigit;
                this.value = barcode; // update input jadi 13 digit
            }
        });

        const todayCheck = document.getElementById("todayCheck");
        const dateRange = document.getElementById("dateRange");

        todayCheck.addEventListener("change", () => {
          const disabled = todayCheck.checked;
          dateRange.querySelectorAll("input").forEach(input => {
            input.disabled = disabled;
          });
        });

        document.querySelector("#modal-block-vcenter-extra-large-export form").addEventListener("submit", function(e) {
          const todayCheck = document.getElementById("todayCheck").checked;
          const startDate = document.getElementById("start_date").value;
          const endDate = document.getElementById("end_date").value;

          if (!todayCheck && (!startDate || !endDate)) {
            e.preventDefault(); // cegah form submit
            Swal.fire({
              icon: 'warning',
              title: 'Oops...',
              text: 'Pilih Hari Ini atau isi Range Tanggal terlebih dahulu!',
              confirmButtonText: 'OK'
            });
          }
        });

        $(document).ready(function(){

// Live search AJAX
$('#search').on('keyup', function(){
    let query = $(this).val();

    if(query.length > 1){
        $.ajax({
            url: "{{ route('petugas.siswa.search') }}",
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

// Klik salah satu siswa
$(document).on('click', '#result li', function(){
    let id = $(this).data('id');
    let text = $(this).text();

    $('#search').val(text);
    $('#siswa_id').val(id);
    $('#result').empty();
});

// Tombol konfirmasi pilih
$('#btnPilih').on('click', function(){
    let siswaText = $('#search').val();
    let siswaId   = $('#siswa_id').val();

    if(siswaId){
        $('#siswa_terpilih').val(siswaText); 
        $('#siswa_terpilih_id').val(siswaId);

        $('#siswaModal').modal('hide'); // tutup modal
    } else {
        alert("Silakan pilih siswa dulu!");
    }
});

});
        </script>
</div>
@endsection