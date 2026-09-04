<?php

declare(strict_types=1);

namespace Core;

/**
 * Roteador simples: casa método HTTP + padrão de caminho com {parametros}.
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:mixed}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function get(string $p, callable|array $h): void    { $this->add('GET', $p, $h); }
    public function post(string $p, callable|array $h): void   { $this->add('POST', $p, $h); }
    public function put(string $p, callable|array $h): void    { $this->add('PUT', $p, $h); }
    public function patch(string $p, callable|array $h): void  { $this->add('PATCH', $p, $h); }
    public function delete(string $p, callable|array $h): void { $this->add('DELETE', $p, $h); }

    /**
     * Permite ?_method=PUT/PATCH/DELETE em formulários HTML.
     */
    private function resolveMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }
        return $method;
    }

    public function dispatch(string $uri): void
    {
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        $method = $this->resolveMethod();
        $allowed = [];

        foreach ($this->routes as $route) {
            $regex = '#^' . preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $route['pattern']) . '$#';
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowed[] = $route['method'];
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $this->invoke($route['handler'], $params);
            return;
        }

        if ($allowed !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowed)));
            echo 'Método não permitido.';
            return;
        }

        http_response_code(404);
        $this->renderNotFound();
    }

    private function invoke(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $action] = $handler;
            $controller = new $class();
            $controller->$action(...array_values($params));
            return;
        }
        $handler(...array_values($params));
    }

    private function renderNotFound(): void
    {
        try {
            \Core\View::render('errors/404', ['title' => 'Página não encontrada'], 'layouts/site');
        } catch (\Throwable) {
            echo '<h1>404 — Página não encontrada</h1>';
        }
    }
}
