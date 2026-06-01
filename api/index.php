<?php

// 1. Definisikan secara manual letak folder root Laravel kamu
$appRoot = __DIR__ . '/..';

// 2. Muat Autoloader Composer
require $appRoot . '/vendor/autoload.php';

// 3. Muat Aplikasi Laravel dan atur path dasarnya secara tegas
$app = require_all_once $appRoot . '/bootstrap/app.php';

// 4. Jalankan aplikasi melalui index public bawaan
require $appRoot . '/public/index.php';

/**
 * Fungsi pembantu untuk memastikan bootstrap/app.php dibaca dengan benar
 */
function require_all_once($file) {
    return require $file;
}