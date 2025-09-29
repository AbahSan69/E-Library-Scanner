<?php

if (! function_exists('generateEAN13')) {
    function getEANBase($id)
    {
        return '899' . str_pad($id, 9, '0', STR_PAD_LEFT); // 12 digit
    }
    function generateEAN13($id)
    {
        // 12 digit awal = prefix (899) + id buku dipad ke 9 digit
        $base = '899' . str_pad($id, 9, '0', STR_PAD_LEFT);

        // Hitung checksum
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $base[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        $checkDigit = (10 - ($sum % 10)) % 10;

        return $base . $checkDigit; // total 13 digit
    }
}
