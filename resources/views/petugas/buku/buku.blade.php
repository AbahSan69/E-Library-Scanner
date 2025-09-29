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
                      <form action="{{ route('petugas.import') }}" method="POST" enctype="multipart/form-data" id="importForm" class="d-inline">
                        @csrf
                        <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                        <button type="button" class="btn btn-sm btn-alt-success" title="Import Data" onclick="document.getElementById('fileInput').click();">
                          <i class="fa fa-fw fa-file-excel"></i>
                          <span>Import Data</span>
                        </button>
                      </form>
                  </div>
                </div>
                <div class="block-content">
                  <div class="table-responsive">
                    <table class="table table-vcenter">
                      <thead>
                        <tr class="text-center">
                          <th style="width: 100px;">
                            No
                          </th>
                          <th style="width: 100px;">No Barcode</th>
                          <th>Judul</th>
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
                                {{ $no = $no + 1 }}
                              </td>
                              <td class="fw-semibold fs-sm">
                                {{ $list_buku->no_barcode }}
                              </td>
                              <td class="fw-semibold fs-sm">
                                {{ $list_buku->judul}}
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
                                  <a href="{{ route('petugas.cetak', $list_buku->id) }}" type="button" class="btn btn-sm btn-alt-success js-bs-tooltip-enabled me-2" title="Print Barcode" target="_blank">
                                    <i class="fa fa-fw fa-print"></i>
                                  </a>
                                  {{-- <a href="{{ route('cetakPdf', $list_buku->id) }}" type="button" class="btn btn-sm btn-alt-primary js-bs-tooltip-enabled me-2" title="Download Barcode">
                                    <i class="fa fa-fw fa-download"></i>
                                  </a> --}}
                                  {{-- <a href="data:image/png;base64,{{ DNS1D::getBarcodePNG($list_buku->no_barcode, 'EAN13', 2, 50, [0,0,0], true) }}"
                                    download="barcode_{{ $list_buku->no_barcode }}.png"
                                    type="button" class="btn btn-sm btn-alt-primary js-bs-tooltip-enabled me-2" title="Download Barcode">
                                    <i class="fa fa-fw fa-download"></i>
                                  </a> --}}
                                  <a href="{{ route('petugas.downloadBarcode', $list_buku->no_barcode) }}"
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
                                        {{-- <div class="border rounded p-2">
                                          <div><strong>QR Code</strong></div>
                                          <img src="data:image/png;base64,{{ 
                                            DNS2D::getBarcodePNG(
                                            'ISBN: '.$list_buku->isbn."\n".
                                            'Judul: '.$list_buku->judul."\n".
                                            'Pengarang: '.$list_buku->pengarang."\n".
                                            'Penerbit: '.$list_buku->penerbit."\n".
                                            'Tahun Terbit: '.$list_buku->tahun_terbit."\n".
                                            'Lokasi Rak: '.$list_buku->lokasi_rak."\n".
                                            'Status: '.$list_buku->status,
                                            'QRCODE',
                                            3, 3
                                            ) 
                                          }}" class="img-fluid" alt="qrcode">
                                        </div> --}}
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
                                        <input type="text" class="form-control" id="pengarang" name="pengarang"  placeholder="Masukan pengarang ..." value="{{ $list_buku->pengarang }}" required>
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-penerbit">Penerbit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="penerbit" name="penerbit"  placeholder="Masukan penerbit ..." value="{{ $list_buku->penerbit }}" required>
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-tahun_terbit">Tahun Terbit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit"  placeholder="Masukan tahun_terbit ..." value="{{ $list_buku->tahun_terbit }}" required>
                                      </div>
                                      <div class="mb-4">
                                        <label class="col-sm-4 col-form-label" for="example-hf-lokasi_rak">Lokasi Rak <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak"  placeholder="Masukan lokasi rak ..." value="{{ $list_buku->lokasi_rak }}" required>
                                      </div>
                                      <div class="mb-4">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select id="status" name="status" 
                                          class="form-select select-search instructor" 
                                          {{ (old('status', $list_buku->status ?? '') == 'Dipinjam') ? 'disabled' : '' }} 
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
                            <label class="col-sm-4 col-form-label" for="example-hf-pengarang">Pengarang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pengarang" name="pengarang"  placeholder="Masukan pengarang ..." required>
                          </div>
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-penerbit">Penerbit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="penerbit" name="penerbit"  placeholder="Masukan penerbit ..." required>
                          </div>
                          
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-tahun_terbit">Tahun Terbit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit"  placeholder="Masukan tahun_terbit ..." required>
                          </div>
                          
                          <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-lokasi_rak">Lokasi Rak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak"  placeholder="Masukan lokasi rak ..." required>
                          </div>
                        <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select select-search instructor" required>
                              <option value="" selected disabled>Pilih Status</option>
                              <option value="Tersedia">Tersedia</option>
                              <option value="Dipinjam">Dipinjam</option>
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
      </div>

    </main>
    @include('petugas.layouts.footer')
</div>
      {{-- <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/select2/js/select2.full.min.js') }}""></script>
      <script src="{{ asset('assets/oneui/s/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/jquery-validation/additional-methods.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/pages/be_forms_validation.min.js') }}"></script> --}}
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
</div>
@endsection