<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Tamsoft Stok (Sadece Stok Senkronu)</h3>
			<div class="d-flex gap-2">
				<button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#advancedBox">Gelişmiş</button>
				<button type="button" id="btnDebug" class="btn btn-sm btn-outline-secondary">Debug</button>
			</div>
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
						<label class="form-check-label" for="onlypos">Sadece stoklu ürünleri getir</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="default_last_barcode_only" id="lastbarcode" <?= !empty($config['default_last_barcode_only']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="lastbarcode">Sadece son barkodu getir</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="default_only_ecommerce" id="onlyecom" <?= !empty($config['default_only_ecommerce']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="onlyecom">Sadece e-ticaret ürünlerini getir</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="sync_active" id="sync_active" <?= !empty($config['sync_active']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="sync_active">Senkron aktif</label>
					</div>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="sync_by_depot" id="sync_by_depot" <?= !empty($config['sync_by_depot']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="sync_by_depot">Depo bazlı çekim</label>
					</div>
				</div>
				<div class="col-md-4">
					<label class="form-label">İstek Aralığı (saniye)</label>
					<input type="number" name="request_interval_sec" class="form-control" value="<?= htmlspecialchars($config['request_interval_sec'] ?? '900', ENT_QUOTES) ?>" />
				</div>
				<div class="collapse mt-2" id="advancedBox">
				<div class="col-12"><h6>İleri Seviye (Performans)</h6><hr class="my-2"/></div>
				<div class="col-md-2">
					<label class="form-label">Master Batch</label>
					<input type="number" name="master_batch" class="form-control" value="<?= htmlspecialchars($config['master_batch'] ?? '', ENT_QUOTES) ?>" placeholder="500" />
				</div>
				<div class="col-md-2">
					<label class="form-label">Price Batch</label>
					<input type="number" name="price_batch" class="form-control" value="<?= htmlspecialchars($config['price_batch'] ?? '', ENT_QUOTES) ?>" placeholder="100" />
				</div>
				<div class="col-md-2">
					<label class="form-label">Price Max Pages</label>
					<input type="number" name="price_max_pages" class="form-control" value="<?= htmlspecialchars($config['price_max_pages'] ?? '', ENT_QUOTES) ?>" placeholder="200" />
				</div>
				<div class="col-md-2">
					<label class="form-label">Price Max Seconds</label>
					<input type="number" name="price_max_seconds" class="form-control" value="<?= htmlspecialchars($config['price_max_seconds'] ?? '', ENT_QUOTES) ?>" placeholder="0=limitsiz" />
				</div>
				<div class="col-md-2">
					<label class="form-label">Depot Max Seconds</label>
					<input type="number" name="max_seconds_per_depot" class="form-control" value="<?= htmlspecialchars($config['max_seconds_per_depot'] ?? '', ENT_QUOTES) ?>" placeholder="180" />
				</div>
				<div class="col-md-2">
					<label class="form-label">Depot Max Pages</label>
					<input type="number" name="max_pages_per_depot" class="form-control" value="<?= htmlspecialchars($config['max_pages_per_depot'] ?? '', ENT_QUOTES) ?>" placeholder="200" />
				</div>
				<div class="col-12 mt-2"><h6>Zamanlama</h6><hr class="my-2"/></div>
				<div class="col-md-3">
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" id="autoSync2" <?= !empty($config['sync_active']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="autoSync2">Otomatik senkron (UI)</label>
					</div>
				</div>
				<div class="col-md-3">
					<input type="number" id="intervalSec2" class="form-control"  value="<?= htmlspecialchars($config['request_interval_sec'] ?? '900', ENT_QUOTES) ?>" placeholder="İstek aralığı (sn)" />
				</div>
				</div>
				<div class="col-12 mt-2">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="bulk_stock_summary" id="bulk_stock_summary" <?= !empty($config['bulk_stock_summary']) ? 'checked' : '' ?> />
						<label class="form-check-label" for="bulk_stock_summary">Stok özeti güncellemesini toplu (bulk) modda yap</label>
					</div>
				</div>
				
				
			<div class="card-footer">
				<div class="col-12 d-flex gap-2 flex-wrap">
					<button type="button" id="btnSave" class="btn btn-primary">Kaydet</button>
				
				</div>
			</div>
			</form>
			<pre id="respBox" class="mt-3 d-none"></pre>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<!-- Debug Modal -->
<div class="modal fade" id="dbgModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Debug Çıktısı</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
			</div>
			<div class="modal-body">
				<pre id="dbgPre" class="bg-light p-3" style="max-height:60vh; overflow:auto"></pre>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
			</div>
		</div>
	</div>
</div>
<script>
const box = document.getElementById('respBox');
const dbgModal = new bootstrap.Modal(document.getElementById('dbgModal'));
const dbgPre = document.getElementById('dbgPre');
function showDebug(data){ try{ const txt = (typeof data==='string')?data:JSON.stringify(data,null,2); box.textContent = txt; dbgPre.textContent = txt; dbgModal.show(); }catch(e){ box.textContent = String(data); } }
document.getElementById('btnDebug')?.addEventListener('click', ()=>{ const txt = box.textContent || 'Debug verisi yok'; dbgPre.textContent = txt; dbgModal.show(); });
const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
document.getElementById('btnSave').addEventListener('click', async ()=>{
	const form = document.getElementById('frmStockCfg');
	const fd = new FormData(form);
	fd.append('_csrf', CSRF);
	const r = await fetch('/admin/tamsoft-stok/ayarlar', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json(); showDebug(d);
	try { showToast(d.success ? 'Kaydedildi' : ('Hata: '+(d.error||' bilinmeyen hata')), d.success?'success':'danger'); } catch(e){}
});
document.getElementById('btnRefresh').addEventListener('click', async ()=>{
	const cfg = new FormData(document.getElementById('frmStockCfg'));
	cfg.append('_csrf', CSRF);
	document.getElementById('btnRefresh').disabled = true;
	document.getElementById('btnRefresh').innerText = 'Çekiliyor...';
	const r = await fetch('/admin/tamsoft-stok/refresh', { method:'POST', body: cfg, headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json(); showDebug(d);
	document.getElementById('btnRefresh').disabled = false;
	document.getElementById('btnRefresh').innerText = 'Stokları Çek';
	try { showToast(d.success ? 'Stok senkron tamamlandı' : ('Hata: '+(d.error||'')), d.success?'success':'danger', 6000); } catch(e){}
});
document.getElementById('btnTokenTest').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/token-test', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json(); showDebug(d);
	try { showToast(d.success ? 'Token OK' : 'Token alınamadı', d.success?'success':'danger'); } catch(e){}
});
document.getElementById('btnDepoSync').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json(); showDebug(d);
	try { showToast(d.success ? 'Depolar senkronize edildi' : ('Hata: '+(d.error||'')), d.success?'success':'danger'); } catch(e){}
});
document.getElementById('btnDepoPreview').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/preview');
	const d = await r.json(); showDebug(d);
});
document.getElementById('btnStokPreview').addEventListener('click', async ()=>{
	const cfg = new FormData(document.getElementById('frmStockCfg'));
	cfg.append('only_positive', document.getElementById('onlypos').checked ? '1' : '0');
	cfg.append('last_barcode_only', document.getElementById('lastbarcode')?.checked ? '1' : '0');
	cfg.append('only_ecommerce', document.getElementById('onlyecom')?.checked ? '1' : '0');
	cfg.append('_csrf', CSRF);
	const r = await fetch('/admin/tamsoft-stok/stok/preview', { method:'POST', body: cfg, headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json(); showDebug(d);
	try { showToast(d.success ? ('Önizleme: '+(d.preview_count||0)+' satır') : ('Hata: '+(d.error||'')), d.success?'info':'danger'); } catch(e){}
});
document.getElementById('btnPriceRefresh').addEventListener('click', async ()=>{
    const btn = document.getElementById('btnPriceRefresh');
    btn.disabled = true; const old = btn.innerText; btn.innerText = 'Çalışıyor...';
    try{
        const fd = new FormData();
        const form = document.getElementById('frmStockCfg');
        const tarih = form.querySelector('input[name="default_date"]').value;
        const depo = form.querySelector('input[name="default_depo_id"]').value;
        if (tarih) fd.append('tarih', tarih);
        if (depo) fd.append('depoid', depo);
        fd.append('_csrf', CSRF);
        const r = await fetch('/admin/tamsoft-stok/price-refresh', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
        const d = await r.json();
        showDebug(d);
        showToast(d.success ? (`Fiyat güncellendi. updated=${d.updated||0}`) : ('Hata: '+(d.error||'')), d.success?'success':'danger');
    }catch(e){ showToast('Hata', 'danger'); }
    finally{ btn.disabled=false; btn.innerText = old; }
});
</script>



