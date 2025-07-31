<?php

namespace app\Middleware;

class AdminMiddleware {
    public static function handle() {
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            header('Location: /');
            exit();
        }
    }
}
