<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Trendyol Go - Siparişler</h3>
            <div class="d-flex gap-2">
                <a href="/admin/trendyolgo" class="btn btn-sm btn-light">Anasayfa</a>
                <a href="/admin/trendyolgo/urunler" class="btn btn-sm btn-light">Ürün Listesi</a>
                <a href="/admin/trendyolgo/ayarlar" class="btn btn-sm btn-light">Ayarlar</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach (['ACTIVE'=>'Aktif','NEW'=>'Yeni','PREPARING'=>'Hazırlanıyor','DELIVERED'=>'Teslim','CANCELLED'=>'İptal'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($status??'')===$k?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Başlangıç</label>
                    <input type="datetime-local" name="start" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['start'] ?? '') ?>" />
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bitiş</label>
                    <input type="datetime-local" name="end" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['end'] ?? '') ?>" />
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mağaza ID</label>
                    <input type="text" name="store_id" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['store_id'] ?? '') ?>" />
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sayfa Boyutu</label>
                    <select name="per" class="form-select form-select-sm">
                        <?php foreach ([25,50,100] as $pp): ?>
                        <option value="<?= $pp ?>" <?= (int)($per??50) === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-sm btn-primary" type="submit">Listele</button>
                </div>
            </form>

            <?php if (empty($items)): ?>
                <div class="alert alert-info">Kayıt bulunamadı.</div>
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
                                <th>Sipariş ID</th>
                                <th>Durum</th>
                                <th>Müşteri</th>
                                <th>Tutar</th>
                                <th>Mağaza</th>
                                <th>Tarih</th>
                                <th>Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['id'] ?? ($o['orderId'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars($o['status'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(($o['customer']['name'] ?? '') . ' ' . ($o['customer']['phone'] ?? '')) ?></td>
                                <td><?= isset($o['totalPrice']) ? number_format((float)$o['totalPrice'], 2, ',', '.') : '-' ?></td>
                                <td><?= htmlspecialchars($o['storeId'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($o['createdDate'] ?? ($o['createDate'] ?? '-')) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-light" onclick="updateOrderStatus('<?= htmlspecialchars($o['id'] ?? ($o['orderId'] ?? '')) ?>','PREPARING')">Hazırlanıyor</button>
                                        <button class="btn btn-sm btn-success" onclick="updateOrderStatus('<?= htmlspecialchars($o['id'] ?? ($o['orderId'] ?? '')) ?>','DELIVERED')">Teslim</button>
                                        <button class="btn btn-sm btn-danger" onclick="updateOrderStatus('<?= htmlspecialchars($o['id'] ?? ($o['orderId'] ?? '')) ?>','CANCELLED')">İptal</button>
                                    </div>
                                </td>
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
async function updateOrderStatus(orderId, status){
    try {
        const res = await fetch('/admin/trendyolgo/siparis/durum', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ order_id: orderId, status: status })
        });
        const d = await res.json();
        if (d.success) {
            if (window.Swal) Swal.fire({toast:true, position:'top-end', icon:'success', title:'Durum güncellendi', showConfirmButton:false, timer:2000});
            setTimeout(()=>location.reload(), 800);
        } else {
            const msg = d.error || 'Güncelleme başarısız';
            if (window.Swal) Swal.fire({toast:true, position:'top-end', icon:'error', title:msg, showConfirmButton:false, timer:3000});
        }
    } catch(e) {
        if (window.Swal) Swal.fire({toast:true, position:'top-end', icon:'error', title:'Hata', text:e.message, showConfirmButton:false, timer:3500});
    }
}
</script>
