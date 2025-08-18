-- Önerilen indeksler ve yapılandırmalar (MySQL/InnoDB)

-- Ürünler
ALTER TABLE tamsoft_urunler
  ADD INDEX idx_urun_adi (urun_adi),
  ADD INDEX idx_aktif (aktif);

-- Barkodlar
ALTER TABLE tamsoft_urun_barkodlar
  ADD INDEX idx_urun (urun_id),
  ADD UNIQUE KEY uk_barkod (barkod);

-- Depolar
ALTER TABLE tamsoft_depolar
  ADD UNIQUE KEY uk_depo_id (depo_id);

-- Depo stok özet
ALTER TABLE tamsoft_depo_stok_ozet
  ADD INDEX idx_depo (depo_id),
  ADD INDEX idx_urun (urun_id);

-- Stok değişim logu
ALTER TABLE tamsoft_depo_stok_degisim_log
  ADD INDEX idx_changed (changed_at),
  ADD INDEX idx_ud (urun_id, depo_id);

-- Entegrasyon eşlemeleri
ALTER TABLE urun_entegrasyon_map
  ADD INDEX idx_barkod (barkod),
  ADD INDEX idx_urun_kodu (urun_kodu);

-- Stage tabloları
ALTER TABLE tamsoft_urunler_stage
  ADD UNIQUE KEY uk_ext (ext_urun_id, barkod);

ALTER TABLE tamsoft_urun_barkodlar_stage
  ADD UNIQUE KEY uk_barkod (barkod);


