<?php

return [
    'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
    'port' => $_ENV['MAIL_PORT'] ?? 2525,
    'username' => $_ENV['MAIL_USERNAME'] ?? null,
    'password' => $_ENV['MAIL_PASSWORD'] ?? null,
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? $_ENV['APP_NAME'] ?? 'Padi REST API',
];
