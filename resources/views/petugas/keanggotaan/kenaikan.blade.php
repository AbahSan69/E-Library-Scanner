@extends('petugas.layouts.main')

@section('content')
@php
  $rp = function ($n) { return 'Rp ' . number_format((float) $n, 0, ',', '.'); };
  $tipeNama = [
    'naik_kelas' => 'Kenaikan kelas', 'lulus' => 'Kelulusan', 'ubah_status' => 'Ubah status',
    'ubah_kelas' => 'Ubah kelas', 'bebas_pustaka' => 'Bebas pustaka',
  ];
  $naikRows = array_merge($rombel['XI'], $rombel['X']); // tampil dari tingkat tertinggi
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

      @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
      @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
      @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

      @if (session('ditahan'))
        <div class="alert alert-warning">
          <div class="fw-semibold mb-2">{{ count(session('ditahan')) }} siswa tidak diluluskan karena masih punya tanggungan (status tetap Aktif):</div>
          <div class="table-responsive">
            <table class="table table-sm table-bordered bg-white mb-0 fs-sm">
              <thead><tr><th>Nama</th><th>Kelas</th><th class="text-center">Buku belum kembali</th><th class="text-end">Denda belum dibayar</th></tr></thead>
              <tbody>
                @foreach (session('ditahan') as $x)
                  <tr>
                    <td>{{ $x->nama }} <span class="text-muted">({{ $x->nisn }})</span></td>
                    <td>{{ $x->kelas }}</td>
                    <td class="text-center">{{ $x->buku }}</td>
                    <td class="text-end">{{ $x->denda_n }} tagihan, {{ $rp($x->denda_total) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Kenaikan Kelas &amp; Kelulusan</h4>
        <a href="{{ route('petugas.keanggotaan.index') }}" class="btn btn-alt-secondary btn-sm">
          <i class="fa fa-fw fa-arrow-left me-1"></i> Arsip Anggota
        </a>
      </div>

      <div class="alert alert-info">
        <b>Urutan yang benar saat pergantian tahun ajaran:</b>
        (1) luluskan kelas XII, (2) naikkan kelas XI ke XII dan kelas X ke XI.
        Tidak ada data siswa yang dihapus. Semua perubahan tercatat di riwayat di bagian bawah.
      </div>

      {{-- ===== 1. KELULUSAN MASSAL ===== --}}
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">1. Kelulusan Massal (Kelas XII)</h3>
        </div>
        <div class="block-content block-content-full">
          @if (count($rombel['XII']) === 0)
            <div class="text-muted">Tidak ada siswa aktif di kelas XII.</div>
          @else
            <form method="POST" action="{{ route('petugas.keanggotaan.lulus') }}" id="formLulus">
              @csrf
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter fs-sm">
                  <thead>
                    <tr>
                      <th class="text-center" style="width:50px">Pilih</th>
                      <th>Rombel</th>
                      <th class="text-center">Siswa Aktif</th>
                      <th class="text-center">Masih Bertanggungan</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($rombel['XII'] as $r)
                      <tr>
                        <td class="text-center"><input type="checkbox" class="form-check-input" name="rombel[]" value="{{ $r['kelas'] }}" checked></td>
                        <td>{{ $r['kelas'] }}</td>
                        <td class="text-center">{{ $r['jumlah'] }}</td>
                        <td class="text-center">
                          @if ($r['tanggungan'] > 0)<span class="badge bg-danger">{{ $r['tanggungan'] }} siswa</span>
                          @else<span class="badge bg-success">Bersih</span>@endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              @if (count($rombel['tanggungan_xii']) > 0)
                <div class="alert alert-warning">
                  <div class="fw-semibold mb-2">Peringatan tanggungan ({{ count($rombel['tanggungan_xii']) }} siswa kelas XII):</div>
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white mb-0 fs-sm">
                      <thead><tr><th>Nama</th><th>Kelas</th><th class="text-center">Buku belum kembali</th><th class="text-end">Denda belum dibayar</th></tr></thead>
                      <tbody>
                        @foreach ($rombel['tanggungan_xii'] as $x)
                          <tr>
                            <td>{{ $x->nama }} <span class="text-muted">({{ $x->nisn }})</span></td>
                            <td>{{ $x->kelas }}</td>
                            <td class="text-center">{{ $x->buku }}</td>
                            <td class="text-end">{{ $x->denda_n }} tagihan, {{ $rp($x->denda_total) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <div class="mt-2 fs-sm">Secara bawaan siswa di atas ditahan (tetap Aktif) sampai tanggungannya selesai.</div>
                </div>
              @endif

              <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                  <label class="form-label">Tahun lulus</label>
                  <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}" min="2000" max="2100" required>
                </div>
                <div class="col-12 col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="paksa" value="1" id="paksaLulus">
                    <label class="form-check-label" for="paksaLulus">Luluskan juga yang masih punya tanggungan (tanggungan tetap tercatat dan bisa diselesaikan)</label>
                  </div>
                </div>
                <div class="col-12 col-md-3 d-grid">
                  <button type="submit" class="btn btn-success"><i class="fa fa-fw fa-graduation-cap me-1"></i> Luluskan</button>
                </div>
              </div>
            </form>
          @endif
        </div>
      </div>

      {{-- ===== 2. KENAIKAN KELAS ===== --}}
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">2. Kenaikan Kelas Massal (X ke XI, XI ke XII)</h3>
        </div>
        <div class="block-content block-content-full">
          @if (count($naikRows) === 0)
            <div class="text-muted">Tidak ada siswa aktif di kelas X atau XI.</div>
          @else
            <form method="POST" action="{{ route('petugas.keanggotaan.naik') }}" id="formNaik">
              @csrf
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter fs-sm">
                  <thead>
                    <tr>
                      <th class="text-center" style="width:50px">Pilih</th>
                      <th>Rombel asal</th>
                      <th class="text-center">Siswa Aktif</th>
                      <th style="width:35%">Rombel tujuan (bisa diubah)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($naikRows as $i => $r)
                      <tr>
                        <td class="text-center">
                          <input type="checkbox" class="form-check-input" name="pilih[{{ $i }}]" value="1" checked>
                          <input type="hidden" name="src[{{ $i }}]" value="{{ $r['kelas'] }}">
                        </td>
                        <td>{{ $r['kelas'] }}</td>
                        <td class="text-center">{{ $r['jumlah'] }}</td>
                        <td><input type="text" name="target[{{ $i }}]" class="form-control form-control-sm" value="{{ $r['saran'] }}" maxlength="100"></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="fs-sm text-muted mb-3">
                Rombel yang tidak dicentang tetap di kelasnya. Untuk siswa tinggal kelas, kenaikan massal
                tetap dijalankan lalu kembalikan siswa tersebut lewat menu <b>Kelas</b> di Arsip Anggota.
              </div>
              <button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-level-up-alt me-1"></i> Naikkan Kelas</button>
            </form>
          @endif

          @if (count($rombel['lain']) > 0)
            <hr>
            <div class="fs-sm text-muted mb-2">Rombel aktif dengan tingkat tidak dikenali (tidak diproses otomatis, ubah manual lewat Arsip Anggota):</div>
            <div class="d-flex flex-wrap gap-2">
              @foreach ($rombel['lain'] as $r)
                <span class="badge bg-secondary">{{ $r['kelas'] !== '' ? $r['kelas'] : '(kosong)' }}: {{ $r['jumlah'] }}</span>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      {{-- ===== RIWAYAT ===== --}}
      <div class="block block-rounded">
        <div class="block-header block-header-default"><h3 class="block-title">Riwayat Perubahan Terakhir</h3></div>
        <div class="block-content">
          <div class="table-responsive">
            <table class="table table-sm table-striped table-vcenter fs-sm">
              <thead><tr><th>Waktu</th><th>Jenis</th><th>Keterangan</th><th class="text-center">Siswa</th></tr></thead>
              <tbody>
                @forelse ($log as $l)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($l->waktu)->format('d/m/Y H:i') }}</td>
                    <td>{{ isset($tipeNama[$l->tipe]) ? $tipeNama[$l->tipe] : $l->tipe }}</td>
                    <td>
                      @if ($l->nama)<b>{{ $l->nama }}</b>: @endif
                      @if ($l->tipe === 'ubah_status' || $l->tipe === 'lulus')
                        {{ $l->dari_status }} &rarr; {{ $l->ke_status }}
                      @elseif ($l->dari_kelas !== $l->ke_kelas)
                        {{ $l->jumlah > 1 ? 'beberapa rombel' : $l->dari_kelas . ' → ' . $l->ke_kelas }}
                      @endif
                      @if ($l->nomor_surat) {{ $l->nomor_surat }} @endif
                      @if ($l->alasan)<div class="text-muted">{{ $l->alasan }}</div>@endif
                    </td>
                    <td class="text-center">{{ $l->jumlah }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center text-muted">Belum ada riwayat.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>

  @include('petugas.layouts.footer')
</div>

<script>
  (function () {
    function konfirmasi(form, opsi) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: opsi.judul,
          html: opsi.html(),
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: opsi.ya,
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then(function (r) { if (r.isConfirmed) form.submit(); });
      });
    }

    var lulus = document.getElementById('formLulus');
    if (lulus) {
      konfirmasi(lulus, {
        judul: 'Luluskan rombel terpilih?',
        ya: 'Ya, luluskan',
        html: function () {
          var n = lulus.querySelectorAll('input[name="rombel[]"]:checked').length;
          var paksa = lulus.querySelector('input[name="paksa"]').checked;
          return n + ' rombel akan diubah menjadi Lulus/Alumni. Akun terkunci dari peminjaman baru, data dan riwayat tetap utuh.'
            + (paksa ? '<br><b>Siswa bertanggungan ikut diluluskan.</b>' : '');
        }
      });
    }

    var naik = document.getElementById('formNaik');
    if (naik) {
      konfirmasi(naik, {
        judul: 'Naikkan kelas rombel terpilih?',
        ya: 'Ya, naikkan',
        html: function () {
          var n = naik.querySelectorAll('input[name^="pilih"]:checked').length;
          return n + ' rombel akan dipindahkan ke rombel tujuan. Pastikan kelas XII sudah diluluskan lebih dulu.';
        }
      });
    }
  })();
</script>
@endsection