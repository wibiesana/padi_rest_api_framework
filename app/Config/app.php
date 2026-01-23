<?php

return [
    'app_name' => $_ENV['APP_NAME'] ?? getenv('APP_NAME') ?? 'Padi REST API',
    'app_env' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production',
    'app_debug' => $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? false,
    'app_url' => $_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'http://localhost',
    'timezone' => (!empty($_ENV['TIMEZONE']) ? $_ENV['TIMEZONE'] : (!empty(getenv('TIMEZONE')) ? getenv('TIMEZONE') : 'Asia/Jakarta')),
];
