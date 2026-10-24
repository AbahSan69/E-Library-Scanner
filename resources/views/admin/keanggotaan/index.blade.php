@extends('admin.layouts.main')

@section('content')
@php
  $rp = function ($n) { return 'Rp ' . number_format((float) $n, 0, ',', '.'); };
  $statusList = \App\Models\Siswa::statusList();
  $warna = ['active' => 'success', 'graduated' => 'info', 'transferred' => 'warning', 'inactive' => 'secondary'];
@endphp
<div id="page-container" class="page-header-dark main-content-boxed">
  @include('admin.layouts.header')

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
        @include('admin.layouts.navbar')

    {{-- Konten utama --}}
    <div class="content">

      @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
      @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
      @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

      @if (session('tanggungan_siswa'))
        @php $tg = session('tanggungan_siswa'); @endphp
        <div class="alert alert-warning">
          <div class="fw-semibold mb-1">Tanggungan {{ $tg['nama'] }}:</div>
          @if (count($tg['data']['buku']) > 0)
            <div>Buku belum dikembalikan:</div>
            <ul class="mb-2">
              @foreach ($tg['data']['buku'] as $b)
                <li>{{ $b->judul }} ({{ $b->qty }} eks), jatuh tempo {{ \Carbon\Carbon::parse($b->jatuh_tempo)->format('d/m/Y') }}</li>
              @endforeach
            </ul>
          @endif
          @if ($tg['data']['denda_n'] > 0)
            <div>Denda belum dibayar: {{ $tg['data']['denda_n'] }} tagihan, total {{ $rp($tg['data']['denda_total']) }}</div>
          @endif
        </div>
      @endif

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Keanggotaan &amp; Arsip Siswa</h3>
          <div class="block-options">
            <a href="{{ route('admin.keanggotaan.kenaikan') }}" class="btn btn-sm btn-primary">
              <i class="fa fa-fw fa-level-up-alt me-1"></i> Kenaikan Kelas &amp; Kelulusan
            </a>
          </div>
        </div>
        <div class="block-content block-content-full">

          <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ($statusList as $k => $nm)
              <span class="badge bg-{{ $warna[$k] }} fs-sm">{{ $nm }}: {{ isset($ringkas[$k]) ? $ringkas[$k] : 0 }}</span>
            @endforeach
          </div>

          <form method="GET" action="{{ route('admin.keanggotaan.index') }}" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
              <input type="text" name="q" value="{{ $filter['q'] }}" class="form-control" placeholder="Cari nama atau NISN">
            </div>
            <div class="col-6 col-md-3">
              <select name="status" class="form-select">
                <option value="">Semua status</option>
                @foreach ($statusList as $k => $nm)
                  <option value="{{ $k }}" {{ $filter['status'] === $k ? 'selected' : '' }}>{{ $nm }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6 col-md-3">
              <select name="kelas" class="form-select">
                <option value="">Semua kelas</option>
                @foreach ($kelasList as $k)
                  <option value="{{ $k }}" {{ $filter['kelas'] === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
              <button class="btn btn-alt-primary" type="submit"><i class="fa fa-search me-1"></i> Cari</button>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter fs-sm">
              <thead>
                <tr>
                  <th>NISN</th>
                  <th>Nama</th>
                  <th>Kelas</th>
                  <th>Status</th>
                  <th>Tanggungan</th>
                  <th style="width:250px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($siswa as $s)
                  @php
                    $st = $s->status ?: 'active';
                    $t  = $tanggungan[$s->id];
                    $nBuku = count($t['buku']);
                    $ada = $nBuku > 0 || $t['denda_n'] > 0;
                  @endphp
                  <tr>
                    <td>{{ $s->nisn }}</td>
                    <td>{{ $s->nama }}</td>
                    <td>{{ $s->kelas ?: '-' }}</td>
                    <td>
                      <span class="badge bg-{{ isset($warna[$st]) ? $warna[$st] : 'secondary' }}">{{ isset($statusList[$st]) ? $statusList[$st] : $st }}</span>
                      @if ($s->tahun_lulus)<div class="text-muted">Lulus {{ $s->tahun_lulus }}</div>@endif
                      @if ($s->status_note && $st !== 'graduated')<div class="text-muted">{{ $s->status_note }}</div>@endif
                    </td>
                    <td>
                      @if ($ada)
                        @if ($nBuku > 0)<span class="badge bg-danger">{{ $nBuku }} buku</span>@endif
                        @if ($t['denda_n'] > 0)<span class="badge bg-danger">Denda {{ $rp($t['denda_total']) }}</span>@endif
                      @else
                        <span class="badge bg-success">Bersih</span>
                      @endif
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-alt-primary btn-status"
                              data-bs-toggle="modal" data-bs-target="#modalStatus"
                              data-id="{{ $s->id }}" data-nama="{{ $s->nama }}" data-status="{{ $st }}"
                              data-ada="{{ $ada ? 1 : 0 }}" data-buku="{{ $nBuku }}" data-denda="{{ $rp($t['denda_total']) }}">
                        Status
                      </button>
                      <button type="button" class="btn btn-sm btn-alt-secondary btn-kelas"
                              data-bs-toggle="modal" data-bs-target="#modalKelas"
                              data-id="{{ $s->id }}" data-nama="{{ $s->nama }}" data-kelas="{{ $s->kelas }}">
                        Kelas
                      </button>
                      @if ($ada)
                        <button type="button" class="btn btn-sm btn-alt-secondary" disabled title="Masih ada tanggungan">Bebas Pustaka</button>
                      @else
                        <a href="{{ route('admin.keanggotaan.bebas_pustaka', $s->id) }}" target="_blank" class="btn btn-sm btn-alt-success">Bebas Pustaka</a>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-muted">Tidak ada data anggota.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3 mb-4">{{ $siswa->links() }}</div>
        </div>
      </div>

    </div>
  </main>

  {{-- Modal ubah status --}}
  <div class="modal fade" id="modalStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" id="formStatus" class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Anggota</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-1 fw-semibold" id="statusNama"></div>
          <div class="alert alert-warning py-2 d-none" id="statusTanggungan"></div>
          <div class="mb-3">
            <label class="form-label">Status baru</label>
            <select name="status" id="statusBaru" class="form-select" required>
              @foreach ($statusList as $k => $nm)
                <option value="{{ $k }}">{{ $nm }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Alasan / keterangan</label>
            <input type="text" name="alasan" class="form-control" maxlength="255" placeholder="mis. pindah ke SMAN 2, putus sekolah, tinggal kelas">
          </div>
          <div class="form-check d-none" id="boxPaksa">
            <input class="form-check-input" type="checkbox" name="paksa" value="1" id="paksa">
            <label class="form-check-label" for="paksa">Tetap ubah status meskipun masih ada tanggungan (tanggungan tetap tercatat)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal ubah kelas --}}
  <div class="modal fade" id="modalKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" id="formKelas" class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Ubah Kelas (Tinggal Kelas / Koreksi)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-1 fw-semibold" id="kelasNama"></div>
          <div class="mb-3">
            <label class="form-label">Kelas baru</label>
            <input type="text" name="kelas" id="kelasBaru" class="form-control" maxlength="100" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alasan</label>
            <input type="text" name="alasan" class="form-control" maxlength="255" placeholder="mis. tinggal kelas">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  @include('admin.layouts.footer')
</div>

<script>
  (function () {
    var urlStatus = "{{ route('admin.keanggotaan.status', '__ID__') }}";
    var urlKelas  = "{{ route('admin.keanggotaan.kelas', '__ID__') }}";
    var ada = false;

    function togglePaksa() {
      var baru = document.getElementById('statusBaru').value;
      var tampil = ada && baru !== 'active';
      document.getElementById('boxPaksa').classList.toggle('d-none', !tampil);
      if (!tampil) document.getElementById('paksa').checked = false;
    }

    document.addEventListener('click', function (e) {
      var b = e.target.closest('.btn-status');
      if (b) {
        document.getElementById('formStatus').action = urlStatus.replace('__ID__', b.dataset.id);
        document.getElementById('statusNama').textContent = b.dataset.nama;
        document.getElementById('statusBaru').value = b.dataset.status;
        ada = b.dataset.ada === '1';

        var box = document.getElementById('statusTanggungan');
        if (ada) {
          box.textContent = 'Masih ada tanggungan: ' + b.dataset.buku + ' buku belum kembali, denda belum dibayar ' + b.dataset.denda + '.';
          box.classList.remove('d-none');
        } else {
          box.classList.add('d-none');
        }
        togglePaksa();
      }

      var k = e.target.closest('.btn-kelas');
      if (k) {
        document.getElementById('formKelas').action = urlKelas.replace('__ID__', k.dataset.id);
        document.getElementById('kelasNama').textContent = k.dataset.nama;
        document.getElementById('kelasBaru').value = k.dataset.kelas || '';
      }
    });

    document.getElementById('statusBaru').addEventListener('change', togglePaksa);
  })();
</script>
@endsection