<?php
namespace app\Models\Kullanici\Ciro;

use core\Model;

class CiroModel extends Model {
    protected $table = 'ciro'; // Tablo adınızı burada belirtin

    public function ciroEkle($data) {
        $this->db->beginTransaction();

        $query = "INSERT INTO cirolar 
              (magaza_id, ekleme_tarihi, gun, nakit, kredi_karti, carliston, getir_carsi, trendyolgo, multinet, sodexo, edenred, setcard, tokenflex, iwallet, metropol, ticket, didi, gider, aciklama, toplam, gorsel) 
              VALUES 
              (:magaza_id, :ekleme_tarihi, :gun, :nakit, :kredi_karti, :carliston, :getir_carsi, :trendyolgo, :multinet, :sodexo, :edenred, :setcard, :tokenflex, :iwallet, :metropol, :ticket, :didi, :gider, :aciklama, :toplam, :gorsel)";

        $stmt = $this->db->prepare($query);
        // Verileri bağlama
        $stmt->bindParam(':magaza_id', $data['magaza_id']);
        $stmt->bindParam(':ekleme_tarihi', $data['ekleme_tarihi']);
        $stmt->bindParam(':gun', $data['gun']);
        $stmt->bindParam(':nakit', $data['nakit']);
        $stmt->bindParam(':kredi_karti', $data['kredi_karti']);
        $stmt->bindParam(':carliston', $data['carliston']);
        $stmt->bindParam(':getir_carsi', $data['getir_carsi']);
        $stmt->bindParam(':trendyolgo', $data['trendyolgo']);
        $stmt->bindParam(':multinet', $data['multinet']);
        $stmt->bindParam(':sodexo', $data['sodexo']);
        $stmt->bindParam(':edenred', $data['edenred']);
        $stmt->bindParam(':setcard', $data['setcard']);
        $stmt->bindParam(':tokenflex', $data['tokenflex']);
        $stmt->bindParam(':iwallet', $data['iwallet']);
        $stmt->bindParam(':metropol', $data['metropol']);
        $stmt->bindParam(':ticket', $data['ticket']);
        $stmt->bindParam(':didi', $data['didi']);
        $stmt->bindParam(':gider', $data['gider']);
        $stmt->bindParam(':aciklama', $data['aciklama']);
        $stmt->bindParam(':toplam', $data['toplam']);
        $stmt->bindParam(':gorsel', $data['gorsel']);
        // Sorguyu çalıştırma
        $result = $stmt->execute();
        if ($result) {
            $this->db->commit();
        } else {
            $this->db->rollBack();
        }
        return $result;
    }

    public function getMagazalar() {
        $query = "SELECT SQL_NO_CACHE id, ad FROM magazalar ORDER BY ad";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function ciroVarMi() {
        $query = "SELECT SQL_NO_CACHE COUNT(*) as sayi FROM cirolar";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $count = $result['sayi'];
        error_log("CiroModel::ciroVarMi() - Toplam kayıt sayısı: " . $count);
        
        return $count > 0;
    }
    public function ciroGuncelle($id, $veriler) {
        $query = "UPDATE cirolar SET
                magaza_id = :magaza_id,
                gun = :gun,
                nakit = :nakit,
                kredi_karti = :kredi_karti,
                carliston = :carliston,
                getir_carsi = :getir_carsi,
                trendyolgo = :trendyolgo,
                multinet = :multinet,
                sodexo = :sodexo,
                edenred = :edenred,
                setcard = :setcard,
                tokenflex = :tokenflex,
                iwallet = :iwallet,
                metropol = :metropol,
                ticket = :ticket,
                didi = :didi,
                toplam = :toplam,
                aciklama = :aciklama,
                gorsel = :gorsel
              WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $veriler['id'] = $id;

        return $stmt->execute($veriler);
    }

    public function ciroGetir($id) {
        $query = "SELECT SQL_NO_CACHE c.*, m.ad as magaza_adi 
                  FROM cirolar c 
                  LEFT JOIN magazalar m ON c.magaza_id = m.id 
                  WHERE c.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function ciroSil($id) {
        $query = "DELETE FROM cirolar WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function ciroListele() {
        // Veritabanı bağlantısını yenile
        $this->db = null;
        
        // Config dosyasını yükle
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../../../../config/database.php';
        }
        
        $this->db = new \PDO(
            "mysql:host=" . \DB_HOST . ";dbname=" . \DB_NAME . ";charset=utf8mb4",
            \DB_USER,
            \DB_PASS,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        
        $query = "SELECT SQL_NO_CACHE c.*, m.ad as magaza_adi 
                  FROM cirolar c 
                  LEFT JOIN magazalar m ON c.magaza_id = m.id 
                  ORDER BY c.gun DESC, c.id DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Debug log
        error_log("CiroModel::ciroListele() - Bulunan kayıt sayısı: " . count($result));
        
        return $result;
    }
}
