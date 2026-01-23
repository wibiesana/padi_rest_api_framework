<?php

declare(strict_types=1);

namespace Core;

/**
 * Base Controller
 */
abstract class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
        $this->response = new Response();
    }

    /**
     * Validate request data
     */
    protected function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator($this->request->all(), $rules, $messages);

        if (!$validator->validate()) {
            $this->response->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return $validator->validated();
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $code = 200): void
    {
        $this->response->json($data, $code);
    }

    /**
     * Return success response
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): void
    {
        $this->response->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return error response
     */
    protected function error(string $message = 'Error', int $code = 400, mixed $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->response->json($response, $code);
    }

    /**
     * Return not found response
     */
    protected function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, 404);
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, 401);
    }

    /**
     * Return forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403);
    }
}
