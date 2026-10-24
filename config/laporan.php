<?php

/*
 * Pengaturan kop surat & penanda tangan Laporan Bulanan Perpustakaan.
 * Sesuaikan isinya dengan data resmi sekolah, lalu jalankan: php artisan config:clear
 */
return [
    'pemerintah' => 'PEMERINTAH PROVINSI JAWA BARAT',
    'dinas'      => 'DINAS PENDIDIKAN',
    'sekolah'    => 'SMA NEGERI 1 CITEUREUP',
    'alamat'     => 'Isi alamat sekolah di config/laporan.php',
    'kontak'     => 'Website: https://www.sman01citeureup.sch.id/',

    // path relatif terhadap folder public/
    'logo'       => 'assets/oneui/media/favicons/logo_sman1citeurep.png',

    // kota untuk baris "Citeureup, 24 Oktober 2026"
    'kota'       => 'Citeureup',

    'kepala_sekolah' => [
        'jabatan' => 'Kepala Sekolah',
        'nama'    => '................................',
        'nip'     => '................................',
    ],

    'pustakawan' => [
        'jabatan' => 'Pustakawan / Petugas Perpustakaan',
        'nama'    => '................................',
        'nip'     => '................................',
    ],
];