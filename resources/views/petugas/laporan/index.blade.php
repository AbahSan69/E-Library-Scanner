@extends('petugas.layouts.main')

@section('content')
@php
  $rp  = function ($n) { return 'Rp ' . number_format((float) $n, 0, ',', '.'); };
  $tgl = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
  $bulanNama = \App\Services\LaporanBulananService::BULAN;
  $statusNama = \App\Services\LaporanBulananService::STATUS;
  $tingkatList = array_keys(\App\Services\LaporanBulananService::TINGKAT);
  $sir = $sirkulasi;
  $keu = $keuangan;
  $stat = $statistik;
@endphp
<div id="page-container" class="page-header-dark main-content-boxed">
  @include('petugas.layouts.header')

  <main id="main-container">

    {{-- Navbar (area gelap). Dua </div> penutup wrapper ada di dalam partial navbar --}}
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

    {{-- Konten utama --}}
    <div class="content">

      {{-- ===== FILTER ===== --}}
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Laporan Bulanan Perpustakaan</h3>
        </div>
        <div class="block-content block-content-full">
          <form method="GET" action="{{ route('petugas.laporan.index') }}">
            <div class="row g-3">
              <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                  @foreach ($bulanNama as $n => $nm)
                    <option value="{{ $n }}" {{ (int) $f['bulan'] === $n ? 'selected' : '' }}>{{ $nm }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select">
                  @foreach ($opsi['tahun'] as $th)
                    <option value="{{ $th }}" {{ (int) $f['tahun'] === $th ? 'selected' : '' }}>{{ $th }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Klasifikasi DDC</label>
                @if ($opsi['punya_ddc'])
                  <select name="ddc" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($opsi['ddc'] as $o)
                      <option value="{{ $o }}" {{ $f['ddc'] === (string) $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                  </select>
                @else
                  <select class="form-select" disabled><option>Kolom DDC belum ada</option></select>
                @endif
              </div>

              <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Tingkat Kelas</label>
                <select name="tingkat" class="form-select">
                  <option value="">Semua</option>
                  @foreach ($tingkatList as $t)
                    <option value="{{ $t }}" {{ $f['tingkat'] === $t ? 'selected' : '' }}>Kelas {{ $t }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Jurusan</label>
                @if ($opsi['punya_jurusan'])
                  <select name="jurusan" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($opsi['jurusan'] as $o)
                      <option value="{{ $o }}" {{ $f['jurusan'] === (string) $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                  </select>
                @else
                  <input type="text" name="jurusan" value="{{ $f['jurusan'] }}" class="form-control" placeholder="mis. IPA (cari di kelas)">
                @endif
              </div>

              <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Status Transaksi</label>
                <select name="status" class="form-select">
                  <option value="">Semua</option>
                  @foreach ($statusNama as $k => $nm)
                    <option value="{{ $k }}" {{ $f['status'] === $k ? 'selected' : '' }}>{{ $nm }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-fw fa-search me-1"></i> Tampilkan
              </button>
              <button type="submit" formaction="{{ route('petugas.laporan.pdf') }}" formtarget="_blank" class="btn btn-danger">
                <i class="fa fa-fw fa-file-pdf me-1"></i> Cetak PDF
              </button>
              <button type="submit" formaction="{{ route('petugas.laporan.csv') }}" class="btn btn-success">
                <i class="fa fa-fw fa-file-csv me-1"></i> Excel / CSV
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- ===== 1. REKAP SIRKULASI ===== --}}
      <h5 class="mb-3">1. Rekapitulasi Sirkulasi &mdash; {{ $label['Periode'] }}</h5>
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-2 fw-bold">{{ $sir['pinjam']['transaksi'] }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Transaksi Peminjaman</div>
              <div class="fs-sm text-muted">{{ $sir['pinjam']['judul'] }} judul &middot; {{ $sir['pinjam']['eksemplar'] }} eksemplar</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-2 fw-bold text-success">{{ $sir['tepat'] }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Kembali Tepat Waktu</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-2 fw-bold text-danger">{{ $sir['terlambat'] }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Kembali Terlambat</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-2 fw-bold text-warning">{{ $sir['outstanding'] }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Belum Kembali</div>
              <div class="fs-sm text-muted">{{ $sir['outstanding_telat'] }} sudah lewat jatuh tempo</div>
            </div>
          </div>
        </div>
      </div>

      @if ($f['status'] !== 'outstanding')
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Daftar Pengembalian</h3>
          <div class="block-options"><span class="badge bg-primary">{{ $sir['daftar_kembali']->count() }} data</span></div>
        </div>
        <div class="block-content">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter fs-sm">
              <thead>
                <tr>
                  <th class="text-center" style="width:50px">No</th>
                  <th>Tgl Kembali</th>
                  <th>Jatuh Tempo</th>
                  <th>Siswa</th>
                  <th>Kelas</th>
                  <th>Buku</th>
                  <th>Status</th>
                  <th class="text-center">Telat (hari)</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($sir['daftar_kembali'] as $i => $x)
                  <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $tgl($x->tgl_dikembalikan) }}</td>
                    <td>{{ $tgl($x->jatuh_tempo) }}</td>
                    <td>{{ $x->nama }}<div class="text-muted">{{ $x->nisn }}</div></td>
                    <td>{{ $x->kelas }}@if (!empty($x->jurusan)) <div class="text-muted">{{ $x->jurusan }}</div>@endif</td>
                    <td>{{ $x->judul }}@if (!empty($x->ddc)) <div class="text-muted">DDC: {{ $x->ddc }}</div>@endif</td>
                    <td>
                      @if ($x->klasifikasi === 'terlambat')
                        <span class="badge bg-danger">Terlambat</span>
                      @else
                        <span class="badge bg-success">Tepat Waktu</span>
                      @endif
                    </td>
                    <td class="text-center">{{ $x->hari_telat }}</td>
                  </tr>
                @empty
                  <tr><td colspan="8" class="text-center text-muted">Tidak ada pengembalian pada periode ini.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      @if (in_array($f['status'], ['', 'outstanding'], true))
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Daftar Buku Belum Kembali</h3>
          <div class="block-options"><span class="badge bg-warning text-dark">{{ $sir['daftar_outstanding']->count() }} data</span></div>
        </div>
        <div class="block-content">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter fs-sm">
              <thead>
                <tr>
                  <th class="text-center" style="width:50px">No</th>
                  <th>Tgl Pinjam</th>
                  <th>Jatuh Tempo</th>
                  <th>Siswa</th>
                  <th>Kelas</th>
                  <th>Buku</th>
                  <th class="text-center">Qty</th>
                  <th class="text-center">Terlambat per hari ini</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($sir['daftar_outstanding'] as $i => $x)
                  <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $tgl($x->tanggal_pinjam) }}</td>
                    <td>{{ $tgl($x->jatuh_tempo) }}</td>
                    <td>{{ $x->nama }}<div class="text-muted">{{ $x->nisn }}</div></td>
                    <td>{{ $x->kelas }}@if (!empty($x->jurusan)) <div class="text-muted">{{ $x->jurusan }}</div>@endif</td>
                    <td>{{ $x->judul }}@if (!empty($x->ddc)) <div class="text-muted">DDC: {{ $x->ddc }}</div>@endif</td>
                    <td class="text-center">{{ $x->qty }}</td>
                    <td class="text-center">
                      @if ($x->hari_telat > 0)
                        <span class="badge bg-danger">{{ $x->hari_telat }} hari</span>
                      @else
                        <span class="badge bg-secondary">Belum jatuh tempo</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="8" class="text-center text-muted">Tidak ada buku yang belum kembali.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      {{-- ===== 2. REKAP KEUANGAN ===== --}}
      <h5 class="mb-3 mt-5">2. Rekapitulasi Keuangan (Denda)</h5>
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-3 fw-bold">{{ $rp($keu['terhitung']['total']) }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Denda Terhitung</div>
              <div class="fs-sm text-muted">{{ $keu['terhitung']['n'] }} denda bulan ini</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-3 fw-bold text-success">{{ $rp($keu['terbayar']['total']) }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Terbayar (Kas Masuk)</div>
              <div class="fs-sm text-muted">{{ $keu['terbayar']['n'] }} pembayaran bulan ini</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-3 fw-bold text-danger">{{ $rp($keu['piutang']['total']) }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Piutang Denda</div>
              <div class="fs-sm text-muted">{{ $keu['piutang']['n'] }} belum dibayar s.d. akhir bulan</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="block block-rounded mb-0 h-100">
            <div class="block-content block-content-full text-center">
              <div class="fs-3 fw-bold text-secondary">{{ $rp($keu['dispensasi']['total']) }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">Dispensasi</div>
              <div class="fs-sm text-muted">{{ $keu['dispensasi']['n'] }} denda dibebaskan</div>
            </div>
          </div>
        </div>
      </div>
      <p class="fs-sm text-muted">
        Denda berjalan (buku belum kembali, estimasi): {{ $rp($keu['berjalan']['total']) }} dari {{ $keu['berjalan']['n'] }} denda.
        Denda terbayar memakai basis kas, jadi bisa mencakup denda dari bulan sebelumnya.
      </p>

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Rincian Denda Terhitung Bulan Ini</h3>
          <div class="block-options"><span class="badge bg-primary">{{ $keu['rincian']->count() }} data</span></div>
        </div>
        <div class="block-content">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter fs-sm">
              <thead>
                <tr>
                  <th class="text-center" style="width:50px">No</th>
                  <th>Tgl Kembali</th>
                  <th>Siswa</th>
                  <th>Kelas</th>
                  <th>Buku</th>
                  <th class="text-center">Qty</th>
                  <th class="text-center">Hari</th>
                  <th class="text-end">Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($keu['rincian'] as $i => $x)
                  <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $tgl($x->tanggal_dikembalikan) }}</td>
                    <td>{{ $x->nama }}<div class="text-muted">{{ $x->nisn }}</div></td>
                    <td>{{ $x->kelas }}</td>
                    <td>{{ $x->judul }}</td>
                    <td class="text-center">{{ $x->qty_terlambat }}</td>
                    <td class="text-center">{{ $x->hari_terlambat }}</td>
                    <td class="text-end">{{ $rp($x->total_denda) }}</td>
                    <td>
                      @if ($x->status === \App\Models\Denda::PAID)
                        <span class="badge bg-success">Lunas</span>
                        <div class="text-muted">{{ $tgl($x->dibayar_pada) }}</div>
                      @elseif ($x->status === \App\Models\Denda::WAIVED)
                        <span class="badge bg-secondary">Dispensasi</span>
                      @else
                        <span class="badge bg-danger">Belum dibayar</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="9" class="text-center text-muted">Tidak ada denda pada periode ini.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- ===== 3. STATISTIK ===== --}}
      <h5 class="mb-3 mt-5">3. Statistik &amp; Tren Peminjaman</h5>
      <div class="row">
        <div class="col-lg-6">
          <div class="block block-rounded">
            <div class="block-header block-header-default"><h3 class="block-title">10 Buku Paling Sering Dipinjam</h3></div>
            <div class="block-content">
              <div class="table-responsive">
                <table class="table table-sm table-striped table-vcenter fs-sm">
                  <thead><tr><th class="text-center" style="width:50px">#</th><th>Judul</th><th class="text-center">Frekuensi</th><th class="text-center">Eks</th></tr></thead>
                  <tbody>
                    @forelse ($stat['top_buku'] as $i => $x)
                      <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $x->judul }}<div class="text-muted">{{ $x->pengarang }}</div></td>
                        <td class="text-center">{{ $x->frekuensi }}</td>
                        <td class="text-center">{{ $x->eksemplar }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="block block-rounded">
            <div class="block-header block-header-default"><h3 class="block-title">10 Anggota Paling Aktif</h3></div>
            <div class="block-content">
              <div class="table-responsive">
                <table class="table table-sm table-striped table-vcenter fs-sm">
                  <thead><tr><th class="text-center" style="width:50px">#</th><th>Nama</th><th>Kelas</th><th class="text-center">Frekuensi</th></tr></thead>
                  <tbody>
                    @forelse ($stat['top_anggota'] as $i => $x)
                      <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $x->nama }}<div class="text-muted">{{ $x->nisn }}</div></td>
                        <td>{{ $x->kelas }}</td>
                        <td class="text-center">{{ $x->frekuensi }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="block block-rounded">
            <div class="block-header block-header-default"><h3 class="block-title">Kelas dengan Peminjaman Tertinggi</h3></div>
            <div class="block-content">
              <div class="table-responsive">
                <table class="table table-sm table-striped table-vcenter fs-sm">
                  <thead><tr><th class="text-center" style="width:50px">#</th><th>Kelas</th><th class="text-center">Frekuensi</th><th class="text-center">Anggota</th></tr></thead>
                  <tbody>
                    @forelse ($stat['top_kelas'] as $i => $x)
                      <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $x->kelas ?: '-' }}</td>
                        <td class="text-center">{{ $x->frekuensi }}</td>
                        <td class="text-center">{{ $x->anggota }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
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