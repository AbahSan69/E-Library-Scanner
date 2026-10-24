@php
  $tgl = $surat['tanggal'];
  $tanggalSurat = $tgl->day . ' ' . $bulan[$tgl->month] . ' ' . $tgl->year;
  $statusLabel = \App\Models\Siswa::statusList();
  $st = $siswa->status ?: 'active';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Surat Keterangan Bebas Pustaka {{ $siswa->nama }}</title>
  <style>
    @page { margin: 1.8cm 2.2cm; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.6; }
    table { width: 100%; border-collapse: collapse; }
    .kop td { vertical-align: middle; }
    .kop-garis { border-bottom: 3px double #000; margin: 6px 0 14px; }
    .judul { text-align: center; margin: 16px 0 14px; }
    .judul .t1 { font-size: 13px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
    .judul .t2 { font-size: 11px; }
    .biodata td { padding: 2px 4px; vertical-align: top; }
    .ttd { margin-top: 36px; page-break-inside: avoid; }
    .ttd td { text-align: center; vertical-align: top; width: 50%; }
    .ttd .ruang { height: 64px; }
    .tombol { text-align: right; margin-bottom: 10px; }
    @media print { .tombol { display: none; } }
  </style>
</head>
<body>

@if (!empty($htmlMode))
  <div class="tombol"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
@endif

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
  <div class="t1">Surat Keterangan Bebas Pustaka</div>
  <div class="t2">Nomor: {{ $surat['nomor'] }}</div>
</div>

<p>Yang bertanda tangan di bawah ini, petugas Perpustakaan {{ $cfg['sekolah'] }}, menerangkan bahwa:</p>

<table class="biodata" style="margin-left:20px; width:auto;">
  <tr><td>Nama</td><td>: <b>{{ $siswa->nama }}</b></td></tr>
  <tr><td>NISN</td><td>: {{ $siswa->nisn }}</td></tr>
  <tr><td>Kelas</td><td>: {{ $siswa->kelas ?: '-' }}</td></tr>
  <tr><td>Status Anggota</td><td>: {{ isset($statusLabel[$st]) ? $statusLabel[$st] : $st }}@if ($siswa->tahun_lulus) (Lulus tahun {{ $siswa->tahun_lulus }})@endif</td></tr>
</table>

<p style="margin-top:12px;">
  Berdasarkan data sirkulasi perpustakaan per tanggal {{ $tanggalSurat }}, yang bersangkutan
  <b>tidak memiliki pinjaman buku yang belum dikembalikan</b> dan
  <b>tidak memiliki tanggungan denda yang belum dibayar</b>.
  Dengan demikian yang bersangkutan dinyatakan <b>BEBAS PUSTAKA</b>.
</p>

<p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

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
      <div>{{ $cfg['kota'] }}, {{ $tanggalSurat }}</div>
      <div>Petugas Perpustakaan,</div>
      <div>&nbsp;</div>
      <div class="ruang"></div>
      <div><b><u>{{ $cfg['pustakawan']['nama'] }}</u></b></div>
      <div>NIP. {{ $cfg['pustakawan']['nip'] }}</div>
    </td>
  </tr>
</table>

</body>
</html>