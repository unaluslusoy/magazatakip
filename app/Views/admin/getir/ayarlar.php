<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">GetirÇarşı Ayarları</h3>
            <div class="d-flex gap-2">
                <a href="/admin/getir" class="btn btn-sm btn-light">Anasayfa</a>
            </div>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Base URL</label>
                    <input name="base_url" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['base_url'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Merchant ID</label>
                    <input name="merchant_id" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['merchant_id'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Key</label>
                    <input name="api_key" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['api_key'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Secret</label>
                    <input name="api_secret" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['api_secret'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Client ID</label>
                    <input name="client_id" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['client_id'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Client Secret</label>
                    <input name="client_secret" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['client_secret'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Webhook Secret</label>
                    <input name="webhook_secret" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['webhook_secret'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Token</label>
                    <input name="token" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['token'] ?? '') ?>" />
                </div>
                <div class="col-md-12">
                    <label class="form-label">Webhook X-API-Key</label>
                    <div class="input-group">
                        <input id="webhook_api_key" name="webhook_api_key" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['webhook_api_key'] ?? '') ?>" readonly />
                        <button class="btn btn-outline-secondary" type="button" id="btnGenKey">Yeni Anahtar Oluştur</button>
                    </div>
                    <div class="form-text">Webhook URL: <code>https://magazatakip.com/api/getir/newOrder</code> — Header: <code>X-API-Key: &lt;bu anahtar&gt;</code></div>
                </div>
                <div class="col-md-6 form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="enabledSwitch" name="enabled" value="1" <?= !empty($ayarlar['enabled']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="enabledSwitch">Servis Aktif</label>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Webhook Logları</h3>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-light" id="btnLogsYenile">Yenile</button>
                <button class="btn btn-sm btn-danger" id="btnLogsTemizle">Temizle</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th>ID</th>
                            <th>Zaman</th>
                            <th>Yön</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Özet</th>
                        </tr>
                    </thead>
                    <tbody id="getirLogsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
document.getElementById('btnGenKey')?.addEventListener('click', async function(){
    try {
        const res = await fetch('/admin/getir/ayarlar/generate-webhook-key', { method: 'POST' });
        const d = await res.json();
        if (d.success) {
            document.getElementById('webhook_api_key').value = d.webhook_api_key;
            if (window.Swal) {
                Swal.fire({toast:true, position:'top-end', icon:'success', title:'Anahtar oluşturuldu', showConfirmButton:false, timer:2500});
            }
        }
    } catch (e) {}
});

async function loadGetirLogs(){
    try {
        const res = await fetch('/admin/getir/loglar');
        const d = await res.json();
        const tbody = document.getElementById('getirLogsBody');
        tbody.innerHTML = '';
        (d.logs||[]).forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.id}</td>
                <td>${row.created_at || ''}</td>
                <td>${row.direction || ''}</td>
                <td>${row.method || ''}</td>
                <td>${row.status ?? ''}</td>
                <td style="max-width:620px;word-break:break-all;">
                    <pre class="bg-light p-2 mb-0" style="white-space:pre-wrap;max-height:180px;overflow:auto;">${row.body_preview || row.message || ''}</pre>
                </td>`;
            tbody.appendChild(tr);
        });
    } catch (e) {}
}
document.getElementById('btnLogsYenile')?.addEventListener('click', loadGetirLogs);
document.getElementById('btnLogsTemizle')?.addEventListener('click', async function(){
    if (!confirm('Logları temizlemek istediğinize emin misiniz?')) return;
    const res = await fetch('/admin/getir/loglar/temizle', { method: 'POST' });
    const d = await res.json();
    if (d.success) {
        loadGetirLogs();
        if (window.Swal) {
            Swal.fire({toast:true, position:'top-end', icon:'success', title:'Loglar temizlendi', showConfirmButton:false, timer:2500});
        }
    }
});
// sayfa açılışında getir
loadGetirLogs();
</script>


