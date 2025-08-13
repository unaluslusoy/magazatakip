<?php
namespace app\Models\System;

use core\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    /** @var bool */
    private static $tableEnsured = false;

    public function __construct()
    {
        parent::__construct();
        if (!self::$tableEnsured) {
            $this->ensureTableExists();
            self::$tableEnsured = true;
        }
    }

    public function log($userId, $action, $entityType, $entityId = null, $meta = [])
    {
        $data = [
            'user_id' => (int)$userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta_json' => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        try {
            return (bool)$this->create($data);
        } catch (\Throwable $e) {
            error_log('ActivityLog::log error: ' . $e->getMessage());
            return false;
        }
    }

    public function latest($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function ensureTableExists(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
              id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              user_id BIGINT UNSIGNED NOT NULL,
              action VARCHAR(32) NOT NULL,
              entity_type VARCHAR(64) NOT NULL,
              entity_id BIGINT NULL,
              meta_json JSON NULL,
              ip_address VARCHAR(45) NULL,
              user_agent VARCHAR(255) NULL,
              created_at DATETIME NOT NULL,
              INDEX idx_user_created (user_id, created_at),
              INDEX idx_entity (entity_type, entity_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($sql);
        } catch (\Throwable $e) {
            error_log('ActivityLog::ensureTableExists error: ' . $e->getMessage());
        }
    }
}


