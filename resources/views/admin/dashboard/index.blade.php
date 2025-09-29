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
            <div class="row">
              <div class="col-6 col-lg-3">
                <a class="block block-rounded block-link-shadow text-center" href="{{ route('admin.peminjaman') }}">
                  <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold {{ $total_hari_ini == 0 ? 'text-secondary' : 'text-success' }}">
                      {{ $total_hari_ini }}
                    </div>                  
                  </div>
                  <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">
                      Peminjaman Hari Ini
                    </p>
                  </div>
                </a>
              </div>
              <div class="col-6 col-lg-3">
                <a class="block block-rounded block-link-shadow text-center" href="{{ route('admin.peminjaman') }}">
                  <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold {{ $total_peminjaman == 0 ? 'text-secondary' : 'text-success' }}">
                      {{ $total_peminjaman }}
                    </div>                  
                  </div>
                  <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">
                      Total Peminjaman
                    </p>
                  </div>
                </a>
              </div>
              <div class="col-6 col-lg-3">
                <a class="block block-rounded block-link-shadow text-center" href="{{ route('admin.buku') }}">
                  <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold {{ $total_buku == 0 ? 'text-secondary' : 'text-success' }}">
                      {{ $total_buku }}
                    </div>                  
                  </div>
                  <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">
                      Total Buku
                    </p>
                  </div>
                </a>
              </div>
              <div class="col-6 col-lg-3">
                <a class="block block-rounded block-link-shadow text-center" href="{{ route('admin.siswa') }}">
                  <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold {{ $total_siswa == 0 ? 'text-secondary' : 'text-success' }}">
                      {{ $total_siswa }}
                    </div>                  
                  </div>
                  <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">
                      Total Siswa
                    </p>
                  </div>
                </a>
              </div>
            </div>
            <div class="row items-push">
              <div class="col-xl-6">
                <div class="block block-rounded h-100 mb-0">
                  <div class="block-header block-header-default">
                    <h3 class="block-title">Top Buku</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                        <i class="si si-refresh"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter fs-sm">
                      <tbody>
                        @foreach($top_buku as $i => $b)
                        <tr>
                            <td class="text-center" style="width: 100px;">
                                <span class="fw-semibold">#{{ $i+1 }}</span>
                            </td>
                            <td>
                                <span>{{ $b->judul }}</span>
                            </td>
                            <td class="d-none d-sm-table-cell text-center">
                                <span class="fw-bold">{{ $b->peminjaman_count }}</span> kali dipinjam
                            </td>
                        </tr>
                        @endforeach
                    </tbody>                    
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-xl-6">
                <div class="block block-rounded h-100 mb-0">
                  <div class="block-header block-header-default">
                    <h3 class="block-title">Top Siswa</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                        <i class="si si-refresh"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content">
                    <table class="table table-borderless table-striped table-vcenter">
                      <tbody>
                        @foreach($top_siswa as $i => $s)
                        <tr>
                            <td class="fw-semibold text-center fs-sm" style="width: 100px;">
                                #{{ $i+1 }}
                            </td>
                            <td class="d-none d-sm-table-cell fs-sm">
                                {{ $s->nisn }} - {{ $s->nama }}
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $s->kelas }}</span>
                            </td>
                            <td class="fw-medium fs-sm text-end">
                                {{ $s->peminjaman_count }} kali
                            </td>
                        </tr>
                        @endforeach
                    </tbody>                    
                    </table>
                  </div>
                </div>
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

</div>
@endsection