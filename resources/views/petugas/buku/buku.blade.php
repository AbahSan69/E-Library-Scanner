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
            <form action="{{ route('petugas.buku') }}" method="GET">
                <div class="input-group">
                  <input type="text" class="form-control" name="q" placeholder="Cari Buku" value="{{ request()->q }}">
                  <!-- Search Icon as Submit Button -->
                  <button class="input-group-text btn btn-primary" type="submit">
                    <i class="fa fa-fw fa-search"></i>
                  </button>
                </div>
            </form>
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                  <h3 class="block-title">List Buku (Total : {{ $total_buku }}) </h3>
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
                          <a class="dropdown-item fw-medium" href="{{ route('petugas.format_buku') }}"><i class="fa fa-fw fa-download"></i> Download Format</a>
                          <form action="{{ route('petugas.import') }}" method="POST" enctype="multipart/form-data" id="importForm" class="d-inline">
                            @csrf
                            <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                            <button type="button" class="dropdown-item fw-medium" title="Import Data" onclick="document.getElementById('fileInput').click();">
                              <i class="fa fa-fw fa-file-excel"></i>
                              <span>Import Data</span>
                            </button>
                          </form>
                        </div>
                      </div>
                      {{-- <form action="{{ route('petugas.import') }}" method="POST" enctype="multipart/form-data" id="importForm" class="d-inline">
                        @csrf
                        <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv">
                        <button type="button" class="btn btn-sm btn-alt-success space-x-1" title="Import Data" onclick="document.getElementById('fileInput').click();">
                          <i class="fa fa-fw fa-file-excel"></i>
                        </button>
                      </form> --}}
                      <form id="bulkDownloadForm" action="{{ route('petugas.downloadBulkBarcode') }}" method="POST" target="_blank" class="d-inline">
                        @csrf
                        <button type="submit" id="downloadBtn" class="btn btn-sm btn-primary" title="Download Barcode Terpilih" disabled>
                          <i class="fa fa-fw fa-download"></i>
                        </button>
                        <span id="selectedCount" class="ms-2 text-muted fw-semibold fs-sm"></span>
                      </form>
                  </div>
                </div>
                <div class="block-content">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr class="text-center">
                          <th style="width: 100px"><input type="checkbox" id="select-all"><br><small>Pilih Barcode</small></th>
                          <th style="width: 100px;">
                            No
                          </th>
                          <th style="width: 100px;">No Barcode</th>
                          <th>Judul</th>
                          <th>Stok</th>
                          <th style="width: 100px;">Status</th>
                          <th style="width: 100px;">Aksi</th>
                        </tr>
                      </thead>

                      @if ($buku->count() > 0)
                        @php
                        $no = 0;
                        @endphp
                        @foreach ($buku as $list_buku)
                          <tbody>
                            <tr>
                              <td class="text-center">
                                <input type="checkbox" name="selected[]" value="{{ $list_buku->id }}" class="checkbox-buku">
                            </td>
                              <td class="fw-semibold fs-sm text-center">
                                {{ $no = $no + 1 }}
                              </td>
                              <td class="fw-semibold fs-sm">
                                {{ $list_buku->no_barcode }}
                              </td>
                              <td class="fw-semibold fs-sm">
                                {{ $list_buku->judul}}
                              </td>
                              <td class="fw-semibold fs-sm">
                                {{ $list_buku->stok}}
                              </td>
                              <td class="fw-semibold fs-sm">
                                @if ($list_buku->status == 'Dipinjam')
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success">
                                    {{ $list_buku->status }}
                                  </span>
                                @elseif ($list_buku->status == 'Tersedia')
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-info-light text-primary">
                                    {{ $list_buku->status }}
                                  </span>
                                @else
                                  <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-secondary-light text-secondary">
                                    {{ $list_buku->status }}
                                  </span>
                                @endif
                              </td> 
                              <td class="text-center">
                                <div class="btn-group">
                                  <button type="button" class="btn btn-sm btn-alt-info js-bs-tooltip-enabled me-2" title="Detail" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-detail-{{ $list_buku->id }}">
                                    <i class="fa fa-fw fa-info"></i>
                                  </button>

                                 <a href="{{ route('petugas.downloadBarcodeSatuan', $list_buku->id) }}"
                                    class="btn btn-sm btn-alt-primary js-bs-tooltip-enabled me-2"
                                    title="Download Barcode">
                                    <i class="fa fa-fw fa-download"></i>
                                 </a>
                                                              
                                  <button type="button" class="btn btn-sm btn-alt-warning js-bs-tooltip-enabled me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-tambah-{{ $list_buku->id }}">
                                    <i class="fa fa-fw fa-pencil"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm btn-alt-danger js-bs-tooltip-enabled" data-bs-toggle="tooltip" aria-label="Delete" data-bs-original-title="Delete" title="Hapus" onclick="confirmationHapusData('{{ route('petugas.hapus_buku', $list_buku->id) }}')">
                                    <i class="fa fa-fw fa-trash"></i>
                                  </button>
                                </div>
                              </td>
                            </tr>
                          </tbody>

                          <!-- Modal Detail Data -->
                          <div class="modal fade" id="modal-block-vcenter-extra-large-detail-{{ $list_buku->id }}" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                              <div class="modal-content">
                                <div class="block block-rounded block-transparent mb-0">
                                  <div class="block-header block-header-default">
                                    <h3 class="block-title">Modal Detail Buku</h3>
                                    <div class="block-options">
                                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                        <i class="fa fa-fw fa-times"></i>
                                      </button>
                                    </div>
                                  </div>
                                  <div class="block-content fs-sm mb-2">
                                    <div class="row g-3">
                                      <!-- Kiri -->
                                      <div class="col-12 col-md-7">
                                        <div class="mb-2"><strong>ISBN:</strong>
                                          @if($list_buku->isbn)
                                            {{ $list_buku->isbn }} 
                                          @else
                                            <span class="badge bg-warning">Tanpa ISBN</span>
                                          @endif
                                        </div>
                                        <div class="mb-2"><strong>Judul:</strong> {{ $list_buku->judul }} </div>
                                        <div class="mb-2"><strong>Pengarang:</strong> {{ $list_buku->pengarang }} </div>
                                        <div class="mb-2"><strong>Penerbit:</strong> {{ $list_buku->penerbit }} </div>
                                        <div class="mb-2"><strong>Tahun Terbit:</strong> {{ $list_buku->tahun_terbit }} </div>
                                        <div class="mb-2"><strong>Lokasi Rak:</strong> {{ $list_buku->lokasi_rak }} </div>
                                        <div class="mb-2"><strong>Status:</strong> {{ $list_buku->status }} </div>
                                      </div>
                              
                                      <!-- Kanan -->
                                      <div class="col-12 col-md-5 text-center d-flex justify-content-center align-items-center">
                                        <div class="border rounded p-2 mb-3">
                                          <div><strong>Barcode</strong></div>
                                          <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($list_buku->no_barcode, 'EAN13', 2, 50, [0,0,0], true) }}" class="img-fluid" alt="barcode"><br>
                                          <small id="detail-barcode"></small>
                                        </div>
                                      </div>
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
                          <div class="modal fade" id="modal-block-vcenter-extra-large-tambah-{{ $list_buku->id }}" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                              <div class="modal-content">
                                <div class="block block-rounded block-transparent mb-0">
                                  <div class="block-header block-header-default">
                                    <h3 class="block-title">Modal Edit Buku</h3>
                                    <div class="block-options">
                                    <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                      <i class="fa fa-fw fa-times"></i>
                                    </button>
                                  </div>
                                </div>
                                <div class="block-content fs-sm">
                                  <form class="space-y-4" action="{{ route('petugas.edit_buku') }}" method="POST">
                                  @csrf
                                    <input type="hidden" name="id" id="id" value="{{ $list_buku->id }}">
                                    <div class="row">
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-isbn">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn"  placeholder="Masukan isbn ..." value="{{ $list_buku->isbn }}">
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-judul">Judul <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="judul" name="judul"  placeholder="Masukan judul ..." value="{{ $list_buku->judul }}" required>
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-pengarang">Pengarang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pengarang" name="pengarang"  placeholder="Masukan pengarang ..." value="{{ $list_buku->pengarang }}">
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-penerbit">Penerbit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="penerbit" name="penerbit"  placeholder="Masukan penerbit ..." value="{{ $list_buku->penerbit }}">
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-tahun_terbit">Tahun Terbit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit"  placeholder="Masukan tahun_terbit ..." value="{{ $list_buku->tahun_terbit }}">
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-lokasi_rak">Lokasi Rak <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak"  placeholder="Masukan lokasi rak ..." value="{{ $list_buku->lokasi_rak }}">
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="stok">Stok <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stok" name="stok" placeholder="Masukan jumlah stok ..." required min="1" value="{{ $list_buku->stok }}">
                                      </div>
                                      <div class="mb-4">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select id="status" name="status" 
                                          class="form-select select-search instructor" 
                                          {{-- {{ (old('status', $list_buku->status ?? '') == 'Dipinjam') ? 'disabled' : '' }}  --}}
                                          required>
                                          <option value="" disabled>Pilih Status</option>
                                          <option value="Tersedia" {{ old('status', $list_buku->status ?? '') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                                          <option value="Dipinjam" {{ old('status', $list_buku->status ?? '') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
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

                        @endforeach
                      @else
                        <tr>
                          <td colspan="5" class="text-center text-danger fw-semibold fs-sm">Buku kosong atau tidak ditemukan</td>
                        </tr>
                      @endif
                    </table>
                    <div class="mt-3">
                      {{ $buku->links() }}
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
                    <h3 class="block-title">Modal Tambah Buku</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-fw fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content fs-sm">
                    <form class="space-y-4" action="{{ route('petugas.simpan_buku') }}" method="POST">
                      @csrf
                      <div class="row">
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-isbn">ISBN</span></label>
                            <input type="text" class="form-control" id="isbn" name="isbn"  placeholder="Masukan isbn ...">
                            <small class="form-text text-muted">
                              Jika buku tidak memiliki ISBN, biarkan kosong. Sistem akan membuat barcode otomatis.
                            </small>
                          </div>
                        <div class="mb-4">
                          <label class="col-sm-4 col-form-label" for="example-hf-judul">Judul <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="judul" name="judul"  placeholder="Masukan judul ..." required>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-pengarang">Pengarang </label>
                            <input type="text" class="form-control" id="pengarang" name="pengarang"  placeholder="Masukan pengarang ...">
                          </div>
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-penerbit">Penerbit </label>
                            <input type="text" class="form-control" id="penerbit" name="penerbit"  placeholder="Masukan penerbit ...">
                          </div>
                          
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-tahun_terbit">Tahun Terbit </label>
                            <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit"  placeholder="Masukan tahun_terbit ...">
                          </div>
                          
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-lokasi_rak">Lokasi Rak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak"  placeholder="Masukan lokasi rak ...">
                          </div>
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="stok">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stok" name="stok" value="1" placeholder="Masukan jumlah stok ..." required min="1">
                          </div>
            
                          <!-- Hidden field untuk status default -->
                          <input type="hidden" name="status" value="Tersedia">
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
    @include('petugas.layouts.footer')
</div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('fileInput');
  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        document.getElementById('importForm').submit();
      }
    });
  } else {
    console.error("fileInput belum ditemukan di DOM!");
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

        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.checkbox-buku');
        const downloadBtn = document.getElementById('downloadBtn');
        const selectedCount = document.getElementById('selectedCount');

        function getSelectedBooks() {
          return JSON.parse(localStorage.getItem('selectedBooks') || '[]');
        }

        function saveSelectedBooks(ids) {
          localStorage.setItem('selectedBooks', JSON.stringify(ids));
        }

        function toggleDownloadBtn() {
          let ids = getSelectedBooks();
          downloadBtn.disabled = ids.length === 0;
          selectedCount.textContent = ids.length > 0 ? ids.length + " buku dipilih" : "";
        }

        document.addEventListener('DOMContentLoaded', () => {
          let selected = getSelectedBooks();

          // tandai yang sudah pernah dipilih
          checkboxes.forEach(cb => {
            if (selected.includes(cb.value)) {
                cb.checked = true;
            }
            cb.addEventListener('change', () => {
                let ids = getSelectedBooks();
                if (cb.checked) {
                    if (!ids.includes(cb.value)) ids.push(cb.value);
                } else {
                    ids = ids.filter(id => id !== cb.value);
                    selectAll.checked = false;
                }
                saveSelectedBooks(ids);
                toggleDownloadBtn();
            });
          });

          // select all (halaman aktif)
          if (selectAll) {
            selectAll.addEventListener('change', function(e) {
                let ids = getSelectedBooks();
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    if (e.target.checked) {
                        if (!ids.includes(cb.value)) ids.push(cb.value);
                    } else {
                        ids = ids.filter(id => id !== cb.value);
                    }
                });
                saveSelectedBooks(ids);
                toggleDownloadBtn();
            });
          }

          toggleDownloadBtn();
        });

        // inject semua ID saat submit
        document.getElementById('bulkDownloadForm').addEventListener('submit', function(e) {
          let ids = getSelectedBooks();
          if (ids.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 buku!');
            return;
          }

          // hapus input hidden lama
          document.querySelectorAll('input[name="selected[]"][type="hidden"]').forEach(el => el.remove());

          // tambahkan input hidden
          ids.forEach(id => {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = id;
            this.appendChild(input);
          });

          // kalau mau kosongkan setelah submit:
          // localStorage.removeItem('selectedBooks');
        });
      </script>
</div>
@endsection