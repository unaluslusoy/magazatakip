<?php
namespace app\Models\Kullanici\Ciro;

use core\Model;

class CiroModel extends Model {
    protected $table = 'ciro'; // Tablo adınızı burada belirtin

    public function ciroEkle($data) {
        $this->db->beginTransaction();

        $query = "INSERT INTO ciro_takibi 
              (magaza_id, ekleme_tarihi, gun, nakit, kredi_karti, carliston, getir_carsi, trendyolgo, multinet, sodexo, edenred, setcard, tokenflex, iwallet,metropol, gider, aciklama, toplam) 
              VALUES 
              (:magaza_id, :ekleme_tarihi, :gun, :nakit, :kredi_karti, :carliston, :getir_carsi, :trendyolgo, :multinet, :sodexo, :edenred, :setcard, :tokenflex, :iwallet, :metropol, :gider, :aciklama, :toplam)";

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
        $stmt->bindParam(':gider', $data['gider']);
        $stmt->bindParam(':aciklama', $data['aciklama']);
        $stmt->bindParam(':toplam', $data['toplam']);
        // Sorguyu çalıştırma
        return $stmt->execute();
    }


    public function getMagazalar() {
        $query = "SELECT id, ad FROM magazalar";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function ciroVarMi() {
        $query = "SELECT COUNT(*) as toplam FROM ciro_takibi";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['toplam'] > 0;
    }
    public function ciroGuncelle($id, $veriler) {
        $query = "UPDATE ciro_takibi SET
                magaza_id = :magaza_id,
                ekleme_tarihi = :ekleme_tarihi,
                gun = :gun,
                nakit = :nakit,
                kredi_karti = :kredi_karti,
                carliston = :carliston,
                getir_carsi = :getir_carsi,
                trendyolgo = :trendyolgo,
                multinet = :multinet,
                sodexo = :sodexo,
                ticket = :ticket,
                edenred = :edenred,
                setcard = :setcard,
                didi = :didi,
                gider = :gider,
                aciklama = :aciklama,
                durum = :durum
              WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $veriler['id'] = $id;

        return $stmt->execute($veriler);
    }

    public function ciroGetir($id) {
        $query = "SELECT * FROM ciro_takibi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function ciroSil($id) {
        $query = "DELETE FROM ciro_takibi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function ciroListele()
    {
    }


}
