@extends('petugas.layouts.main')

@section('content')
<div id="page-container" class="page-header-dark main-content-boxed">
  @include('petugas.layouts.header')

  <main id="main-container">

    {{-- Navbar (area gelap) --}}
    <div class="bg-primary-darker">
      <div class="content py-3">
        <div class="d-lg-none">
          <button type="button"
                  class="btn w-100 btn-alt-secondary d-flex justify-content-between align-items-center"
                  data-toggle="class-toggle" data-target="#main-navigation" data-class="d-none">
            Menu
            <i class="fa fa-bars"></i>
          </button>
        </div>
        @include('petugas.layouts.navbar')

      <div class="content">

        {{-- Judul halaman --}}
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center mb-3">
            <h4 class="mb-2 mb-sm-0">Pengaturan Denda</h4>
            <a href="{{ route('petugas.denda.index') }}" class="btn btn-alt-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Daftar Denda
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- Form pengaturan --}}
            <div class="col-lg-5">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Aturan Denda</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <form action="{{ route('petugas.denda.pengaturan.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label" for="fine_rate_per_day">Tarif denda per hari (Rp)</label>
                                <input type="number" min="0" id="fine_rate_per_day" name="fine_rate_per_day"
                                       class="form-control"
                                       value="{{ old('fine_rate_per_day', $setting->fine_rate_per_day) }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="grace_period_days">Toleransi keterlambatan (hari)</label>
                                <input type="number" min="0" id="grace_period_days" name="grace_period_days"
                                       class="form-control"
                                       value="{{ old('grace_period_days', $setting->grace_period_days) }}" required>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="exclude_saturday" value="1" id="sab"
                                           {{ old('exclude_saturday', $setting->exclude_saturday) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sab">Kecualikan hari Sabtu</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="exclude_sunday" value="1" id="min"
                                           {{ old('exclude_sunday', $setting->exclude_sunday) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="min">Kecualikan hari Minggu</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="exclude_holidays" value="1" id="libur"
                                           {{ old('exclude_holidays', $setting->exclude_holidays) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="libur">Kecualikan hari libur nasional</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Hari libur --}}
            <div class="col-lg-7">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Hari Libur Nasional</h3>
                    </div>
                    <div class="block-content block-content-full">

                        <form action="{{ route('petugas.denda.libur.store') }}" method="POST" class="row g-2 mb-4">
                            @csrf
                            <div class="col-12 col-md-4">
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
                            </div>
                            <div class="col-12 col-md-5">
                                <input type="text" name="keterangan" class="form-control" placeholder="Keterangan"
                                       value="{{ old('keterangan') }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn btn-alt-primary w-100">
                                    <i class="fa fa-plus me-1"></i> Tambah
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 130px;">Tanggal</th>
                                        <th>Keterangan</th>
                                        <th class="text-center" style="width: 90px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($hariLibur as $h)
                                    <tr>
                                        <td class="text-nowrap">{{ $h->tanggal->format('d/m/Y') }}</td>
                                        <td>{{ $h->keterangan ? $h->keterangan : '-' }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('petugas.denda.libur.destroy', $h->id) }}" method="POST"
                                                  onsubmit="return confirm('Hapus hari libur ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-alt-danger" title="Hapus">
                                                    <i class="fa fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $hariLibur->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

      </div>
    </main>

    @include('petugas.layouts.footer')
</div>
@endsection