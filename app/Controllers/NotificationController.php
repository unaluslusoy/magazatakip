<?php
namespace app\Controllers;

use app\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $page = $this->request->getGet('page', 1);
        $perPage = $this->request->getGet('per_page', 10);
        $dateFilter = $this->request->getGet('date_filter', 'all');

        $data = [
            'title' => 'Gönderilen Bildirimler',
            'link' => 'Bildirimler',
            'page' => $page,
            'perPage' => $perPage,
            'dateFilter' => $dateFilter,
            'bildirimler' => $this->notificationModel->getPaginatedNotifications($page, $perPage, $dateFilter),
            'pager' => $this->notificationModel->pager
        ];

        $this->view('admin/onesignal/bildirim_listesi', $data);
    }
}
