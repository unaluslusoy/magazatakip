<?php

namespace app\Models;

use core\Model;

class Bildirim extends Model
{
    protected $table = 'bildirimler';

    public function getPaginatedNotifications($page = 1, $perPage = 10, $dateFilter = 'all', $aliciFilter = 'all', $durumFilter = 'all', $searchTerm = '')
    {
        try {
            $offset = ($page - 1) * $perPage;
            $whereConditions = [];
            $params = [];

            // Tarih filtresi
            if ($dateFilter !== 'all') {
                switch ($dateFilter) {
                    case 'today':
                        $whereConditions[] = "DATE(gonderim_tarihi) = CURDATE()";
                        break;
                    case 'week':
                        $whereConditions[] = "gonderim_tarihi >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                        break;
                    case 'month':
                        $whereConditions[] = "gonderim_tarihi >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                        break;
                }
            }

            // Alıcı filtresi
            if ($aliciFilter !== 'all') {
                $whereConditions[] = "alici_tipi = :alici_tipi";
                $params[':alici_tipi'] = $aliciFilter;
            }

            // Durum filtresi
            if ($durumFilter !== 'all') {
                $whereConditions[] = "durum = :durum";
                $params[':durum'] = $durumFilter;
            }

            // Arama terimi
            if (!empty($searchTerm)) {
                $whereConditions[] = "(baslik LIKE :search OR mesaj LIKE :search)";
                $params[':search'] = "%{$searchTerm}%";
            }

            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            }

            $sql = "SELECT b.*, k.ad as gonderici_adi, k.soyad as gonderici_soyadi 
                    FROM {$this->table} b 
                    LEFT JOIN kullanicilar k ON b.kullanici_id = k.id 
                    {$whereClause} 
                    ORDER BY b.gonderim_tarihi DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            
            // Parametreleri bind et
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim listesi alınırken hata: " . $e->getMessage());
            return [];
        }
    }

    public function getNotificationCounts()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as toplam,
                        SUM(CASE WHEN durum = 'gonderildi' THEN 1 ELSE 0 END) as gonderildi,
                        SUM(CASE WHEN durum = 'beklemede' THEN 1 ELSE 0 END) as beklemede,
                        SUM(CASE WHEN durum = 'basarisiz' THEN 1 ELSE 0 END) as basarisiz,
                        SUM(CASE WHEN DATE(gonderim_tarihi) = CURDATE() THEN 1 ELSE 0 END) as bugun
                    FROM {$this->table}";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim sayıları alınırken hata: " . $e->getMessage());
            return [
                'toplam' => 0,
                'gonderildi' => 0,
                'beklemede' => 0,
                'basarisiz' => 0,
                'bugun' => 0
            ];
        }
    }

    public function deleteBildirim($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Bildirim silinirken hata: " . $e->getMessage());
            return false;
        }
    }

    public function markAsRead($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET okundu = 1, okunma_tarihi = NOW() WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Bildirim okundu işaretlenirken hata: " . $e->getMessage());
            return false;
        }
    }

    public function getBildirimById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT b.*, k.ad as gonderici_adi, k.soyad as gonderici_soyadi 
                                       FROM {$this->table} b 
                                       LEFT JOIN kullanicilar k ON b.kullanici_id = k.id 
                                       WHERE b.id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim detayı alınırken hata: " . $e->getMessage());
            return false;
        }
    }

    public function getKullaniciBildirimleri($kullaniciId, $limit = 10)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
                                       WHERE hedef_kullanici_id = :kullanici_id 
                                       ORDER BY gonderim_tarihi DESC 
                                       LIMIT :limit");
            $stmt->bindValue(':kullanici_id', $kullaniciId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Kullanıcı bildirimleri alınırken hata: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kullanıcının bildirimlerini sayfalama ile getir
     */
    public function getKullaniciBildirimleriPaginated($kullaniciId, $page = 1, $perPage = 10, $durumFilter = 'all', $searchTerm = '')
    {
        try {
            $offset = ($page - 1) * $perPage;
            $whereConditions = ["hedef_kullanici_id = :kullanici_id"];
            $params = [':kullanici_id' => $kullaniciId];

            // Durum filtresi
            if ($durumFilter !== 'all') {
                $whereConditions[] = "okundu = :okundu";
                $params[':okundu'] = ($durumFilter === 'okundu') ? 1 : 0;
            }

            // Arama terimi
            if (!empty($searchTerm)) {
                $whereConditions[] = "(baslik LIKE :search OR mesaj LIKE :search)";
                $params[':search'] = "%{$searchTerm}%";
            }

            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

            $sql = "SELECT b.*, k.ad as gonderici_adi, k.soyad as gonderici_soyadi 
                    FROM {$this->table} b 
                    LEFT JOIN kullanicilar k ON b.kullanici_id = k.id 
                    {$whereClause} 
                    ORDER BY b.gonderim_tarihi DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Kullanıcı bildirimleri alınırken hata: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kullanıcının bildirim sayılarını getir
     */
    public function getKullaniciBildirimSayilari($kullaniciId)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as toplam,
                        SUM(CASE WHEN okundu = 0 THEN 1 ELSE 0 END) as okunmamis,
                        SUM(CASE WHEN okundu = 1 THEN 1 ELSE 0 END) as okundu,
                        SUM(CASE WHEN DATE(gonderim_tarihi) = CURDATE() THEN 1 ELSE 0 END) as bugun
                    FROM {$this->table} 
                    WHERE hedef_kullanici_id = :kullanici_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim sayıları alınırken hata: " . $e->getMessage());
            return [
                'toplam' => 0,
                'okunmamis' => 0,
                'okundu' => 0,
                'bugun' => 0
            ];
        }
    }

    /**
     * Kullanıcının okunmamış bildirim sayısını getir
     */
    public function getUnreadCount($kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE hedef_kullanici_id = :kullanici_id AND okundu = 0");
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (\PDOException $e) {
            error_log("Okunmamış bildirim sayısı alınırken hata: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Kullanıcının bildirim detayını getir
     */
    public function getKullaniciBildirimDetay($id, $kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("SELECT b.*, k.ad as gonderici_adi, k.soyad as gonderici_soyadi 
                                       FROM {$this->table} b 
                                       LEFT JOIN kullanicilar k ON b.kullanici_id = k.id 
                                       WHERE b.id = :id AND b.hedef_kullanici_id = :kullanici_id");
            $stmt->execute(['id' => $id, 'kullanici_id' => $kullaniciId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Bildirim detayı alınırken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kullanıcının bildirimini okundu olarak işaretle
     */
    public function markKullaniciBildirimAsRead($id, $kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET okundu = 1, okunma_tarihi = NOW() WHERE id = :id AND hedef_kullanici_id = :kullanici_id");
            return $stmt->execute(['id' => $id, 'kullanici_id' => $kullaniciId]);
        } catch (\PDOException $e) {
            error_log("Bildirim okundu işaretlenirken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     */
    public function markAllAsRead($kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET okundu = 1, okunma_tarihi = NOW() WHERE hedef_kullanici_id = :kullanici_id AND okundu = 0");
            return $stmt->execute([':kullanici_id' => $kullaniciId]);
        } catch (\PDOException $e) {
            error_log("Tüm bildirimler okundu işaretlenirken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Okundu sayısını getir
     */
    public function getReadCount($kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE hedef_kullanici_id = :kullanici_id AND okundu = 1");
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (\PDOException $e) {
            error_log("Okundu bildirim sayısı alınırken hata: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Toplam sayıyı getir
     */
    public function getTotalCount($kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE hedef_kullanici_id = :kullanici_id");
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (\PDOException $e) {
            error_log("Toplam bildirim sayısı alınırken hata: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Bugünkü sayıyı getir
     */
    public function getTodayCount($kullaniciId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE hedef_kullanici_id = :kullanici_id AND DATE(gonderim_tarihi) = CURDATE()");
            $stmt->execute([':kullanici_id' => $kullaniciId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (\PDOException $e) {
            error_log("Bugünkü bildirim sayısı alınırken hata: " . $e->getMessage());
            return 0;
        }
    }
}