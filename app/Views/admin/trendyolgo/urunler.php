<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Trendyol Go - Ürün Listesi</h3>
            <div class="d-flex gap-2">
                <a href="/admin/trendyolgo" class="btn btn-sm btn-light">Anasayfa</a>
                <a href="/admin/trendyolgo/magazalar" class="btn btn-sm btn-light">Mağaza Listesi</a>
                <a href="/admin/trendyolgo/ayarlar" class="btn btn-sm btn-light">Ayarlar</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="d-flex align-items-center gap-2 mb-3">
                <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control form-control-sm" placeholder="Ürün adı/kodu ara" style="width: 240px;" />
                <select name="per" class="form-select form-select-sm" style="width: 100px;">
                    <?php foreach ([25,50,100] as $pp): ?>
                        <option value="<?= $pp ?>" <?= (int)$per === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-light" type="submit">Ara</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnImport">Ürünleri İçeri Al (Önizleme)</button>
            </form>
            <div id="importPreview" class="mb-4" style="display:none">
                <div class="alert alert-info mb-2">Önizleme - ilk 10 kayıt</div>
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Ürün</th>
                                <th>Barcode</th>
                                <th>Kod</th>
                                <th>Marka</th>
                                <th>Kategori</th>
                            </tr>
                        </thead>
                        <tbody id="importPreviewTbody"></tbody>
                    </table>
                </div>
            </div>
            <?php if (empty($items)): ?>
                <div class="alert alert-info">Henüz veri yok.</div>
                <?php if (!empty($last_request)): ?>
                    <div class="alert alert-warning mt-3">
                        <div><b>Son istek:</b> <?= htmlspecialchars($last_request['url'] ?? '-') ?></div>
                        <div><b>Status:</b> <?= htmlspecialchars($last_request['status'] ?? '-') ?></div>
                        <pre class="bg-light p-3 mt-2" style="white-space:pre-wrap;max-height:200px;overflow:auto;"><?= htmlspecialchars($last_request['body_preview'] ?? '-') ?></pre>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Ürün</th>
                                <th>Barcode</th>
                                <th>Kod</th>
                                <th>Marka</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Stok</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['barcode'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['code'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['brand'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['categoryId'] ?? '-') ?></td>
                                <td><?= isset($p['price']) ? number_format((float)$p['price'], 2, ',', '.') : '-' ?></td>
                                <td><?= htmlspecialchars($p['stock'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['status'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
document.getElementById('btnImport')?.addEventListener('click', async function(){
    try {
        const res = await fetch('/admin/trendyolgo/urunler/import-trigger', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            const tbody = document.getElementById('importPreviewTbody');
            tbody.innerHTML = '';
            (data.preview || []).forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${(p.name||'-')}</td><td>${(p.barcode||'-')}</td><td>${(p.code||'-')}</td><td>${(p.brand||'-')}</td><td>${(p.categoryId||'-')}</td>`;
                tbody.appendChild(tr);
            });
            document.getElementById('importPreview').style.display = 'block';
            if ((data.preview||[]).length === 0) {
                const warn = document.createElement('div');
                warn.className = 'alert alert-warning mt-3';
                const url = data.last_request?.url || '-';
                const status = data.last_request?.status ?? '-';
                const body = data.last_request?.body_preview || (data.error||'');
                warn.innerHTML = `<div><b>Son istek:</b> ${url}</div><div><b>Status:</b> ${status}</div><pre class="bg-light p-3 mt-2" style="white-space:pre-wrap;max-height:200px;overflow:auto;">${body}</pre>`;
                document.getElementById('importPreview').appendChild(warn);
                if (window.Swal) {
                    Swal.fire({toast:true, position:'top-end', icon:'warning', title:'Önizleme boş geldi', text:'Detaylar sayfada listelendi.', showConfirmButton:false, timer:4000});
                }
            } else {
                if (window.Swal) {
                    Swal.fire({toast:true, position:'top-end', icon:'success', title:'Önizleme hazır', showConfirmButton:false, timer:2000});
                }
            }
        } else {
            const msg = data.error || 'İş tetiklenemedi';
            if (window.Swal) {
                Swal.fire({toast:true, position:'top-end', icon:'error', title:msg, showConfirmButton:false, timer:3000});
            } else {
                alert(msg);
            }
        }
    } catch (e) {
        if (window.Swal) {
            Swal.fire({toast:true, position:'top-end', icon:'error', title:'Hata', text:e.message, showConfirmButton:false, timer:3500});
        } else { alert('Hata: ' + e.message); }
    }
});
</script>

