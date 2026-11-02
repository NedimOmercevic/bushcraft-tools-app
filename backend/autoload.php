<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\Dao\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = __DIR__ . '/dao/' . $relative . '.php';
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
