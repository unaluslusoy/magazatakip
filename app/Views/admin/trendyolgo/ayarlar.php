<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Trendyol Go Ayarları</h3>
            <div class="d-flex gap-2">
                <a href="/admin/trendyolgo" class="btn btn-sm btn-light">Anasayfa</a>
                <a href="/admin/trendyolgo/urunler" class="btn btn-sm btn-light">Ürün Listesi</a>
                <a href="/admin/trendyolgo/magazalar" class="btn btn-sm btn-light">Mağaza Listesi</a>
                <a href="/admin/trendyolgo/ayarlar" class="btn btn-sm btn-primary">Ayarlar</a>
            </div>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Satıcı ID (Cari ID)</label>
                    <input name="satici_cari_id" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['satici_cari_id'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Entegrasyon Referans Kodu</label>
                    <input name="entegrasyon_ref_kodu" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['entegrasyon_ref_kodu'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Key</label>
                    <input name="api_key" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['api_key'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Secret</label>
                    <input name="api_secret" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['api_secret'] ?? '') ?>" />
                </div>
                <div class="col-md-12">
                    <label class="form-label">Token</label>
                    <input name="token" type="text" class="form-control" value="<?= htmlspecialchars($ayarlar['token'] ?? '') ?>" />
                </div>
                <div class="col-md-6 form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="enabledSwitch" name="enabled" value="1" <?= !empty($ayarlar['enabled']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="enabledSwitch">Servis Aktif</label>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-light" type="button" id="btnHealth">Servisi Test Et</button>
                    <button class="btn btn-primary" type="submit">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Servis Logları</h3>
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
                            <th>Method</th>
                            <th>Status</th>
                            <th>URL</th>
                            <th>Özet</th>
                        </tr>
                    </thead>
                    <tbody id="tgoLogsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
document.getElementById('btnHealth')?.addEventListener('click', async function(){
    try {
        const res = await fetch('/admin/trendyolgo/health');
        const d = await res.json();
        const ok = d.service_ok ? 'Evet' : 'Hayır';
        const base = d.base_url || '-';
        const text = `Aktif: ${d.enabled ? 'Evet' : 'Hayır'}\nÇalışıyor: ${ok}\nBase URL: ${base}`;
        const icon = d.enabled && d.service_ok ? 'success' : (d.enabled ? 'warning' : 'info');
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: 'Trendyol GO Servis Testi',
                text: text,
                showConfirmButton: false,
                timer: 4000,
            });
        } else if (typeof showToast === 'function') {
            showToast(text, icon === 'success' ? 'success' : 'warning');
        } else {
            console.log(text);
        }
    } catch (e) { alert('Test başarısız: ' + e.message); }
});
</script>
<script>
async function loadLogs(){
    try {
        const res = await fetch('/admin/trendyolgo/loglar');
        const d = await res.json();
        const tbody = document.getElementById('tgoLogsBody');
        tbody.innerHTML = '';
        (d.logs||[]).forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.id}</td>
                <td>${row.created_at || ''}</td>
                <td>${row.method || ''}</td>
                <td>${row.status ?? ''}</td>
                <td style="max-width:420px;word-break:break-all;">${row.url || ''}</td>
                <td style="max-width:420px;word-break:break-all;"><pre class="bg-light p-2 mb-0" style="white-space:pre-wrap;max-height:140px;overflow:auto;">${row.body_preview || row.message || ''}</pre></td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {
        console.error(e);
    }
}
document.getElementById('btnLogsYenile')?.addEventListener('click', loadLogs);
document.getElementById('btnLogsTemizle')?.addEventListener('click', async function(){
    if (!confirm('Logları temizlemek istediğinize emin misiniz?')) return;
    const res = await fetch('/admin/trendyolgo/loglar/temizle', { method: 'POST' });
    const d = await res.json();
    if (d.success) {
        if (window.Swal) {
            Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Loglar temizlendi', showConfirmButton:false, timer:2500 });
        }
        loadLogs();
    }
});
// sayfa açılışında otomatik yükle
loadLogs();
</script>

