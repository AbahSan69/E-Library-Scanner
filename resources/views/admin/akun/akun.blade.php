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
            <form action="{{ route('admin.akun') }}" method="GET">
                <div class="input-group">
                  <input type="text" class="form-control" name="search" placeholder="Cari Akun" value="{{ request()->input('search') }}">
                  <!-- Search Icon as Submit Button -->
                  <button class="input-group-text btn btn-primary" type="submit">
                    <i class="fa fa-fw fa-search"></i>
                  </button>
                </div>
            </form>
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                  <h3 class="block-title">List Akun (Total : {{ $total_akun ?? 0 }}) </h3>
                  <div class="block-options">
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
                          <th>Username</th>
                          <th>Email</th>
                          <th style="width: 100px;">Status</th>
                          <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                      </thead>
                      @php
                        $no = 0;
                      @endphp
                      @foreach ($akun as $list_akun)
                      <tbody>
                                      <tr>
                            <td class="text-center">
                              {{ $no = $no + 1 }}
                            </td>
                            <td class="fw-semibold fs-sm">
                                {{ $list_akun->name }}
                              </td>
                            <td class="fw-semibold fs-sm">
                              {{ $list_akun->email }}
                            </td>
                            <td class="fw-semibold fs-sm">
                                <span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-info-light text-info">{{ $list_akun->status }}</span>
                            </td>
                            <td class="text-center">
                              <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-alt-warning js-bs-tooltip-enabled me-2" title="Edit" data-bs-toggle="modal" data-bs-target="#modal-block-vcenter-extra-large-tambah-{{ $list_akun->id }}">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-alt-danger js-bs-tooltip-enabled" data-bs-toggle="tooltip" aria-label="Delete" data-bs-original-title="Delete" title="Hapus" onclick="confirmationHapusData('{{ route('admin.hapus_akun', $list_akun->id) }}')">
                                  <i class="fa fa-fw fa-trash"></i>
                                </button>
                              </div>
                            </td>
                          </tr>
                                  </tbody>

                                  <!-- modal Edit Data -->
        <div class="modal fade" id="modal-block-vcenter-extra-large-tambah-{{ $list_akun->id }}" tabindex="-1" aria-labelledby="modal-block-vcenter" style="display: none;" aria-hidden="true">
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
                    <form class="space-y-4" action="{{ route('admin.edit_akun') }}" method="POST">
                      @csrf
                      <input type="hidden" name="id" id="id" value="{{ $list_akun->id }}">
                      <div class="row">
                        <div class="mb-4">
                          <label class="col-sm-4 col-form-label" for="example-hf-nama">Nama <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="name" name="name"  placeholder="Masukan nama ..." value="{{ $list_akun->name }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"  placeholder="Masukan email ..." value="{{ $list_akun->email }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password"  placeholder="Masukan password">
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-konfirmasi-password">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"  placeholder="Konfirmasi Password">
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select select-search instructor" required>
                              <option value="" selected disabled>Pilih Status</option>
                              <option value="active" {{ old('status', $list_akun->status ?? '') == 'active' ? 'selected' : '' }}>Aktif</option>
                              <option value="tidak active" {{ old('status', $list_akun->status ?? '') == 'tidak active' ? 'selected' : '' }}>Tidak Aktif</option>
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
                    </table>
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
                    <h3 class="block-title">Modal Tambah Akun</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-fw fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content fs-sm">
                    <form class="space-y-4" action="{{ route('admin.simpan_akun') }}" method="POST">
                      @csrf
                      <div class="row">
                        <div class="mb-4">
                          <label class="col-sm-4 col-form-label" for="example-hf-nama">Nama <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="name" name="name"  placeholder="Masukan nama ..." required>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"  placeholder="Masukan email ..." required>
                        </div>
                        <div class="mb-4">
                          <label for="status" class="form-label">Role <span class="text-danger">*</span></label>
                          <select id="role" name="role" class="form-select select-search instructor" required>
                            <option value="" selected disabled>Pilih Role</option>
                            <option value="1">Admin</option>
                            <option value="2">Petugas</option>
                          </select>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password"  placeholder="Masukan password" required>
                        </div>
                        <div class="mb-4">
                            <label class="col-sm-4 col-form-label" for="example-hf-konfirmasi-password">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"  placeholder="Konfirmasi Password" required>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select select-search instructor" required>
                              <option value="" selected disabled>Pilih Status</option>
                              <option value="active">Aktif</option>
                              <option value="tidak active">Tidak Aktif</option>
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
    @include('admin.layouts.footer')
</div>
      <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/select2/js/select2.full.min.js') }}""></script>
      <script src="{{ asset('assets/oneui/s/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/jquery-validation/additional-methods.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/pages/be_forms_validation.min.js') }}"></script>
      <script>
        // Get the password and confirmation password fields
        var passwordField = document.getElementById("password");
        var confirmPasswordField = document.getElementById("konfirmasi_password");
        var buttonSubmit = document.getElementById("btn-register");
              
        // Add event listeners to both fields
        passwordField.addEventListener("input", validatePasswords);
        confirmPasswordField.addEventListener("input", validatePasswords);
              
        function validatePasswords() {
            var password = passwordField.value;
            var confirmPassword = confirmPasswordField.value;
              
            // Check if the passwords match
            if (password === confirmPassword) {
                confirmPasswordField.style.borderColor = "green";  // Green if they match
                buttonSubmit.enabled = 'true';
            } else {
                confirmPasswordField.style.borderColor = "red";    // Red if they don't match
                buttonSubmit.enabled = 'false';
            }
        }

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