<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Tamsoft Entegrasyon Ayarları</h3>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Base URL</label>
                    <input name="base_url" type="text" class="form-control" placeholder="http://tamsoftintegration.camlica.com.tr" value="<?= htmlspecialchars($ayarlar['base_url'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alternatif Base URL</label>
                    <input name="alt_base_url" type="text" class="form-control" placeholder="http://185.124.86.45:8899" value="<?= htmlspecialchars($ayarlar['alt_base_url'] ?? '') ?>" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input name="username" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['username'] ?? '') ?>" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Şifre</label>
                    <input name="password" type="password" class="form-control" value="<?= htmlspecialchars($ayarlar['password'] ?? '') ?>" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kontrol Sıklığı (dakika)</label>
                    <input name="schedule_minutes" type="number" min="0" class="form-control" placeholder="30" value="<?= htmlspecialchars($ayarlar['schedule_minutes'] ?? '') ?>" />
                </div>
                <div class="col-12 d-flex align-items-center gap-3">
                    <div class="form-check form-switch">
                        <input name="enabled" class="form-check-input" type="checkbox" id="enabledSwitch" <?= !empty($ayarlar['enabled']) ? 'checked' : '' ?> />
                        <label class="form-check-label" for="enabledSwitch">Servis aktif</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Kaydet</button>
                    <a href="/admin/tamsoft/token-test" target="_blank" class="btn btn-light-primary">Token Test (JSON)</a>
                    <a href="/admin/tamsoft/depolar" target="_blank" class="btn btn-light-secondary">Depo Listesi (JSON)</a>
                </div>
            </form>
        </div>
    </div>
 </div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

