<?php
namespace app\Models;

use core\Model;

class Bildirim extends Model
{
    protected $table = 'bildirimler';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'kullanici_id', 'hedef_kullanici_id', 'baslik', 'mesaj', 'url', 'icon',
        'alici_tipi', 'gonderim_tarihi', 'okunma_tarihi', 'okundu', 'durum',
        'gonderim_kanali', 'oncelik', 'etiketler', 'ekstra_veri'
    ];

    public function getPaginatedNotifications($page = 1, $perPage = 10, $dateFilter = 'all', $aliciFilter = 'all', $durumFilter = 'all', $searchTerm = ''): array
    {
        $offset = ($page - 1) * $perPage;

        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($dateFilter != 'all') {
            switch($dateFilter) {
                case 'today':
                    $query .= " AND DATE(gonderim_tarihi) = CURDATE()";
                    $countQuery .= " AND DATE(gonderim_tarihi) = CURDATE()";
                    break;
                case 'yesterday':
                    $query .= " AND DATE(gonderim_tarihi) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                    $countQuery .= " AND DATE(gonderim_tarihi) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                    break;
                case 'last_week':
                    $query .= " AND gonderim_tarihi >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND gonderim_tarihi < CURDATE()";
                    $countQuery .= " AND gonderim_tarihi >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND gonderim_tarihi < CURDATE()";
                    break;
            }
        }

        if ($aliciFilter != 'all') {
            $query .= " AND alici_tipi = :alici_tipi";
            $countQuery .= " AND alici_tipi = :alici_tipi";
            $params[':alici_tipi'] = $aliciFilter;
        }

        if ($durumFilter != 'all') {
            $query .= " AND durum = :durum";
            $countQuery .= " AND durum = :durum";
            $params[':durum'] = $durumFilter;
        }

        if ($searchTerm != '') {
            $query .= " AND (baslik LIKE :search OR mesaj LIKE :search)";
            $countQuery .= " AND (baslik LIKE :search OR mesaj LIKE :search)";
            $params[':search'] = "%$searchTerm%";
        }

        // LIMIT ve OFFSET için bindParam kullanamayız, bunları direkt olarak query string'e ekliyoruz.
        $query .= " ORDER BY gonderim_tarihi DESC LIMIT $offset, $perPage";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $data,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'lastPage' => ceil($total / $perPage)
        ];
    }

    public function getNotificationCounts(): array
    {
        $query = "SELECT 
            SUM(CASE WHEN DATE(gonderim_tarihi) = CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN DATE(gonderim_tarihi) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as yesterday,
            SUM(CASE WHEN gonderim_tarihi >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND gonderim_tarihi < CURDATE() THEN 1 ELSE 0 END) as last_week
        FROM {$this->table}";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        $values = array_values($data);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function getBildirimById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateBildirim($id, $data): bool
    {
        $setClause = implode(', ', array_map(function($field) {
            return "$field = :$field";
        }, array_keys($data)));

        $query = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function deleteBildirim($id): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function markAsRead($id): bool
    {
        $query = "UPDATE {$this->table} SET okundu = 1, okunma_tarihi = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
}