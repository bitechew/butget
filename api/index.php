<?php

// 1. Deteksi otomatis lokasi file bootstrap Laravel
$laravelIndex = __DIR__ . '/../public/index.php';

if (!file_exists($laravelIndex)) {
    // Jika path salah, tampilkan pesan diagnosis agar kita tahu Vercel mencarinya di mana
    die("Eror Jalur: File Laravel tidak ditemukan di: " . realpath(__DIR__ . '/../') . '/public/index.php');
}

// 2. Bersihkan config cache secara paksa langsung dari memori runtime
$cacheFile = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($cacheFile)) {
    @unlink($cacheFile);
}

// 3. Muat aplikasi Laravel jika jalur sudah aman
require $laravelIndex;