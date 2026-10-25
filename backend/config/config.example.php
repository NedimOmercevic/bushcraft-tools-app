<?php
/**
 * Environment configuration template.
 * Copy this file to config.php and fill in local values.
 * Do not commit config.php (see .gitignore).
 */
return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'bushcraft_tools',
        'user' => 'your_db_user',
        'pass' => 'your_db_password',
        'charset' => 'utf8mb4',
    ],
    'jwt' => [
        'secret' => 'change_this_to_a_long_random_secret',
        'algorithm' => 'HS256',
        'expiration' => 3600,
    ],
    'openweather' => [
        'api_key' => 'your_openweather_api_key',
        'base_url' => 'https://api.openweathermap.org/data/2.5',
    ],
];
