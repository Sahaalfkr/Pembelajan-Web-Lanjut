<?php

if (!function_exists('hitung_ppn')) {
    function hitung_ppn(float $total_harga): float
    {
        return round($total_harga * 0.11, 2);
    }
}

if (!function_exists('hitung_biaya_admin')) {
    function hitung_biaya_admin(float $total_harga): float
    {
        if ($total_harga <= 20000000) {
            return round($total_harga * 0.006, 2);
        }

        if ($total_harga <= 40000000) {
            return round($total_harga * 0.008, 2);
        }

        return round($total_harga * 0.01, 2);
    }
}

if (!function_exists('hitung_diskon_voucher')) {
    function hitung_diskon_voucher(float $total_harga, ?string $voucher_code): float
    {
        $voucher_code = strtoupper(trim((string) $voucher_code));
        $rates = [
            'FLASH10'  => 0.10,
            'FLASH15'  => 0.15,
            'MEMBER20' => 0.20,
        ];

        $rate = $rates[$voucher_code] ?? 0;

        return round($total_harga * $rate, 2);
    }
}
