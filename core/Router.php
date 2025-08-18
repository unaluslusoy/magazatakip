<?php

namespace core;
class Router {
    public $routes = [];

    private function getLogConfig() {
        static $cfg = null;
        if ($cfg !== null) { return $cfg; }
        $path = __DIR__ . '/../config/app_logging.php';
        $cfg = file_exists($path) ? (require $path) : ['enabled'=>true,'router'=>false,'slow'=>true];
        return $cfg;
    }

    private function logRequest($message) {
        // Log kontrolü: DEBUG açık ise her zaman; aksi halde config'e bak
        $cfg = $this->getLogConfig();
        if (!(defined('DEBUG_MODE') && DEBUG_MODE)) {
            if (empty($cfg['enabled']) || empty($cfg['router'])) { return; }
        }
        try {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
            $line = '[' . date('Y-m-d H:i:s') . '] ' . ($message ?? '') . "\n";
            @file_put_contents($logDir . '/router.log', $line, FILE_APPEND);
        } catch (\Throwable $e) {
            // no-op
        }
    }

    private function logSlow($message) {
        $cfg = $this->getLogConfig();
        if (empty($cfg['enabled']) || empty($cfg['slow'])) { return; }
        try {
            $logDir = __DIR__ . '/../logs/performance';
            if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
            $line = '[' . date('Y-m-d H:i:s') . '] ' . ($message ?? '') . "\n";
            @file_put_contents($logDir . '/access_slow.log', $line, FILE_APPEND);
        } catch (\Throwable $e) {
            // no-op
        }
    }

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
                    $currentSegment = $uriParts[$key] ?? '';

                    // {param} biçimli placeholders
                    if (preg_match('/^{\w+}$/', $part)) {
                        $parameters[] = $currentSegment;
                        continue;
                    }

                    // CodeIgniter tarzı placeholderlar: (:num), (:any)
                    if ($part === '(:num)') {
                        if (ctype_digit($currentSegment)) {
                            $parameters[] = $currentSegment;
                            continue;
                        }
                        $isMatch = false;
                        break;
                    }

                    if ($part === '(:any)') {
                        // herhangi bir segment
                        $parameters[] = $currentSegment;
                        continue;
                    }

                    // Birebir eşleşme
                    if ($part !== $currentSegment) {
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
        $start = microtime(true);
        $cleanUriForLog = trim(strtok($uri, '?'), '/');
        $this->logRequest("BEGIN {$method} /{$cleanUriForLog}");
        
        // CLI için varsayılan değerler
        if (php_sapi_name() === 'cli') {
            $uri = $uri ?: '/';
            $method = 'GET';
        }
        
        // PUT ve DELETE istekleri için HTTP_X_HTTP_METHOD_OVERRIDE header'ını kontrol et
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }
        
        // Query string'i çıkar ve normalize et
        $cleanUri = trim(strtok($uri, '?'), '/');
        
        // Admin route kontrolü - Güvenlik (başında 'admin' olan her path)
        if (strpos($cleanUri, 'admin') === 0) {
            // Admin route'ları için AdminMiddleware kontrolü
            if (!class_exists('app\\Middleware\\AdminMiddleware')) {
                require_once 'app/Middleware/AdminMiddleware.php';
            }
            \app\Middleware\AdminMiddleware::handle();

            // Admin POST/PUT/DELETE isteklerinde CSRF kontrolü
            if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
                if (!class_exists('app\\Middleware\\CsrfMiddleware')) {
                    require_once 'app/Middleware/CsrfMiddleware.php';
                }
                \app\Middleware\CsrfMiddleware::handle();
            }
        }

        // Kullanıcı alanı için middleware (public rotalar hariç)
        if (strpos($cleanUri, 'api/') !== 0 && strpos($cleanUri, 'admin') !== 0) {
            $publicRoutes = ['','auth/giris','auth/kayit'];
            if (!in_array($cleanUri, $publicRoutes, true)) {
                if (!class_exists('app\\Middleware\\UserMiddleware')) {
                    require_once 'app/Middleware/UserMiddleware.php';
                }
                \app\Middleware\UserMiddleware::handle();
            }
        }
        
        // CSRF kontrolü ileriki adımda admin formları için etkinleştirilecek

        // Genel erişim kontrolü (public olmayan rotalar için login zorunlu)
        if (!class_exists('core\\AuthManager')) {
            require_once __DIR__ . '/AuthManager.php';
        }
        $authManager = AuthManager::getInstance();
        $routeForAccess = '/' . $cleanUri;
        if ($routeForAccess === '/') {
            $routeForAccess = '/';
        }
        $access = $authManager->checkRouteAccess($routeForAccess);
        if (isset($access['allowed']) && $access['allowed'] === false) {
            $redirectUrl = $access['redirect'] ?? '/auth/giris';
            $this->logRequest("DENY {$method} /{$cleanUri} -> {$redirectUrl}");
            header('Location: ' . $redirectUrl);
            exit();
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
            $this->logRequest("CALL {$controllerName}@{$action}");
            
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
            $durMs = round((microtime(true) - $start) * 1000);
            $this->logRequest("END {$method} /{$cleanUri} {$durMs}ms");
            // Yavaş istekleri ayrı logla (DEBUG bağımsız)
            $slowThreshold = (int)(getenv('SLOW_LOG_MS') ?: 2000);
            if ($durMs >= $slowThreshold) {
                $this->logSlow("{$method} /{$cleanUri} {$durMs}ms");
            }
        } else {
            // Hata sayfasına yönlendir
            $this->logRequest("NOT_FOUND {$method} /{$cleanUri}");
            header("HTTP/1.0 404 Not Found");
            echo "404 - Sayfa Bulunamadı";
            exit();
        }
    }
}
