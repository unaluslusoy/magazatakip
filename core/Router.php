<?php

namespace core;
class Router {
    private $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    private function parseUri($uri) {
        // URI'den query string'i çıkar
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        return explode('/', $uri);
    }

    private function match($method, $uri) {
        $uriParts = $this->parseUri($uri);
        
        foreach ($this->routes[$method] as $route => $controller) {
            $routeParts = $this->parseUri($route);
            
            if (count($uriParts) === count($routeParts)) {
                $parameters = [];
                $isMatch = true;
                
                foreach ($routeParts as $key => $part) {
                    if (preg_match('/^{\w+}$/', $part)) {
                        $parameters[] = $uriParts[$key];
                    } elseif ($part !== $uriParts[$key]) {
                        $isMatch = false;
                        break;
                    }
                }
                
                if ($isMatch) {
                    return ['controller' => $controller, 'parameters' => $parameters];
                }
            }
        }
        
        return false;
    }

    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Query string'i çıkar
        $cleanUri = strtok($uri, '?');
        
        $match = $this->match($method, $cleanUri);

        if ($match) {
            $controllerAction = explode('@', $match['controller']);
            $controllerName = "app\\Controllers\\" . $controllerAction[0];
            $action = $controllerAction[1] ?? 'index';
            
            // Kontrol: Controller sınıfı var mı?
            if (!class_exists($controllerName)) {
                // Eğer TokenController bulunamazsa, sessiz bir şekilde yok say
                if (strpos($controllerName, 'TokenController') !== false) {
                    error_log("TokenController bulunamadı, göz ardı ediliyor.");
                    return;
                }

                error_log("Controller bulunamadı: " . $controllerName);
                header("HTTP/1.0 500 Internal Server Error");
                echo "Sunucu hatası: Controller bulunamadı";
                exit();
            }
            
            $controller = new $controllerName();
            
            // Kontrol: Action metodu var mı?
            if (!method_exists($controller, $action)) {
                error_log("Action metodu bulunamadı: " . $controllerName . "@" . $action);
                header("HTTP/1.0 500 Internal Server Error");
                echo "Sunucu hatası: Action metodu bulunamadı";
                exit();
            }
            
            // Parametreleri ve GET parametrelerini birleştir
            $allParameters = $match['parameters'];
            
            call_user_func_array([$controller, $action], [$allParameters]);
        } else {
            // Hata sayfasına yönlendir veya özel bir hata işleme mekanizması kullan
            header("HTTP/1.0 404 Not Found");
            echo "404 - Sayfa Bulunamadı";
            exit();
        }
    }
}
