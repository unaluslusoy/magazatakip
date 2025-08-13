<?php
namespace app\Controllers\Kullanici;

use core\Controller;
use app\Middleware\UserMiddleware;

class AyarlarController extends Controller
{
    public function __construct()
    {
        // Sadece girişli kullanıcı
        UserMiddleware::handle();
    }

    public function index()
    {
        $this->view('kullanici/ayarlar/index');
    }
}


