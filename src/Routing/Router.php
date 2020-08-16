<?php


namespace Gotee\Routing;


use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    protected array $routes = [];

    protected array $groups = [];

    protected ?Dispatcher $dispatcher = null;


    public function __construct()
    {
        
    }

    public function group(array $attributes, callable $fn)
    {
        $lastGroup = $this->getArrayLast($this->groups);
        $attrs = [
            'name' => $this->getInheritedName($lastGroup, $attributes),
            'namespace' => $this->getInheritedNamespace($lastGroup, $attributes),
            'middleware' => $this->getInheritedMiddleware($lastGroup, $attributes),
            'prefix' => $this->getInheritedPrefix($lastGroup, $attributes),
        ];

        array_push($this->groups, $attrs);
        $fn($this);
        array_pop($this->groups);
    }

    public function get(string $uri, $handler)
    {
        $this->newRoute('get', $uri, $handler);

        return $this;
    }

    public function post(string $uri, $handler)
    {

    }

    public function put(string $uri, $handler)
    {

    }

    public function patch(string $uri, $handler)
    {

    }

    public function delete(string $uri, $handler)
    {

    }

    public function setMiddleware($middleware)
    {
        $lastRoute = $this->getArrayLast($this->routes);
        $middlewares = $lastRoute['middleware'] ?? [];
        $middlewares[] = $middleware;
        $lastRoute['middleware'] = $middlewares;
        $this->mergeArrayLastElement($this->routes, $lastRoute);

        return $this;
    }

    public function setName($name)
    {
        $lastGroup = $this->getArrayLast($this->groups) ?? [];
        $groupName = $lastGroup['name'] ?? '';

        $this->setArrayLast($this->routes, 'name', $groupName . $name);

        return $this;
    }

    public function newRoute(string $method, string $uri, $handler) : void
    {
        $lastGroup = $this->getArrayLast($this->groups) ?? [];
        $groupPrefix = $lastGroup['prefix'] ?? '';
        $middleware = $lastGroup['middleware'] ?? [];

        $method = strtoupper($method);
        array_push($this->routes, [
            'method' => $method,
            'uri' => $groupPrefix . $uri,
            'handler' => $handler,
            'middleware' => $middleware,
        ]);

    }

    public function getRoutes() : array 
    {
        return $this->routes;
    }

    public function register()
    {
        $this->dispatcher = simpleDispatcher(function(RouteCollector $router) {
            foreach ($this->getRoutes() as $route) {
                $router->addRoute($route['method'], $route['uri'], $route);
            }
        });
    }

    public function resolve()
    {
        if (is_null($this->dispatcher)) return false;
        // TODO: need to throw meaningful exception here

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        $routeHandlerInfo = $this->processRoute($routeInfo);
        dd($routeHandlerInfo);

        return $routeHandlerInfo;
    }

    protected function processRoute($route) : ?array
    {
        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $route[1];
                // ... 405 Method Not Allowed
                break;
            case Dispatcher::FOUND:
                $data['handler'] = $route[1];
                $data['params'] = $route[2];
                // ... call $handler with $vars
                return $data;
        }

        return null;
    }

    public function getDispatcher() : ?Dispatcher
    {
        return $this->dispatcher;
    }


    public function mergeArrayLastElement(array &$array, array $data) : ?array
    {
        if (!count($array)) return null;
        end($array);
        $key  = key($array);
        $last = &$array[$key];
        $last = array_merge($last, $data);
        $array[$key] = $last;
        reset($array);

        return $last;
    }


    public function getArrayLast(array $array) : ?array
    {
        if (!count($array)) return null;
        end($array);
        $key  = key($array);
        return $array[$key];
    }


    public function setArrayLast(array &$array, string $name, $value) : ?array
    {
        if (!count($array)) return null;
        end($array);
        $key  = key($array);
        $last = &$array[$key];
        $last[$name] = $value;
        reset($array);

        return $last;
    }

    protected function getInheritedName($old, $new)
    {
        return ($old['name'] ?? '') . ($new['name'] ?? '');
    }

    protected function getInheritedPrefix($old, $new)
    {
        return ($old['prefix'] ?? '') . ($new['prefix'] ?? '');
    }

    protected function getInheritedNamespace($old, $new)
    {
        return ($old['namespace'] ?? '') . ($new['namespace'] ?? '');
    }

    protected function getInheritedMiddleware($old, $new)
    {
        $oldMiddleware = $old['middleware'] ?? [];
        $newMiddleware = $new['middleware'] ?? [];

        return array_merge($oldMiddleware, $newMiddleware);
    }
}