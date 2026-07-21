<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    private function map(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim((string) config('url', ''), '/');
        // Windows/Apache: o caminho pode chegar com casing diferente (ex.: /ticket/public)
        if ($base !== '' && strncasecmp($path, $base, strlen($base)) === 0) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        // Pedidos diretos a .../index.php ou .../index.php/rota
        if (strcasecmp($path, '/index.php') === 0) {
            $path = '/';
        } elseif (preg_match('#^/index\.php(/.*)?$#i', $path, $m)) {
            $path = $m[1] ?? '/';
        }
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $controller = new $class();
                call_user_func_array([$controller, $action], $params);
                return;
            }
            call_user_func_array($handler, $params);
            return;
        }

        http_response_code(404);
        view('pages/404', ['title' => 'Página não encontrada']);
    }
}
