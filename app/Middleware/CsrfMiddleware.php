<?php

namespace app\Middleware;

class CsrfMiddleware
{
    public static function handle()
    {
        if (php_sapi_name() === 'cli') {
            return; // CLI testlerinde atla
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        // Sadece state-changing isteklerde zorunlu
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST','PUT','PATCH','DELETE'], true)) {
            // GET/HEAD/OPTIONS vs. serbest; token üretimini yine de yapalım
            if (!isset($_SESSION['_csrf'])) { $_SESSION['_csrf'] = bin2hex(random_bytes(16)); }
            return;
        }

        // AJAX veya form token kontrolü (header veya form field)
        $tokenFromHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        $tokenFromPost = $_POST['_csrf'] ?? null;
        $sessionToken = $_SESSION['_csrf'] ?? null;

        // Session token yoksa üret
        if (!$sessionToken) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
            $sessionToken = $_SESSION['_csrf'];
        }

        // Token zorunlu
        if (!$tokenFromHeader && !$tokenFromPost) {
            self::deny('CSRF token gönderilmedi');
        }

        if ($tokenFromHeader && hash_equals($sessionToken, $tokenFromHeader)) {
            return; // Geçerli
        }

        if ($tokenFromPost && hash_equals($sessionToken, $tokenFromPost)) {
            return; // Geçerli
        }

        self::deny('CSRF doğrulaması başarısız');
    }

    public static function generateToken(): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $token = bin2hex(random_bytes(16));
        $_SESSION['_csrf'] = $token;
        return $token;
    }

    private static function deny($message)
    {
        http_response_code(419);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
        exit();
    }
}


