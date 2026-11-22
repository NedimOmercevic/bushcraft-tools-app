<?php

declare(strict_types=1);

// Router for PHP's local development server, launched from the project root.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/backend' || str_starts_with($path, '/backend/')) {
    if (in_array($path, ['/backend/openapi.json', '/backend/swagger/index.html'], true)) {
        return false;
    }
    if ($path === '/backend/swagger' || $path === '/backend/swagger/') {
        header('Location: /backend/swagger/index.html');
        return true;
    }

    $_SERVER['SCRIPT_NAME'] = '/backend/api-entry.php';
    require __DIR__ . '/backend/api-entry.php';
    return true;
}

if ($path === '/' || $path === '/frontend' || $path === '/frontend/') {
    header('Location: /frontend/index.html');
    return true;
}

$file = realpath(__DIR__ . rawurldecode($path));
if ($file !== false && is_file($file) && str_starts_with($file, __DIR__ . '/frontend/')) {
    return false;
}

http_response_code(404);
return true;
