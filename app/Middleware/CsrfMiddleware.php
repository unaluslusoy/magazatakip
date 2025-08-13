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

        // AJAX veya form token kontrolü (header veya form field)
        $tokenFromHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        $tokenFromPost = $_POST['_csrf'] ?? null;
        $sessionToken = $_SESSION['_csrf'] ?? null;

        // Session token yoksa üret (breakage önleme: strict enforcement değil)
        if (!$sessionToken) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
            $sessionToken = $_SESSION['_csrf'];
        }

        // Eğer gelen istekte token yoksa (mevcut formlar henüz eklenmemiş olabilir) şimdilik izin ver ve logla
        if (!$tokenFromHeader && !$tokenFromPost) {
            error_log('CSRF notice: Token gönderilmedi, geçici olarak izin verildi: ' . ($_SERVER['REQUEST_URI'] ?? ''));
            return;
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


