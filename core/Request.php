<?php

declare(strict_types=1);

namespace Core;

class Request
{
    private array $params = [];
    private array $query = [];
    private array $body = [];
    private array $files = [];
    private array $headers = [];
    private string $method;
    private string $uri;
    public ?object $user = null;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query = $_GET;
        $this->files = $_FILES;
        $this->parseHeaders();
        $this->parseBody();
    }

    /**
     * Parse request headers
     */
    private function parseHeaders(): void
    {
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $this->headers[$header] = $value;
            }
        }

        // Add content type if available
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $this->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
    }

    /**
     * Parse request body
     */
    private function parseBody(): void
    {
        $contentType = $this->header('Content-Type', '');

        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $this->body = json_decode($input, true) ?? [];
        } elseif ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'PATCH') {
            $this->body = $_POST;
        }

        // Sanitize all inputs
        $this->sanitize($this->query);
        $this->sanitize($this->body);
    }

    /**
     * Sanitize input data to prevent XSS
     */
    private function sanitize(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->sanitize($value);
            } elseif (is_string($value)) {
                $value = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
            }
        }
    }

    /**
     * Get request method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get all input data (sanitized)
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->params);
    }

    /**
     * Get raw input data (unsanitized)
     */
    public function raw(): array
    {
        $contentType = $this->header('Content-Type', '');
        $rawQuery = $_GET;
        $rawBody = [];

        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $rawBody = json_decode($input, true) ?? [];
        } else {
            $rawBody = $_POST;
        }

        return array_merge($rawQuery, $rawBody, $this->params);
    }

    /**
     * Get specific input value
     */
    public function input(string $key, mixed $default = null)
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Get only specified keys
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Get all except specified keys
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Check if input has key
     */
    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    /**
     * Get query parameter
     */
    public function query(?string $key = null, mixed $default = null)
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Get header value
     */
    public function header(string $key, mixed $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get all files
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Set route parameters
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Get route parameter
     */
    public function param(string $key, mixed $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Get bearer token
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization', '');

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if request is JSON
     */
    public function isJson(): bool
    {
        return strpos($this->header('Content-Type', ''), 'application/json') !== false;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
