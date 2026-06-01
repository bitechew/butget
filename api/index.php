<?php

// Ensure safe APP_*_CACHE and APP_BASE_PATH values before Laravel boots on Vercel.
$basePath = dirname(__DIR__);
$defaults = [
	'APP_CONFIG_CACHE' => 'cache/config.php',
	'APP_ROUTES_CACHE' => 'cache/routes-v7.php',
	'APP_EVENTS_CACHE' => 'cache/events.php',
	'APP_SERVICES_CACHE' => 'cache/services.php',
	'APP_PACKAGES_CACHE' => 'cache/packages.php',
];

foreach ($defaults as $key => $default) {
	$val = getenv($key);

	if ($val === false || $val === '' || (is_string($val) && is_dir($val))) {
		putenv("{$key}={$default}");
		$_ENV[$key] = $default;
		$_SERVER[$key] = $default;
	}
}

if (getenv('APP_BASE_PATH') === false || getenv('APP_BASE_PATH') === '' || (is_string(getenv('APP_BASE_PATH')) && is_dir(getenv('APP_BASE_PATH')))) {
	putenv("APP_BASE_PATH={$basePath}");
	$_ENV['APP_BASE_PATH'] = $basePath;
	$_SERVER['APP_BASE_PATH'] = $basePath;
}

// Mengarahkan Vercel untuk membaca index utama Laravel
require __DIR__ . '/../public/index.php';