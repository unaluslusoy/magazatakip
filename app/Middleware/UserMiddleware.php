<?php

namespace app\Middleware;

use core\AuthManager;

class UserMiddleware
{
    public static function handle()
    {
        $auth = AuthManager::getInstance();
        $result = $auth->authenticate();

        if (!$result['authenticated']) {
            header('Location: /auth/giris');
            exit();
        }

        // Admin kullanıcıların kullanıcı alanına girmesini engelle
        if ($auth->isAdmin()) {
            header('Location: /admin');
            exit();
        }

        $auth->updateActivity();
    }
}


