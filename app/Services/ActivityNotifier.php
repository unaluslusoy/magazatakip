<?php

namespace app\Services;

use app\Models\System\ActivityLog;
use app\Models\Bildirim;
use app\Models\Kullanici;

class ActivityNotifier
{
    public function recordAndNotify(
        int $userId,
        string $action,           // create | update | delete
        string $entityType,       // ciro | gider | is_emri
        ?int $entityId = null,
        array $meta = []
    ): void {
        // 1) Aktivite logla (best-effort)
        try {
            (new ActivityLog())->log($userId, $action, $entityType, $entityId, $meta);
        } catch (\Throwable $t) {
            error_log('ActivityNotifier log error: ' . $t->getMessage());
        }

        // 2) Bildirim başlığı/mesajı
        [$title, $message] = $this->buildNotificationContent($userId, $action, $entityType, $entityId);

        // 3) Adminlere DB bildirimi oluştur
        try {
            // Admin bildirimleri yönetici bildirim listesine yönlensin
            (new Bildirim())->createForAdmin($title, $message, '/admin/bildirimler', $userId);
        } catch (\Throwable $t) {
            error_log('ActivityNotifier DB notify error: ' . $t->getMessage());
        }

        // 4) Adminlere push gönderimi
        try {
            $kullaniciModel = new Kullanici();
            $admins = $kullaniciModel->getAll();
            $service = new BildirimService();
            foreach ($admins as $admin) {
                if (!empty($admin['yonetici'])) {
                    // İşlemi yapan kişi admin ise kendisine bildirim gönderme
                    if (!empty($admin['id']) && (int)$admin['id'] === (int)$userId) {
                        continue;
                    }
                    $service->tekBildirimGonder(
                        $admin,
                        $title,
                        $message,
                        'web',
                        '/admin/bildirimler'
                    );
                }
            }
        } catch (\Throwable $t) {
            error_log('ActivityNotifier push notify error: ' . $t->getMessage());
        }

        // 5) İlgili son kullanıcıya bildirim (iş emri vs.)
        try {
            if ($entityType === 'is_emri' && $entityId) {
                // istek sahibi kullanıcıyı DB'den getir
                $db = (new \core\Database())->getConnection();
                $stmt = $db->prepare('SELECT kullanici_id, baslik FROM istekler WHERE id = :id');
                $stmt->execute([':id' => $entityId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row && !empty($row['kullanici_id'])) {
                    $userIdToNotify = (int)$row['kullanici_id'];
                    $actor = (new Kullanici())->get($userId);
                    $isActorAdmin = !empty($actor) && !empty($actor['yonetici']);
                    // Kullanıcı kendi kaydı oluşturduğunda bildirim gönderme. Yalnızca admin güncellemelerinde bildir.
                    if ($isActorAdmin && in_array($action, ['update', 'delete'], true)) {
                        $titleUser = $action === 'delete' ? 'İş Emri Silindi' : 'İş Emri Güncellendi';
                        $messageUser = 'İş emriniz #' . $entityId . ($action === 'delete' ? ' silindi.' : ' güncellendi.');
                        if (!empty($meta['durum'])) { $messageUser .= ' Durum: ' . $meta['durum']; }
                        (new BildirimService())->notifyUserId($userIdToNotify, $titleUser, $messageUser, '/isemri/listesi', true);
                    }
                }
            }
        } catch (\Throwable $t) {
            error_log('ActivityNotifier user notify error: ' . $t->getMessage());
        }

        // 6) Yönetici tarafından yapılan ciro/gider düzenlemelerinde ilgili mağaza kullanıcılarına bildirim gönder
        try {
            // Aktör admin mi?
            $actor = (new Kullanici())->get($userId);
            $isActorAdmin = !empty($actor) && !empty($actor['yonetici']);

            if ($isActorAdmin && in_array($entityType, ['ciro', 'gider'], true) && !empty($entityId)) {
                $db = (new \core\Database())->getConnection();
                $table = $entityType === 'ciro' ? 'cirolar' : 'giderler';
                $stmt = $db->prepare("SELECT magaza_id FROM {$table} WHERE id = :id");
                $stmt->execute([':id' => $entityId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row && !empty($row['magaza_id'])) {
                    $magazaId = (int)$row['magaza_id'];
                    // Mağazaya bağlı kullanıcıları getir
                    $stmtUsers = $db->prepare("SELECT id FROM kullanicilar WHERE magaza_id = :magaza_id");
                    $stmtUsers->execute([':magaza_id' => $magazaId]);
                    $userIds = $stmtUsers->fetchAll(\PDO::FETCH_COLUMN);

                    if (!empty($userIds)) {
                        $service = new BildirimService();
                        foreach ($userIds as $uid) {
                            $uid = (int)$uid;
                            // İşlemi yapan kişiye (admin) tekrar gönderme
                            if ($uid === (int)$userId) { continue; }
                            $service->notifyUserId($uid, $title, $message, '/kullanici/bildirimler', true);
                        }
                    }
                }
            }
        } catch (\Throwable $t) {
            error_log('ActivityNotifier store user notify error: ' . $t->getMessage());
        }
    }

    private function buildNotificationContent(int $userId, string $action, string $entityType, ?int $entityId): array
    {
        $entityLabel = [
            'ciro' => 'Ciro',
            'gider' => 'Gider',
            'is_emri' => 'İş Emri',
        ][$entityType] ?? ucfirst($entityType);

        $actionLabel = [
            'create' => 'Yeni Kayıt',
            'update' => 'Güncelleme',
            'delete' => 'Silme',
        ][$action] ?? ucfirst($action);

        $title = $entityLabel . ' ' . $actionLabel;
        $suffix = $entityId ? (' #' . $entityId) : '';
        $message = 'Kullanıcı #' . $userId . ' ' . strtolower($entityLabel) . $suffix . ' ' . strtolower($actionLabel) . ' işlemi yaptı.';
        return [$title, $message];
    }
}


