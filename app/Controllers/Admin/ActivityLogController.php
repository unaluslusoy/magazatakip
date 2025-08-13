<?php
namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Models\System\ActivityLog;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        AdminMiddleware::handle();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 25;
        $logs = [];

        try {
            $model = new ActivityLog();
            // Tablo yoksa oluşturmaya zorla (constructor zaten dener)
            $logs = $model->latest($page, $perPage);
        } catch (\Throwable $e) {
            error_log('ActivityLogController@index error: ' . $e->getMessage());
            // View tarafında kullanıcıya nazik bir mesaj gösterebiliriz
            $_SESSION['message'] = 'Aktivite logları yüklenemedi.';
            $_SESSION['message_type'] = 'warning';
        }

        $this->view('admin/activity_logs/index', [
            'logs' => $logs,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }
}


