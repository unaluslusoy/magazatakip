<?php
$title="<h2>İş Emri Güncelle</h2>";
$link = "Güncelleme" ;

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

    <div class="mb-4">
        <a href="/admin/istekler" class="btn btn-light btn-sm">
            <i class="ki-outline ki-arrow-left fs-5 me-2"></i> Geri
        </a>
    </div>

    <form method="post" action="/admin/istek/guncelle/<?= $istek['id']; ?>">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><span class="badge">İstek Bilgileri</span></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="baslik" class="form-label">Başlık</label>
                            <input type="text" id="baslik" class="form-control" value="<?= $istek['baslik']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">Açıklama</label>
                            <textarea id="aciklama" class="form-control" rows="5" disabled><?= $istek['aciklama']; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mağaza</label>
                                <input type="text" class="form-control" value="<?= $istek['magaza']; ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Öncelik</label>
                                <input type="text" class="form-control" value="<?= $istek['derece']; ?>" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Oluşturma</label>
                                <input type="text" class="form-control" value="<?= $istek['tarih'] ?? '';?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kullanıcı ID</label>
                                <input type="text" class="form-control" value="<?= $istek['kullanici_id'] ?? '';?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><span class="badge"> Atama ve Durum</span></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="durum" class="form-label">Durum</label>
                            <select name="durum" id="durum" class="form-select" required>
                                <option value="Yeni" <?= ($istek['durum'] ?? '') === 'Yeni' ? 'selected' : ''; ?>>Yeni</option>
                                <option value="Beklemede" <?= ($istek['durum'] ?? '') === 'Beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                                <option value="Devam Ediyor" <?= ($istek['durum'] ?? '') === 'Devam Ediyor' ? 'selected' : ''; ?>>Devam Ediyor</option>
                                <option value="Tamamlandı" <?= ($istek['durum'] ?? '') === 'Tamamlandı' ? 'selected' : ''; ?>>Tamamlandı</option>
                                <option value="Durduruldu" <?= ($istek['durum'] ?? '') === 'Durduruldu' ? 'selected' : ''; ?>>Durduruldu</option>
                                <option value="Gözden Geçiriliyor" <?= ($istek['durum'] ?? '') === 'Gözden Geçiriliyor' ? 'selected' : ''; ?>>Gözden Geçiriliyor</option>
                                <option value="Onay Bekliyor" <?= ($istek['durum'] ?? '') === 'Onay Bekliyor' ? 'selected' : ''; ?>>Onay Bekliyor</option>
                                <option value="Red Edildi" <?= ($istek['durum'] ?? '') === 'Red Edildi' ? 'selected' : ''; ?>>Red Edildi</option>
                                <option value="Revize Ediliyor" <?= ($istek['durum'] ?? '') === 'Revize Ediliyor' ? 'selected' : ''; ?>>Revize Ediliyor</option>
                                <option value="Erteleme" <?= ($istek['durum'] ?? '') === 'Erteleme' ? 'selected' : ''; ?>>Erteleme</option>
                                <option value="İptal Edildi" <?= ($istek['durum'] ?? '') === 'İptal Edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                                <option value="Sorun Var" <?= ($istek['durum'] ?? '') === 'Sorun Var' ? 'selected' : ''; ?>>Sorun Var</option>
                                <option value="Tekrar Açıldı" <?= ($istek['durum'] ?? '') === 'Tekrar Açıldı' ? 'selected' : ''; ?>>Tekrar Açıldı</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="personel_id" class="form-label">Sorumlu Personel</label>
                            <select name="personel_id" id="personel_id" class="form-select">
                                <option value="0">Seçim Yapın</option>
                                <?php foreach ($personeller as $personel): ?>
                                <option value="<?= $personel['id']; ?>" <?= $personel['id'] == ($istek['personel_id'] ?? 0) ? 'selected' : ''; ?>><?= $personel['ad']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_aciklamasi" class="form-label">İş Açıklaması</label>
                            <textarea name="is_aciklamasi" id="is_aciklamasi" class="form-control" rows="3"><?= $istek['is_aciklamasi'] ?? ''; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi</label>
                                <input type="date" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control" value="<?= $istek['baslangic_tarihi'] ?? '' ; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi</label>
                                <input type="date" name="bitis_tarihi" id="bitis_tarihi" class="form-control" value="<?= $istek['bitis_tarihi'] ?? '' ; ?>">
                            </div>
                        </div>
                        <?php $attachments = $istek['attachments'] ?? []; if (!empty($attachments)): ?>
                        <div class="mb-3">
                            <label class="form-label">Yüklenen Görseller</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($attachments as $att): ?>
                                <a href="/public/uploads/isemri/<?= htmlspecialchars($att['dosya_yolu']) ?>" target="_blank" class="d-inline-block" title="<?= htmlspecialchars($att['dosya_adi'] ?? 'Dosya') ?>">
                                    <img src="/public/uploads/isemri/<?= htmlspecialchars($att['dosya_yolu']) ?>" alt="ek" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #eee;" />
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
<?php
require_once 'app/Views/layouts/footer.php';