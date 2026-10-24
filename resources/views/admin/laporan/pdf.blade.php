@php
  $rp  = function ($n) { return 'Rp ' . number_format((float) $n, 0, ',', '.'); };
  $tgl = function ($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; };
  $bulanNama = \App\Services\LaporanBulananService::BULAN;
  $sir = $sirkulasi;
  $keu = $keuangan;
  $stat = $statistik;
  $sekarang = \Carbon\Carbon::now();
  $tanggalCetak = $sekarang->day . ' ' . $bulanNama[$sekarang->month] . ' ' . $sekarang->year;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Perpustakaan {{ $label['Periode'] }}</title>
  <style>
    @page { margin: 1.4cm 1.6cm; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #000; }
    h2 { font-size: 11px; margin: 16px 0 6px; text-transform: uppercase; }
    h3 { font-size: 10px; margin: 12px 0 4px; }
    table { width: 100%; border-collapse: collapse; }
    .kop td { border: 0; vertical-align: middle; }
    .kop-garis { border-bottom: 3px double #000; margin: 6px 0 10px; }
    .judul { text-align: center; margin-bottom: 8px; }
    .judul .t1 { font-size: 13px; font-weight: bold; text-transform: uppercase; }
    .judul .t2 { font-size: 10px; }
    .data th, .data td { border: 1px solid #333; padding: 3px 4px; vertical-align: top; }
    .data th { background: #e6e6e6; text-align: center; }
    .data tr { page-break-inside: avoid; }
    .c { text-align: center; }
    .r { text-align: right; }
    .muted { color: #555; }
    .filter td { border: 0; padding: 1px 4px 1px 0; }
    .ttd { margin-top: 26px; page-break-inside: avoid; }
    .ttd td { border: 0; text-align: center; vertical-align: top; width: 50%; }
    .ttd .ruang { height: 58px; }
    .tombol { text-align: right; margin-bottom: 10px; }
    @media print { .tombol { display: none; } }
  </style>
</head>
<body>

@if (!empty($htmlMode))
  <div class="tombol"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
@endif

{{-- ===== KOP SURAT ===== --}}
<table class="kop">
  <tr>
    <td style="width:70px; text-align:center;">
      @if (!empty($logo))<img src="{{ $logo }}" alt="Logo" style="height:62px;">@endif
    </td>
    <td style="text-align:center;">
      <div style="font-size:11px;">{{ $cfg['pemerintah'] }}</div>
      <div style="font-size:11px;">{{ $cfg['dinas'] }}</div>
      <div style="font-size:15px; font-weight:bold;">{{ $cfg['sekolah'] }}</div>
      <div style="font-size:9px;">{{ $cfg['alamat'] }}</div>
      <div style="font-size:9px;">{{ $cfg['kontak'] }}</div>
    </td>
    <td style="width:70px;"></td>
  </tr>
</table>
<div class="kop-garis"></div>

<div class="judul">
  <div class="t1">Laporan Bulanan Perpustakaan</div>
  <div class="t2">Periode {{ $label['Periode'] }}</div>
</div>

@if (count($label) > 1)
<table class="filter" style="width:auto; margin-bottom:6px;">
  @foreach ($label as $k => $v)
    @if ($k !== 'Periode')
      <tr><td>{{ $k }}</td><td>: {{ $v }}</td></tr>
    @endif
  @endforeach
</table>
@endif

{{-- ===== 1. SIRKULASI ===== --}}
<h2>1. Rekapitulasi Sirkulasi</h2>
<table class="data" style="width:60%;">
  <tr><td>Total transaksi peminjaman</td><td class="r" style="width:90px;">{{ $sir['pinjam']['transaksi'] }}</td></tr>
  <tr><td>Total judul / eksemplar dipinjam</td><td class="r">{{ $sir['pinjam']['judul'] }} / {{ $sir['pinjam']['eksemplar'] }}</td></tr>
  <tr><td>Dikembalikan tepat waktu</td><td class="r">{{ $sir['tepat'] }}</td></tr>
  <tr><td>Dikembalikan terlambat</td><td class="r">{{ $sir['terlambat'] }}</td></tr>
  <tr><td>Belum kembali (outstanding)</td><td class="r">{{ $sir['outstanding'] }}</td></tr>
  <tr><td>&nbsp;&nbsp;di antaranya sudah lewat jatuh tempo</td><td class="r">{{ $sir['outstanding_telat'] }}</td></tr>
</table>

@if ($f['status'] !== 'outstanding')
<h3>Daftar Pengembalian</h3>
<table class="data">
  <thead>
    <tr>
      <th style="width:26px;">No</th><th>Tgl Kembali</th><th>Jatuh Tempo</th><th>Siswa</th>
      <th>Kelas</th><th>Judul Buku</th><th>Status</th><th>Telat (hari)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($sir['daftar_kembali'] as $i => $x)
      <tr>
        <td class="c">{{ $i + 1 }}</td>
        <td class="c">{{ $tgl($x->tgl_dikembalikan) }}</td>
        <td class="c">{{ $tgl($x->jatuh_tempo) }}</td>
        <td>{{ $x->nama }} <span class="muted">({{ $x->nisn }})</span></td>
        <td>{{ $x->kelas }}</td>
        <td>{{ $x->judul }}</td>
        <td class="c">{{ $x->klasifikasi === 'terlambat' ? 'Terlambat' : 'Tepat Waktu' }}</td>
        <td class="c">{{ $x->hari_telat }}</td>
      </tr>
    @empty
      <tr><td colspan="8" class="c muted">Tidak ada pengembalian pada periode ini.</td></tr>
    @endforelse
  </tbody>
</table>
@endif

@if (in_array($f['status'], ['', 'outstanding'], true))
<h3>Daftar Buku Belum Kembali</h3>
<table class="data">
  <thead>
    <tr>
      <th style="width:26px;">No</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Siswa</th>
      <th>Kelas</th><th>Judul Buku</th><th>Qty</th><th>Terlambat per hari ini</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($sir['daftar_outstanding'] as $i => $x)
      <tr>
        <td class="c">{{ $i + 1 }}</td>
        <td class="c">{{ $tgl($x->tanggal_pinjam) }}</td>
        <td class="c">{{ $tgl($x->jatuh_tempo) }}</td>
        <td>{{ $x->nama }} <span class="muted">({{ $x->nisn }})</span></td>
        <td>{{ $x->kelas }}</td>
        <td>{{ $x->judul }}</td>
        <td class="c">{{ $x->qty }}</td>
        <td class="c">{{ $x->hari_telat > 0 ? $x->hari_telat . ' hari' : '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="8" class="c muted">Tidak ada buku yang belum kembali.</td></tr>
    @endforelse
  </tbody>
</table>
@endif

{{-- ===== 2. KEUANGAN ===== --}}
<h2>2. Rekapitulasi Keuangan (Denda)</h2>
<table class="data" style="width:75%;">
  <thead><tr><th>Keterangan</th><th style="width:70px;">Jumlah</th><th style="width:110px;">Nominal</th></tr></thead>
  <tbody>
    <tr><td>Denda terhitung bulan ini</td><td class="c">{{ $keu['terhitung']['n'] }}</td><td class="r">{{ $rp($keu['terhitung']['total']) }}</td></tr>
    <tr><td>Denda terbayar / kas masuk bulan ini</td><td class="c">{{ $keu['terbayar']['n'] }}</td><td class="r">{{ $rp($keu['terbayar']['total']) }}</td></tr>
    <tr><td>Dispensasi (dibebaskan) dari denda bulan ini</td><td class="c">{{ $keu['dispensasi']['n'] }}</td><td class="r">{{ $rp($keu['dispensasi']['total']) }}</td></tr>
    <tr><td><b>Piutang denda belum dibayar (s.d. akhir bulan)</b></td><td class="c"><b>{{ $keu['piutang']['n'] }}</b></td><td class="r"><b>{{ $rp($keu['piutang']['total']) }}</b></td></tr>
    <tr><td class="muted">Denda berjalan (estimasi, buku belum kembali)</td><td class="c muted">{{ $keu['berjalan']['n'] }}</td><td class="r muted">{{ $rp($keu['berjalan']['total']) }}</td></tr>
  </tbody>
</table>
<div class="muted" style="margin-top:3px;">Denda terbayar memakai basis kas, sehingga dapat mencakup denda dari bulan sebelumnya.</div>

<h3>Rincian Denda Terhitung Bulan Ini</h3>
<table class="data">
  <thead>
    <tr>
      <th style="width:26px;">No</th><th>Tgl Kembali</th><th>Siswa</th><th>Kelas</th><th>Judul Buku</th>
      <th>Qty</th><th>Hari</th><th>Total</th><th>Status</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($keu['rincian'] as $i => $x)
      <tr>
        <td class="c">{{ $i + 1 }}</td>
        <td class="c">{{ $tgl($x->tanggal_dikembalikan) }}</td>
        <td>{{ $x->nama }} <span class="muted">({{ $x->nisn }})</span></td>
        <td>{{ $x->kelas }}</td>
        <td>{{ $x->judul }}</td>
        <td class="c">{{ $x->qty_terlambat }}</td>
        <td class="c">{{ $x->hari_terlambat }}</td>
        <td class="r">{{ $rp($x->total_denda) }}</td>
        <td class="c">
          @if ($x->status === \App\Models\Denda::PAID) Lunas ({{ $tgl($x->dibayar_pada) }})
          @elseif ($x->status === \App\Models\Denda::WAIVED) Dispensasi
          @else Belum dibayar @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="9" class="c muted">Tidak ada denda pada periode ini.</td></tr>
    @endforelse
  </tbody>
</table>

{{-- ===== 3. STATISTIK ===== --}}
<h2>3. Statistik &amp; Tren Peminjaman</h2>

<h3>10 Buku Paling Sering Dipinjam</h3>
<table class="data">
  <thead><tr><th style="width:26px;">No</th><th>Judul</th><th>Pengarang</th><th style="width:70px;">Frekuensi</th><th style="width:60px;">Eksemplar</th></tr></thead>
  <tbody>
    @forelse ($stat['top_buku'] as $i => $x)
      <tr><td class="c">{{ $i + 1 }}</td><td>{{ $x->judul }}</td><td>{{ $x->pengarang }}</td><td class="c">{{ $x->frekuensi }}</td><td class="c">{{ $x->eksemplar }}</td></tr>
    @empty
      <tr><td colspan="5" class="c muted">Belum ada data.</td></tr>
    @endforelse
  </tbody>
</table>

<h3>10 Anggota Paling Aktif</h3>
<table class="data">
  <thead><tr><th style="width:26px;">No</th><th>NISN</th><th>Nama</th><th>Kelas</th><th style="width:70px;">Frekuensi</th></tr></thead>
  <tbody>
    @forelse ($stat['top_anggota'] as $i => $x)
      <tr><td class="c">{{ $i + 1 }}</td><td class="c">{{ $x->nisn }}</td><td>{{ $x->nama }}</td><td>{{ $x->kelas }}</td><td class="c">{{ $x->frekuensi }}</td></tr>
    @empty
      <tr><td colspan="5" class="c muted">Belum ada data.</td></tr>
    @endforelse
  </tbody>
</table>

<h3>Kelas dengan Peminjaman Tertinggi</h3>
<table class="data" style="width:60%;">
  <thead><tr><th style="width:26px;">No</th><th>Kelas</th><th style="width:70px;">Frekuensi</th><th style="width:90px;">Anggota Meminjam</th></tr></thead>
  <tbody>
    @forelse ($stat['top_kelas'] as $i => $x)
      <tr><td class="c">{{ $i + 1 }}</td><td>{{ $x->kelas ?: '-' }}</td><td class="c">{{ $x->frekuensi }}</td><td class="c">{{ $x->anggota }}</td></tr>
    @empty
      <tr><td colspan="4" class="c muted">Belum ada data.</td></tr>
    @endforelse
  </tbody>
</table>

{{-- ===== TANDA TANGAN ===== --}}
<table class="ttd">
  <tr>
    <td>
      <div>&nbsp;</div>
      <div>Mengetahui,</div>
      <div>{{ $cfg['kepala_sekolah']['jabatan'] }}</div>
      <div class="ruang"></div>
      <div><b><u>{{ $cfg['kepala_sekolah']['nama'] }}</u></b></div>
      <div>NIP. {{ $cfg['kepala_sekolah']['nip'] }}</div>
    </td>
    <td>
      <div>{{ $cfg['kota'] }}, {{ $tanggalCetak }}</div>
      <div>Dibuat oleh,</div>
      <div>{{ $cfg['pustakawan']['jabatan'] }}</div>
      <div class="ruang"></div>
      <div><b><u>{{ $cfg['pustakawan']['nama'] }}</u></b></div>
      <div>NIP. {{ $cfg['pustakawan']['nip'] }}</div>
    </td>
  </tr>
</table>

</body>
</html>