<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $parts = explode('\\', $class);
    if (count($parts) !== 3 || $parts[0] !== 'App' || !in_array($parts[1], ['Dao', 'Services'], true)) {
        return;
    }

    $file = __DIR__ . '/' . strtolower($parts[1]) . '/' . $parts[2] . '.php';
    if (is_readable($file)) {
        require $file;
    }
});

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($composerAutoload)) {
    require $composerAutoload;
} else {
    require __DIR__ . '/lib/flightphp/flight/autoload.php';
}
