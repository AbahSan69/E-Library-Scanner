@extends('admin.layouts.main')
@section('content')
<div id="page-container" class="page-header-dark main-content-boxed">
    @include('admin.layouts.header')
    
    <main id="main-container">

      <div class="bg-primary-darker">
        <div class="content py-3">
          <div class="d-lg-none">
            <button type="button" class="btn w-100 btn-alt-secondary d-flex justify-content-between align-items-center" data-toggle="class-toggle" data-target="#main-navigation" data-class="d-none">
              Menu
              <i class="fa fa-bars"></i>
            </button>
          </div>
          @include('admin.layouts.navbar')
          <div class="content">
            @if (session('import_errors'))
    <div class="alert alert-danger">
        <strong>Detail kesalahan import:</strong>
        <ul class="mb-0 mt-2">
            @foreach (session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.siswa') }}" method="GET" class="mb-3">
  <div class="row g-2">
      <div class="col-md-5">
          <input type="text" class="form-control"
                 name="q"
                 placeholder="Cari Nama / NISN"
                 value="{{ request('q') }}">
      </div>

      <div class="col-md-4">
          <select name="kelas" class="form-select">
              <option value="">-- Semua Kelas --</option>
              @foreach($kelasList as $kelas)
                  <option value="{{ $kelas }}"
                      {{ request('kelas') == $kelas ? 'selected' : '' }}>
                      {{ $kelas }}
                  </option>
              @endforeach
          </select>
      </div>

      <div class="col-md-3 d-grid">
          <button class="btn btn-primary" type="submit">
              <i class="fa fa-fw fa-search me-1"></i> Filter
          </button>
      </div>
  </div>
</form>

            <div class="block block-rounded">
                <div class="block-header block-header-default">
                  <h3 class="block-title">List Siswa (Total : {{ $total_siswa }}) </h3>
                  <div class="block-options">
                    <button type="button" class="btn btn-sm btn-alt-primary js-bs-tooltip-enabled me-1" title="Tambah Data" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-tambah">
                        <i class="fa fa-fw fa-plus"></i>
                        <span>Tambah Data</span>
                      </button>
                      <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-sm btn-alt-success space-x-1" 
                                id="dropdown-analytics-overview" 
                                data-bs-toggle="dropdown" 
                                aria-haspopup="true" 
                                aria-expanded="false">
                          <i class="fa fa-fw fa-file-excel"></i>
                          <span>Import</span>
                          <i class="fa fa-fw fa-angle-down"></i>
                        </button>
                      
                        <div class="dropdown-menu dropdown-menu-end fs-sm" 
                             aria-labelledby="dropdown-analytics-overview">
                          <a class="dropdown-item fw-medium" href="{{ route('admin.download_format_siswa') }}"><i class="fa fa-fw fa-download"></i> Download Format</a>
                          <form action="{{ route('admin.import_siswa') }}" method="POST" enctype="multipart/form-data" id="importForm" class="d-inline">
                            @csrf
                            <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                            <button type="button" class="dropdown-item fw-medium" title="Import Data" onclick="document.getElementById('fileInput').click();">
                              <i class="fa fa-fw fa-file-excel"></i>
                              <span>Import Data</span>
                            </button>
                          </form>
                        </div>

                        <button type="button" id="downloadSelectedBtn" class="btn btn-sm btn-primary shadow-sm border">
                          <i class="fa fa-download me-1"></i> Download Barcode Terpilih
                      </button>

                      {{-- <a href="{{ route('admin.barcode.batch.progress') }}" 
   class="btn btn-sm btn-primary shadow-sm border">
    <i class="fa fa-download me-1"></i> Download Semua Barcode
</a> --}}

                      
                      </div>
                      
                      {{-- <form action="{{ route('admin.import_siswa') }}" method="POST" enctype="multipart/form-data" id="importForm" class="d-inline">
                        @csrf
                        <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                        <button type="button" class="btn btn-sm btn-alt-success" title="Import Data" onclick="document.getElementById('fileInput').click();">
                          <i class="fa fa-fw fa-file-excel"></i>
                          <span>Import Data</span>
                        </button>
                      </form> --}}
                  </div>
                </div>
                <div class="block-content">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">
                              <input type="checkbox" id="checkAll">
                            </th>
                            <th class="text-center" style="width: 70px;">No</th>
                            <th style="width: 120px;">NISN</th>
                            <th>Nama Siswa</th>
                            {{-- <th>BarCode</th> --}}
                            <th style="width: 100px;">Status</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                        </thead>                        
                      @if ($siswa->count() > 0)
                      @php
                      $no = 0;
                    @endphp
                    @foreach ($siswa as $list_siswa)
                    {{-- @php
                      $qrPng = DNS2D::getBarcodePNG($list_siswa->nisn, 'QRCODE');
                      $nisn = $list_siswa->nisn;

                      $barcodePng = (new \Milon\Barcode\DNS1D())->getBarcodePNG($list_siswa->nisn, 'C128');
                    @endphp --}}
                    <tbody>
                      <tr>
                        <td class="text-center">
                          <input type="checkbox" class="checkItem" value="{{ $list_siswa->id }}">
                      </td>
                        <td class="text-center">
                          {{ $no = $no + 1 }}
                        </td>
                        <td class="fw-semibold fs-sm">
                            {{ $list_siswa->nisn }}
                        </td>
                        <td class="fw-semibold fs-sm">
                            {{ $list_siswa->nama }}
                        </td>
                        {{-- <td class="qr-cell">
                          <img 
                            src="data:image/png;base64,{{ $qrPng }}" 
                            class="qr-image" 
                            data-nisn="{{ $list_siswa->nisn }}"
                            style="width: 80px; height: 80px;"
                          >
                          <div style="width:260px; text-align:center; background:white; padding:8px; border-radius:6px;">

                            <!-- Nama Sekolah di atas -->
                            <div style="font-size:16px; font-weight:600; margin-bottom:6px;">
                              SMAN 1 CITEUREUP
                            </div>

                            <!-- Barcode -->
                            <img 
                              src="data:image/png;base64,{{ $barcodePng }}"
                              class="barcode-image"
                              style="width:180px; display:block; margin:4px auto;"
                            >

                            <!-- NISN di bawah -->
                            <div style="font-size:14px; margin-top:2px;">
                              {{ $list_siswa->nisn }}
                            </div>

                          </div> --}}

                      </td>                                       
                        <td class="fw-semibold fs-sm">
                          @if ($list_siswa->status == 'Aktif')
                              <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success">
                                  {{ $list_siswa->status }}
                              </span>
                          @elseif ($list_siswa->status == 'Tidak Aktif')
                              <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-warning-light text-warning">
                                  {{ $list_siswa->status }}
                              </span>
                          @else
                              <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-secondary-light text-secondary">
                                  {{ $list_siswa->status }}
                              </span>
                          @endif
                      </td>                        
                        <td class="text-center">
                          <div class="btn-group">
                            {{-- <button class="btn btn-sm btn-primary download-qr me-2"
                                        data-nisn="{{ $list_siswa->nisn }}">
                                    <i class="fa fa-download"></i>
                                </button> --}}

                                <button class="btn btn-sm btn-primary download-barcode me-2"
    data-id="{{ $list_siswa->id }}">
    <i class="fa fa-download"></i>
</button>



                            <button type="button" class="btn btn-sm btn-alt-info js-bs-tooltip-enabled me-2" title="Detail" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-detail-{{ $list_siswa->id }}">
                              <i class="fa fa-fw fa-info"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-alt-warning js-bs-tooltip-enabled me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-tambah-{{ $list_siswa->id }}">
                              <i class="fa fa-fw fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-alt-danger js-bs-tooltip-enabled" data-bs-toggle="tooltip" aria-label="Delete" data-bs-original-title="Delete" title="Hapus" onclick="confirmationHapusData('{{ route('admin.hapus_siswa', $list_siswa->id) }}')">
                              <i class="fa fa-fw fa-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>

                    <!-- Modal Detail Data -->
                    <div class="modal fade" id="modal-block-vcenter-extra-large-Detail-{{ $list_siswa->id }}" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="block block-rounded block-transparent mb-0">
                            <div class="block-header block-header-default">
                              <h3 class="block-title">Modal Detail Siswa</h3>
                              <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                  <i class="fa fa-fw fa-times"></i>
                                </button>
                              </div>
                            </div>
                          <div class="block-content fs-sm">
                            <div class="row">
                              <div class="mb-2"><strong>NISN:</strong> {{ $list_siswa->nisn }} </div>
                                  <div class="mb-2"><strong>Nama:</strong> {{ $list_siswa->nama }} </div>
                                  <div class="mb-2"><strong>Kelas:</strong> {{ $list_siswa->kelas }} </div>
                                  <div class="mb-2"><strong>Jenis Kelamin:</strong> {{ $list_siswa->jenis_kelamin }} </div>
                                  <div class="mb-2"><strong>Tanggal Lahir:</strong> {{ $list_siswa->tanggal_lahir }} </div>
                                  <div class="mb-2"><strong>Alamat:</strong> {{ $list_siswa->alamat }} </div>
                                  <div class="mb-2"><strong>No Handphone / WhatsApp:</strong> {{ $list_siswa->no_hp }} </div>
                                  <div class="mb-2"><strong>Status:</strong> {{ $list_siswa->status }} </div>
                            </div>
                            </div>
                            <div class="block-content block-content-full text-end bg-body">
                              <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Close</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- modal Edit Data -->
                    <div class="modal fade" id="modal-block-vcenter-extra-large-tambah-{{ $list_siswa->id }}" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="block block-rounded block-transparent mb-0">
                            <div class="block-header block-header-default">
                              <h3 class="block-title">Modal Edit Admin</h3>
                              <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                  <i class="fa fa-fw fa-times"></i>
                                </button>
                              </div>
                            </div>
                          <div class="block-content fs-sm">
                          <form class="space-y-4" action="{{ route('admin.edit_siswa') }}" method="POST">
                          @csrf
                            <input type="hidden" name="id" id="id" value="{{ $list_siswa->id }}">
                              <div class="row">
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-nama">NISN <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="nisn" name="nisn"  placeholder="Masukan nisn ..." value="{{ $list_siswa->nisn }}" required>
                                </div>
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-nama">Nama <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="nama" name="nama"  placeholder="Masukan nama ..." value="{{ $list_siswa->nama }}" required>
                                </div>
                                <div class="mb-4">
                                  <label class="col-sm-4 col-form-label" for="example-hf-kelas">Kelas <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="kelas" name="kelas"  placeholder="Masukan kelas ..." value="{{ $list_siswa->kelas }}" required>
                                </div>
                                <div class="mb-4">
                                  <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                                  <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="laki" name="jenis_kelamin" value="L"
                                      {{ old('jenis_kelamin', $list_siswa->jenis_kelamin) == 'L' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="laki">Laki-laki</label>
                                  </div>
                        
                                  <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="perempuan" name="jenis_kelamin" value="P"
                                      {{ old('jenis_kelamin', $list_siswa->jenis_kelamin) == 'P' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="perempuan">Perempuan</label>
                                  </div>
                                </div>
                        
                                <div class="mb-4">
                                  <label for="tanggal-lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                  <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="{{ $list_siswa->tanggal_lahir }}" required>
                                </div>
                                <div class="mb-4">
                                  <label class="form-label" for="val-suggestions">Alamat</span></label>
                                  <textarea class="form-control" id="alamat" name="alamat" rows="5" placeholder="Alamat ...">{{ $list_siswa->alamat }}</textarea>
                                </div>
                                <div class="mb-4">
                                  <label for="nomor-handphone" class="form-label">Nomor Handphone <span class="text-danger">*</span></label>
                                  <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="Masukkan Nomor Handphone" maxlength="15" value="{{ $list_siswa->no_hp }}">
                                  <small class="form-text text-muted">Hanya masukkan angka, maksimal 15 digit.</small>
                                </div>
                                <div class="mb-4">
                                  <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                  <select id="status" name="status" class="form-select select-search instructor" required>
                                    <option value="" selected disabled>Pilih Status</option>
                                    <option value="Aktif" {{ old('status', $list_siswa->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Tidak Aktif" {{ old('status', $list_siswa->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                  </select>
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
                        <td colspan="5" class="text-center text-danger fw-semibold fs-sm">Siswa kosong atau tidak ditemukan</td>
                      </tr>
                      @endif
                    </table>
                    <div class="mt-3">
                      @if ($siswa instanceof \Illuminate\Pagination\LengthAwarePaginator)
    {{ $siswa->links() }}
@endif

                  </div>
                  </div>
                </div>
              </div>
        </div>

        <!-- modal Tamnbah Data -->
        <div class="modal fade" id="modal-block-vcenter-extra-large-tambah" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="block block-rounded block-transparent mb-0">
                  <div class="block-header block-header-default">
                    <h3 class="block-title">Modal Tambah Siswa</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-fw fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content fs-sm">
                    <form class="space-y-4" action="{{ route('admin.simpan_siswa') }}" method="POST">
                      @csrf
                      <div class="row">
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-nama">NISN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nisn" name="nisn"  placeholder="Masukan NISN ..." required>
                          </div>
                        <div class="mb-4">
                          <label class="col-sm-4 col-form-label" for="example-hf-nama">Nama <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="nama" name="nama"  placeholder="Masukan nama ..." required>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-nama">Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kelas" name="kelas"  placeholder="Masukan kelas ..." required>
                          </div>
                          <div class="mb-4">
                            <label class="col-form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            
                            <div class="form-check">
                              <input class="form-check-input" type="radio" id="laki" name="jenis_kelamin" value="L" required>
                              <label class="form-check-label" for="laki">Laki-laki</label>
                            </div>
                          
                            <div class="form-check">
                              <input class="form-check-input" type="radio" id="perempuan" name="jenis_kelamin" value="P" required>
                              <label class="form-check-label" for="perempuan">Perempuan</label>
                            </div>
                          </div>
                          
                          <div class="mb-4">
                            <label for="tanggal_lahir" class="col-form-label">
                              Tanggal Lahir <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="tanggal_lahir" 
                                   name="tanggal_lahir" 
                                   max="{{ date('Y-m-d') }}" 
                                   required>
                          </div>
                          
                          <div class="mb-4">
                            <label class="form-label" for="val-suggestions">Alamat</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="5" placeholder="Deskripsi alamat"></textarea>
                          </div>
                          <div class="mb-4">
                            <label for="nomor-handphone" class="form-label">Nomor Handphone <span class="text-danger">*</span></label>
                            <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="Masukkan Nomor Handphone" maxlength="15">
                            <small class="form-text text-muted">Hanya masukkan angka, maksimal 15 digit.</small>
                          </div>
                        {{-- <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select select-search instructor" required>
                              <option value="" selected disabled>Pilih Status</option>
                              <option value="Aktif">Aktif</option>
                              <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                          </div> --}}
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

    </main>
    @include('admin.layouts.footer')
</div>
      <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/select2/js/select2.full.min.js') }}"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
      <script>
      document.getElementById('fileInput').addEventListener('change', function() {
          if(this.files.length > 0){
            document.getElementById('importForm').submit();
          }
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
      </script>
      <script>
        document.getElementById('fileInput').addEventListener('change', function(){
  if (this.files.length > 0) {
    document.getElementById('importForm').submit();
  }
});

// Download QR Satuan
// $(document).on("click", ".download-qr", function () {

//     let row = $(this).closest("tr");
//     let img = row.find(".qr-image")[0];
//     let nisn = $(this).data("nisn");

//     if (!img) return alert("QR tidak ditemukan!");

//     let a = document.createElement("a");
//     a.href = img.src;
//     a.download = `QR_${nisn}.png`;
//     a.click();
// });


// Download Bulk ZIP QR
// $("#downloadSelectedBtn").on("click", async function () {

//     let selected = $(".checkItem:checked");
//     if (selected.length < 1) {
//         return Swal.fire("Oops!", "Pilih minimal satu siswa!", "warning");
//     }

//     let zip = new JSZip();

//     for (let cb of selected) {
//         let row = $(cb).closest("tr");
//         let nisn = row.find("td:nth-child(3)").text().trim();
//         let img = row.find(".qr-image")[0];

//         if (!img) continue;

//         let base64 = img.src.split(",")[1];

//         zip.file(`QR_${nisn}.png`, base64, { base64: true });
//     }

//     zip.generateAsync({ type: "blob" }).then(output => {
//         saveAs(output, "QR_TERPILIH.zip");
//     });
// });

$(document).on("click", ".download-barcode", function () {
    let id = $(this).data("id");
    window.location.href = "{{ url('admin/barcode-siswa') }}/" + id;
});


$("#downloadSelectedBtn").on("click", function () {
    let selected = $(".checkItem:checked");

    if (selected.length < 1) {
        return Swal.fire("Oops!", "Pilih minimal satu siswa!", "warning");
    }

    let form = $('<form>', {
        method: 'POST',
        action: '/admin/barcode-siswa-bulk'
    });

    // csrf_token Laravel
    form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');

    selected.each(function () {
        form.append(
            $('<input>', {
                type: 'hidden',
                name: 'selected[]',
                value: $(this).val()
            })
        );
    });

    $('body').append(form);
    form.submit();
});

// Check All
$("#checkAll").on("change", function () {
    $(".checkItem").prop("checked", $(this).prop("checked"));
});

      </script>
</div>
@endsection