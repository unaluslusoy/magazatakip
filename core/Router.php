<?php

namespace core;
class Router {
    public $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
        // Debug: Route ekleme
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $controllerStr = is_callable($controller) ? 'Closure' : $controller;
            error_log("Route added: GET $uri -> $controllerStr");
        }
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    public function put($uri, $controller) {
        $this->routes['PUT'][$uri] = $controller;
    }

    public function delete($uri, $controller) {
        $this->routes['DELETE'][$uri] = $controller;
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
        
        // Method için route'lar var mı kontrol et
        if (!isset($this->routes[$method]) || empty($this->routes[$method])) {
            return false;
        }
        
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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // CLI için varsayılan değerler
        if (php_sapi_name() === 'cli') {
            $uri = $uri ?: '/';
            $method = 'GET';
        }
        
        // PUT ve DELETE istekleri için HTTP_X_HTTP_METHOD_OVERRIDE header'ını kontrol et
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }
        
        // Query string'i çıkar
        $cleanUri = strtok($uri, '?');
        
        // Admin route kontrolü - Güvenlik
        if (strpos($cleanUri, 'admin') === 0) {
            // Admin route'ları için AdminMiddleware kontrolü
            if (!class_exists('app\\Middleware\\AdminMiddleware')) {
                require_once 'app/Middleware/AdminMiddleware.php';
            }
            \app\Middleware\AdminMiddleware::handle();
        }
        
        // Debug: Route'ları logla (sadece geliştirme ortamında)
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Router Debug - Method: " . $method . ", URI: " . $cleanUri);
        }
        
        $match = $this->match($method, $cleanUri);

        // Debug: Match sonucu
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Router Match: " . ($match ? 'true' : 'false') . " for URI: $cleanUri");
        }
        


        if ($match) {
            $controller = $match['controller'];
            $parameters = $match['parameters'];
            
            if (is_callable($controller)) {
                call_user_func_array($controller, $parameters);
                return;
            }
            
            $controllerAction = explode('@', $controller);
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
            $allParameters = $parameters;
            
            call_user_func_array([$controller, $action], $allParameters);
        } else {
            // Hata sayfasına yönlendir
            header("HTTP/1.0 404 Not Found");
            echo "404 - Sayfa Bulunamadı";
            exit();
        }
    }
}
