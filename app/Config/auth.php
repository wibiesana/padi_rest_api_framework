<?php

return [
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production',
    'jwt_algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
    'jwt_expiration' => (int)($_ENV['JWT_EXPIRATION'] ?? 3600), // 1 hour in seconds
    'jwt_refresh_expiration' => (int)($_ENV['JWT_REFRESH_EXPIRATION'] ?? 604800), // 7 days in seconds
];
