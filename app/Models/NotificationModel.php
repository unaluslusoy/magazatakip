<?php
namespace app\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'bildirimler';
    protected $primaryKey = 'id';
    protected $allowedFields = ['alici_tipi', 'baslik', 'mesaj', 'gonderim_tarihi'];

    public function getPaginatedNotifications($page = 1, $perPage = 10, $dateFilter = 'all')
    {
        $this->builder()->select('*');

        switch ($dateFilter) {
            case 'today':
                $this->builder()->where('DATE(gonderim_tarihi)', date('Y-m-d'));
                break;
            case 'yesterday':
                $this->builder()->where('DATE(gonderim_tarihi)', date('Y-m-d', strtotime('-1 day')));
                break;
            case 'last_week':
                $this->builder()->where('gonderim_tarihi >=', date('Y-m-d', strtotime('-1 week')))
                    ->where('gonderim_tarihi <', date('Y-m-d'));
                break;
        }

        return $this->paginate($perPage, 'default', $page);
    }

    public function getNotificationCounts()
    {
        $counts = [
            'today' => $this->where('DATE(gonderim_tarihi)', date('Y-m-d'))->countAllResults(),
            'yesterday' => $this->where('DATE(gonderim_tarihi)', date('Y-m-d', strtotime('-1 day')))->countAllResults(),
            'last_week' => $this->where('gonderim_tarihi >=', date('Y-m-d', strtotime('-1 week')))
                ->where('gonderim_tarihi <', date('Y-m-d'))
                ->countAllResults()
        ];

        return $counts;
    }
}
