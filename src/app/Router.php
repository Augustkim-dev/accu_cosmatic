<?php
namespace App;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void  { $this->add('GET', $path, $handler); }
    public function post(string $path, array $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, array $handler): void
    {
        // {id} → 명명 캡처그룹
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#',
            'handler' => $handler,
        ];
    }

    /** 매칭되면 컨트롤러 실행 후 true */
    public function dispatch(string $method, string $uri): bool
    {
        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            if (preg_match($r['regex'], $uri, $m)) {
                $params = [];
                foreach ($m as $k => $v) {
                    if (!is_int($k)) $params[$k] = $v;
                }
                [$class, $action] = $r['handler'];
                (new $class())->$action($params);
                return true;
            }
        }
        return false;
    }
}
