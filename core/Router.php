<?php

declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    /**
     * Add GET route
     */
    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add POST route
     */
    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add PATCH route
     */
    public function patch(string $path, $handler): self
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add OPTIONS route
     */
    public function options(string $path, $handler): self
    {
        return $this->addRoute('OPTIONS', $path, $handler);
    }

    /**
     * Add route for any method
     */
    public function any(string $path, $handler): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }

    /**
     * Create versioned route group
     */
    public function version(string $v, callable $callback): void
    {
        $this->group(['prefix' => 'v' . ltrim($v, 'v')], $callback);
    }

    /**
     * Create route group with prefix
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        if (isset($attributes['prefix'])) {
            $this->prefix .= '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware'])
                ? $attributes['middleware']
                : [$attributes['middleware']];
            $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    /**
     * Add middleware to route
     */
    public function middleware($middleware): self
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        $lastRouteKey = array_key_last($this->routes);

        if ($lastRouteKey !== null) {
            $this->routes[$lastRouteKey]['middlewares'] = array_merge(
                $this->routes[$lastRouteKey]['middlewares'],
                $middlewares
            );
        }

        return $this;
    }

    /**
     * Add route
     */
    private function addRoute(string $method, string $path, $handler): self
    {
        $path = '/' . trim($this->prefix . '/' . trim($path, '/'), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        // Pre-compile regex for performance
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'regex' => $regex,
            'handler' => $handler,
            'middlewares' => $this->groupMiddlewares
        ];

        return $this;
    }

    /**
     * Dispatch request
     */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = rtrim($request->uri(), '/') ?: '/';

        // Handle OPTIONS request for CORS
        if ($method === 'OPTIONS') {
            $response = new Response();
            $response->status(200)->text('');
            return;
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $this->executeMiddleware($middleware, $request);
                }

                // Execute handler
                $this->executeHandler($route['handler'], $request);
                return;
            }
        }

        // Route not found
        $response = new Response();
        $response->json([
            'success' => false,
            'message' => 'Route not found'
        ], 404);
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware($middleware, Request $request): void
    {
        if (is_string($middleware)) {
            $middlewareClass = "App\\Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $instance = new $middlewareClass();
                $instance->handle($request);
            }
        } elseif (is_callable($middleware)) {
            $middleware($request);
        }
    }

    /**
     * Execute route handler
     */
    private function executeHandler($handler, Request $request): void
    {
        if (is_callable($handler)) {
            $handler($request);
        } elseif (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";

            if (class_exists($controllerClass)) {
                $instance = new $controllerClass($request);
                if (method_exists($instance, $method)) {
                    $instance->$method();
                } else {
                    throw new \Exception("Method {$method} not found in {$controllerClass}");
                }
            } else {
                throw new \Exception("Controller {$controllerClass} not found");
            }
        }
    }
}
