<?php

// 1. Tentukan folder utama aplikasi (mundur 1 tingkat dari folder api)
$appRoot = __DIR__ . '/..';

// 2. Langsung panggil file public bawaan Laravel
require $appRoot . '/public/index.php';