<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

class CorsMiddleware
{
    public function handle(Request $request): void
    {
        // CORS headers are already set in Response class
        // This middleware can be used for additional CORS logic if needed

        if ($request->method() === 'OPTIONS') {
            $response = new Response();
            $response->status(200)->text('');
        }
    }
}
