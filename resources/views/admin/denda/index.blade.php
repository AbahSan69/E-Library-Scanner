@extends('admin.layouts.main')

@section('content')
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

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Daftar Denda</h3>
          <div class="block-options d-flex align-items-center gap-2">
            <form method="GET" class="m-0">
              <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum dibayar</option>
                <option value="{{ \App\Models\Denda::PAID }}" {{ request('status') === \App\Models\Denda::PAID ? 'selected' : '' }}>Lunas</option>
                <option value="{{ \App\Models\Denda::WAIVED }}" {{ request('status') === \App\Models\Denda::WAIVED ? 'selected' : '' }}>Dispensasi</option>
              </select>
            </form>
            <a href="{{ route('admin.denda.pengaturan') }}" class="btn btn-sm btn-alt-secondary">
              <i class="fa fa-fw fa-cog"></i> Pengaturan
            </a>
          </div>
        </div>

        <div class="block-content">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter">
              <thead>
                <tr>
                  <th>Siswa</th>
                  <th>Buku</th>
                  <th class="text-center">Qty</th>
                  <th class="text-center">Hari</th>
                  <th class="text-end">Total</th>
                  <th>Status</th>
                  <th style="width:200px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($denda as $d)
                  <tr>
                    <td>{{ optional($d->siswa)->nama ?: '-' }}</td>
                    <td>{{ optional(optional($d->detail)->buku)->judul ?: '-' }}</td>
                    <td class="text-center">{{ $d->qty_terlambat }}</td>
                    <td class="text-center">{{ $d->hari_terlambat }}</td>
                    <td class="text-end">Rp {{ number_format($d->total_denda, 0, ',', '.') }}</td>
                    <td>
                      @if (!$d->is_final)
                        <span class="badge bg-warning text-dark">Berjalan</span>
                        <div class="fs-sm text-muted">Buku belum dikembalikan</div>
                      @elseif ($d->status === 'unpaid')
                        <span class="badge bg-danger">Belum dibayar</span>
                        <div class="fs-sm text-muted">
                          Kembali {{ optional($d->tanggal_dikembalikan)->format('d/m/Y') }}
                        </div>
                      @elseif ($d->status === \App\Models\Denda::PAID)
                        <span class="badge bg-success">Lunas</span>
                        <div class="fs-sm text-muted">
                          {{ optional($d->dibayar_pada)->format('d/m/Y H:i') }}
                        </div>
                      @else
                        <span class="badge bg-secondary">Dispensasi</span>
                        <div class="fs-sm text-muted">{{ $d->alasan_dispensasi }}</div>
                      @endif
                    </td>
                    <td>
                      @if ($d->bisaDiproses())
                        {{-- Konfirmasi ditangani SweetAlert (lihat script di bawah) --}}
                        <form action="{{ route('admin.denda.bayar', $d->id) }}" method="POST" class="d-inline form-bayar"
                              data-siswa="{{ optional($d->siswa)->nama ?: '-' }}"
                              data-buku="{{ optional(optional($d->detail)->buku)->judul ?: '-' }}"
                              data-total="Rp {{ number_format($d->total_denda, 0, ',', '.') }}">
                          @csrf
                          <button type="submit" class="btn btn-success btn-sm">Bayar</button>
                        </form>

                        <button type="button" class="btn btn-warning btn-sm"
                                data-bs-toggle="modal" data-bs-target="#dispensasi{{ $d->id }}">
                          Dispensasi
                        </button>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted">Belum ada data denda.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3 mb-4">
            {{ $denda->withQueryString()->links() }}
          </div>
        </div>
      </div>

    </div>
  </main>

  {{-- Modal dispensasi (di luar tabel supaya HTML valid) --}}
  @foreach ($denda as $d)
    @if ($d->bisaDiproses())
      <div class="modal fade" id="dispensasi{{ $d->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <form action="{{ route('admin.denda.dispensasi', $d->id) }}" method="POST" class="modal-content form-dispensasi"
                data-siswa="{{ optional($d->siswa)->nama ?: '-' }}"
                data-buku="{{ optional(optional($d->detail)->buku)->judul ?: '-' }}"
                data-total="Rp {{ number_format($d->total_denda, 0, ',', '.') }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title">Dispensasi Denda</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <label class="form-label">Alasan (wajib)</label>
              <textarea name="alasan_dispensasi" class="form-control" rows="3" required minlength="5"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-warning">Hapus Denda</button>
            </div>
          </form>
        </div>
      </div>
    @endif
  @endforeach

  @include('admin.layouts.footer')
</div>

<script>
  (function () {
    function esc(s) {
      return String(s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    function rincian(el) {
      return '<div class="text-start">' +
        '<div><b>Siswa</b>: ' + esc(el.dataset.siswa) + '</div>' +
        '<div><b>Buku</b>: ' + esc(el.dataset.buku) + '</div>' +
        '<div><b>Total</b>: ' + esc(el.dataset.total) + '</div>' +
        '</div>';
    }

    document.addEventListener('submit', function (e) {
      var form = e.target;

      // ===== Konfirmasi BAYAR =====
      if (form.classList.contains('form-bayar')) {
        e.preventDefault();
        Swal.fire({
          title: 'Catat pembayaran denda?',
          html: rincian(form),
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, catat lunas',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#198754',
          reverseButtons: true
        }).then(function (r) {
          if (r.isConfirmed) form.submit(); // submit() native tidak memicu event ini lagi
        });
        return;
      }

      // ===== Konfirmasi DISPENSASI =====
      // Event 'submit' hanya jalan kalau validasi required/minlength sudah lolos
      if (form.classList.contains('form-dispensasi')) {
        e.preventDefault();
        Swal.fire({
          title: 'Hapus denda ini?',
          html: rincian(form) + '<div class="mt-2 text-muted fs-sm">Denda akan ditandai dispensasi dan tidak ditagih.</div>',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus denda',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#ffc107',
          reverseButtons: true,
          target: form.closest('.modal') // render di dalam modal supaya fokus tidak direbut Bootstrap
        }).then(function (r) {
          if (r.isConfirmed) form.submit();
        });
      }
    });
  })();
</script>
@endsection