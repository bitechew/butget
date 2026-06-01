<?php

// Paksa pemuatan konfigurasi dari folder root yang benar sebelum public dipanggil
$_ENV['APP_BASE_PATH'] = __DIR__ . '/..';

require __DIR__ . '/../public/index.php';