<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

class Router
{
    protected array $routes = [];
    protected array $params = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function add(string $route, array $params = []): void
    {
        // convert route to regexp
        $route = preg_replace('/\//', '\\/', $route);

        // convert variables: {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regexp {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }



    public function match(string $url): bool
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;

                return true;
            }
        }
        return false;
    }

    public function dispatch(string $url): void
    {
        $url = $this->removeQueryStringVariables($url);

        if (!$this->match($url)) {
            throw new \Exception('No route matched', 404);
        }

        $controller = $this->params['controller'];
        $controller = $this->convertToStudlyCaps($controller);
        $controller = $this->getNamespace() . $controller;

        if (!class_exists($controller)) {
            throw new \Exception("Controller class $controller not found");
        }

        $controller_object = new $controller($this->params);

        $action = $this->params['action'];
        $action = $this->convertToCamelCase($action);

        if (preg_match('/action$/i', $action) == 0) {
            $controller_object->$action();
        } else {
            throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method");
        }
    }

    protected function convertToCamelCase(string $string): string
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    protected function convertToStudlyCaps(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    protected function removeQueryStringVariables(string $url): string
    {
        if ($url === '') {
            return $url;
        }

        $parts = explode('&', $url, 2);
        $url = str_contains($parts[0], '=') ? '' : $parts[0];

        return $url;
    }

    public function getNamespace(): string
    {
        $namespace = 'Ilyamur\\PhpMvc\\App\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
