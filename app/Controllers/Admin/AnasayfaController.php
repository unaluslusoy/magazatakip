<?php

namespace app\Controllers\Admin;
use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Middleware\AuthMiddleware;

use app\Models\Kullanici;
use app\Models\Magaza;
use app\Models\Personel;
use app\Models\Gorev;
use app\Models\Istek;

class AnasayfaController extends Controller {
    public function __construct() {

        AuthMiddleware::handle();
    }

    public function index() {
        $kullaniciModel = new Kullanici();
        $magazaModel = new Magaza();
        $personelModel = new Personel();
        $gorevModel = new Gorev();
        $IsModel = new Istek();

        $totalUsers = $kullaniciModel->getTotalCount();
        $totalStores = $magazaModel->getTotalCount();
        $totalEmployees = $personelModel->getTotalCount();
        $pendingTasks = $gorevModel->getPendingCount();
        $IsTasks = $IsModel->getIsEmriCount();

        $this->view('admin/anasayfa', [
            'totalUsers' => $totalUsers,
            'totalStores' => $totalStores,
            'totalEmployees' => $totalEmployees,
            'pendingTasks' => $pendingTasks,
            'IsTasks' => $IsTasks
        ]);
    }
}
