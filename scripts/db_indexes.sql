-- Önerilen indeksler (varsa oluşturma, yoksa atla) - MySQL 5.7/8 uyumlu

-- Helper: koşullu index oluşturma prosedürü gibi çalışacak blok
-- Kullanım: SET @table='...'; SET @index='...'; SET @def='CREATE INDEX ...'; CALL benzeri blok

-- Tamsoft
SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='tamsoft_urunler' AND INDEX_NAME='idx_tu_ext'), 'SELECT 1', 'CREATE INDEX idx_tu_ext ON tamsoft_urunler (ext_urun_id)');
PREPARE s1 FROM @def; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='tamsoft_urunler' AND INDEX_NAME='idx_tu_barkod'), 'SELECT 1', 'CREATE INDEX idx_tu_barkod ON tamsoft_urunler (barkod)');
PREPARE s2 FROM @def; EXECUTE s2; DEALLOCATE PREPARE s2;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='tamsoft_depo_stok_ozet' AND INDEX_NAME='idx_s_urun_depo'), 'SELECT 1', 'CREATE INDEX idx_s_urun_depo ON tamsoft_depo_stok_ozet (urun_id, depo_id)');
PREPARE s3 FROM @def; EXECUTE s3; DEALLOCATE PREPARE s3;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='urun_entegrasyon_map' AND INDEX_NAME='idx_map_urun'), 'SELECT 1', 'CREATE INDEX idx_map_urun ON urun_entegrasyon_map (urun_kodu)');
PREPARE s4 FROM @def; EXECUTE s4; DEALLOCATE PREPARE s4;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='urun_entegrasyon_map' AND INDEX_NAME='idx_map_barkod'), 'SELECT 1', 'CREATE INDEX idx_map_barkod ON urun_entegrasyon_map (barkod)');
PREPARE s5 FROM @def; EXECUTE s5; DEALLOCATE PREPARE s5;

-- Ciro/Gider
SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='cirolar' AND INDEX_NAME='idx_cirolar_gun'), 'SELECT 1', 'CREATE INDEX idx_cirolar_gun ON cirolar (gun)');
PREPARE s6 FROM @def; EXECUTE s6; DEALLOCATE PREPARE s6;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='cirolar' AND INDEX_NAME='idx_cirolar_magaza_gun'), 'SELECT 1', 'CREATE INDEX idx_cirolar_magaza_gun ON cirolar (magaza_id, gun)');
PREPARE s7 FROM @def; EXECUTE s7; DEALLOCATE PREPARE s7;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='giderler' AND INDEX_NAME='idx_giderler_tarih'), 'SELECT 1', 'CREATE INDEX idx_giderler_tarih ON giderler (tarih)');
PREPARE s8 FROM @def; EXECUTE s8; DEALLOCATE PREPARE s8;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='giderler' AND INDEX_NAME='idx_giderler_magaza_tarih'), 'SELECT 1', 'CREATE INDEX idx_giderler_magaza_tarih ON giderler (magaza_id, tarih)');
PREPARE s9 FROM @def; EXECUTE s9; DEALLOCATE PREPARE s9;

-- İş Emri
SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='istekler' AND INDEX_NAME='idx_istekler_magaza'), 'SELECT 1', 'CREATE INDEX idx_istekler_magaza ON istekler (magaza_id)');
PREPARE s10 FROM @def; EXECUTE s10; DEALLOCATE PREPARE s10;

SET @def := IF(EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='istekler' AND INDEX_NAME='idx_istekler_durum_magaza'), 'SELECT 1', 'CREATE INDEX idx_istekler_durum_magaza ON istekler (durum, magaza_id)');
PREPARE s11 FROM @def; EXECUTE s11; DEALLOCATE PREPARE s11;


