<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Tamsoft Stok (Sadece Stok Senkronu)</h3>
		</div>
		<div class="card-body">
			<form id="frmStockCfg" class="row g-3">
				<div class="col-12"><h5>API Ayarları</h5><hr class="my-2"/></div>
				<div class="col-md-4">
					<label class="form-label">API URL</label>
					<input type="text" name="api_url" class="form-control" value="<?= htmlspecialchars($config['api_url'] ?? '', ENT_QUOTES) ?>" />
				</div>
				<div class="col-md-4">
					<label class="form-label">Kullanıcı</label>
					<input type="text" name="kullanici" class="form-control" value="<?= htmlspecialchars($config['kullanici'] ?? '', ENT_QUOTES) ?>" />
				</div>
				<div class="col-md-4">
					<label class="form-label">Şifre</label>
					<input type="password" name="sifre" class="form-control" value="<?= htmlspecialchars($config['sifre'] ?? '', ENT_QUOTES) ?>" />
				</div>
				<div class="col-12 mt-2"><h5>Parametre Ayarları</h5><hr class="my-2"/></div>
				<div class="col-md-3">
					<label class="form-label">Varsayılan Tarih</label>
					<input type="date" name="default_date" class="form-control" value="<?= htmlspecialchars($config['default_date'] ?? '1900-01-01', ENT_QUOTES) ?>" />
				</div>
				<div class="col-md-3">
					<label class="form-label">Varsayılan Depo ID (boş=hepsi)</label>
					<input type="number" name="default_depo_id" class="form-control" value="<?= htmlspecialchars($config['default_depo_id'] ?? '', ENT_QUOTES) ?>" />
				</div>
				<hr>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="default_only_positive" id="onlypos" <?= !empty($config['default_only_positive']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="onlypos">miktarsifirdanbuyukstoklarlistelensin</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="default_last_barcode_only" id="lastbarcode" <?= !empty($config['default_last_barcode_only']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="lastbarcode">urununsonbarkodulistelensin</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="default_only_ecommerce" id="onlyecom" <?= !empty($config['default_only_ecommerce']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="onlyecom">sadeceeticaretstoklarigetir</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="sync_active" id="sync_active" <?= !empty($config['sync_active']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="sync_active">Senkron Aktif</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="sync_by_depot" id="sync_by_depot" <?= !empty($config['sync_by_depot']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="sync_by_depot">Depo Bazlı Çek</label>
					</div>
				</div>
				<div class="col-md-4">
					<label class="form-label">İstek Aralığı (saniye)</label>
					<input type="number" name="request_interval_sec" class="form-control" value="<?= htmlspecialchars($config['request_interval_sec'] ?? '900', ENT_QUOTES) ?>" />
				</div>
				<div class="form-check form-switch ms-4">
						<input class="form-check-input" type="checkbox" id="autoSync2" <?= !empty($config['sync_active']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="autoSync2">Otomatik Senkron</label>
						
					</div>
					<div class="col-md-4">
					<input type="number" id="intervalSec2" class="form-control"  value="<?= htmlspecialchars($config['request_interval_sec'] ?? '900', ENT_QUOTES) ?>" />
					</div>
					
					
				<div class="card-footer">
					<div class="col-12 d-flex gap-2 flex-wrap">
						<button type="button" id="btnSave" class="btn btn-primary">Kaydet</button>
						<button type="button" id="btnRefresh" class="btn btn-outline-primary">Stokları Çek</button>
						<button type="button" id="btnTokenTest" class="btn btn-light">Token Test</button>
						<button type="button" id="btnDepoSync" class="btn btn-outline">Depoları Senkronize Et</button>
						<button type="button" id="btnDepoPreview" class="btn btn-light">Depo Önizleme</button>
						<button type="button" id="btnStokPreview" class="btn btn-light">Stok Önizleme</button>
						
						
					</div>
				</div>
			</form>
			<pre id="respBox" class="mt-3"></pre>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
const box = document.getElementById('respBox');
document.getElementById('btnSave').addEventListener('click', async ()=>{
	const form = document.getElementById('frmStockCfg');
	const fd = new FormData(form);
	const r = await fetch('/admin/tamsoft-stok/ayarlar', { method:'POST', body: fd });
	const d = await r.json(); box.textContent = JSON.stringify(d,null,2);
});
document.getElementById('btnRefresh').addEventListener('click', async ()=>{
	const cfg = new FormData(document.getElementById('frmStockCfg'));
	const r = await fetch('/admin/tamsoft-stok/refresh', { method:'POST', body: cfg });
	const d = await r.json(); box.textContent = JSON.stringify(d,null,2);
});
document.getElementById('btnTokenTest').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/token-test', { method:'POST' });
	const d = await r.json(); box.textContent = JSON.stringify(d,null,2);
});
document.getElementById('btnDepoSync').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST' });
	const d = await r.json(); box.textContent = JSON.stringify(d,null,2);
});
document.getElementById('btnDepoPreview').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/preview');
	const d = await r.json(); box.textContent = JSON.stringify(d,null,2);
});
document.getElementById('btnStokPreview').addEventListener('click', async ()=>{
	const cfg = new FormData(document.getElementById('frmStockCfg'));
	cfg.append('only_positive', document.getElementById('onlypos').checked ? '1' : '0');
	cfg.append('last_barcode_only', document.getElementById('lastbarcode')?.checked ? '1' : '0');
	cfg.append('only_ecommerce', document.getElementById('onlyecom')?.checked ? '1' : '0');
	const r = await fetch('/admin/tamsoft-stok/stok/preview', { method:'POST', body: cfg });
	const d = await r.json(); box.textContent = JSON.stringify(d,null,2);
});
</script>



