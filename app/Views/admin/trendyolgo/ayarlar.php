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
				<div class="col-md-6">
					<label class="form-label">Default Store</label>
					<select name="default_store_id" class="form-select">
						<option value="">Seçiniz</option>
						<?php foreach (($magazalar ?? []) as $m): $sid = (string)($m['store_id'] ?? ''); $sel = ((string)($ayarlar['default_store_id'] ?? '') === $sid) ? 'selected' : ''; ?>
							<option value="<?= htmlspecialchars($sid) ?>" <?= $sel ?>><?= htmlspecialchars(($m['magaza_adi'] ?? '') . ' (#' . $sid . ')') ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-6">
					<label class="form-label">Cron Periyodu (dakika)</label>
					<input name="schedule_minutes" type="number" min="0" class="form-control" value="<?= (int)($ayarlar['schedule_minutes'] ?? 0) ?>" />
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
			<h3 class="card-title mb-0">Ürün İçeri Al – Önizleme ve İşlemler</h3>
			<div class="d-flex gap-2">
				<select id="previewStoreId" class="form-select form-select-sm" style="width: 260px;">
					<option value="">Mağaza seçiniz</option>
					<?php foreach (($magazalar ?? []) as $m): $sid = (string)($m['store_id'] ?? ''); $sel = ((string)($ayarlar['default_store_id'] ?? '') === $sid) ? 'selected' : ''; ?>
						<option value="<?= htmlspecialchars($sid) ?>" <?= $sel ?>><?= htmlspecialchars(($m['magaza_adi'] ?? '') . ' (#' . $sid . ')') ?></option>
					<?php endforeach; ?>
				</select>
				<button class="btn btn-sm btn-light" id="btnPreview">Önizleme</button>
				<button class="btn btn-sm btn-primary" id="btnImportAll">Tümünü İçe Al</button>
				<button class="btn btn-sm btn-outline-secondary" id="btnCronTrigger" title="Cronu manuel tetikle">Cronu Tetikle</button>
			</div>
		</div>
		<div class="card-body">
			<div id="importPreviewBox" style="display:none">
				<div class="alert alert-info">Önizleme - ilk 10 kayıt</div>
				<div class="table-responsive">
					<table class="table table-row-dashed align-middle">
						<thead>
							<tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
								<th>Ürün</th>
								<th>Barcode</th>
								<th>SKU/Kod</th>
								<th>Marka</th>
								<th>Kategori</th>
							</tr>
						</thead>
						<tbody id="ayarPreviewTbody"></tbody>
					</table>
				</div>
			</div>
			<div id="cronSummaryBox" style="display:none" class="mt-4">
				<div class="alert alert-secondary">Tüm şubeler için içe alma özeti</div>
				<div class="table-responsive">
					<table class="table table-row-dashed align-middle">
						<thead>
							<tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
								<th>Mağaza</th>
								<th>Store ID</th>
								<th>Sayfa</th>
								<th>Eklenen</th>
								<th>Hata</th>
								<th>Beklenen Toplam</th>
								<th>Çekilen</th>
							</tr>
						</thead>
						<tbody id="cronSummaryTbody"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="card mt-4">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Mağaza Senkron Durumu</h3>
		</div>
		<div class="card-body">
			<?php if (empty($magazalar)): ?>
				<div class="alert alert-info">Henüz mağaza eklenmemiş.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-row-dashed align-middle">
						<thead>
							<tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
								<th>Mağaza</th>
								<th>Store ID</th>
								<th>Son Senkron</th>
								<th>Son Başarılı</th>
								<th class="text-end">Aksiyon</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach (($magazalar ?? []) as $m): ?>
							<tr>
								<td><?= htmlspecialchars($m['magaza_adi'] ?? '-') ?></td>
								<td><?= htmlspecialchars($m['store_id'] ?? '-') ?></td>
								<td><?= htmlspecialchars($m['last_sync'] ?? '-') ?></td>
								<td><?= htmlspecialchars($m['last_success'] ?? '-') ?></td>
								<td class="text-end"><button class="btn btn-sm btn-outline-primary btnStoreImport" data-store="<?= htmlspecialchars($m['store_id'] ?? '') ?>">Bu Mağazayı İçe Al</button></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
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
// Servis test

document.getElementById('btnHealth')?.addEventListener('click', async function(){
	try {
		const res = await fetch('/admin/trendyolgo/health');
		const d = await res.json();
		const ok = d.service_ok ? 'Evet' : 'Hayır';
		const base = d.base_url || '-';
		const text = `Aktif: ${d.enabled ? 'Evet' : 'Hayır'}\nÇalışıyor: ${ok}\nBase URL: ${base}`;
		const icon = d.enabled && d.service_ok ? 'success' : (d.enabled ? 'warning' : 'info');
		if (window.Swal) {
			Swal.fire({ toast:true, position:'top-end', icon:icon, title:'Trendyol GO Servis Testi', text:text, showConfirmButton:false, timer:4000 });
		} else { console.log(text); }
	} catch (e) { alert('Test başarısız: ' + e.message); }
});

// Önizleme ve içe alma

document.getElementById('btnPreview')?.addEventListener('click', async function(){
	try {
		const storeId = document.getElementById('previewStoreId').value.trim();
		const res = await fetch('/admin/trendyolgo/urunler/import-trigger' + (storeId?`?store_id=${encodeURIComponent(storeId)}`:''), { method:'POST' });
		const data = await res.json();
		const box = document.getElementById('importPreviewBox');
		const tbody = document.getElementById('ayarPreviewTbody');
		tbody.innerHTML = '';
		(data.preview||[]).forEach(p => {
			const tr = document.createElement('tr');
			tr.innerHTML = `<td>${(p.name||'-')}</td><td>${(p.barcode||'-')}</td><td>${(p.sku||p.code||'-')}</td><td>${(p.brand||'-')}</td><td>${(p.categoryName||p.categoryId||'-')}</td>`;
			tbody.appendChild(tr);
		});
		box.style.display = 'block';
		if ((data.preview||[]).length === 0 && window.Swal) {
			Swal.fire({ toast:true, position:'top-end', icon:'warning', title:'Önizleme boş', showConfirmButton:false, timer:2500 });
		}
	} catch (e) { alert('Önizleme başarısız: '+e.message); }
});

document.getElementById('btnImportAll')?.addEventListener('click', async function(){
	if (!confirm('Seçili mağaza için tüm ürünler içe alınsın mı?')) return;
	try {
		const storeId = document.getElementById('previewStoreId').value.trim();
		const form = new FormData();
		form.append('store_id', storeId);
		form.append('per', '200');
		form.append('max_pages', '100');
		const res = await fetch('/admin/trendyolgo/urunler/import', { method:'POST', body: form });
		const d = await res.json();
		if (d.success && window.Swal) {
			Swal.fire({ toast:true, position:'top-end', icon:'success', title:`${d.inserted_or_updated} ürün kaydedildi`, showConfirmButton:false, timer:3000 });
		}
	} catch (e) { alert('İçe alma başarısız: '+e.message); }
});

// Cron tetikleme – gerçek import-all endpoint

document.getElementById('btnCronTrigger')?.addEventListener('click', async function(){
	try {
		this.disabled = true;
		this.innerText = 'Çalışıyor...';
		const form = new FormData();
		form.append('per', '200');
		form.append('max_pages', '100');
		const res = await fetch('/admin/trendyolgo/cron/import-all', { method:'POST', body: form });
		const d = await res.json();
		this.disabled = false;
		this.innerText = 'Cronu Tetikle';
		if (!d.success) { alert('Cron: ' + (d.error||'başarısız')); return; }
		const tbody = document.getElementById('cronSummaryTbody');
		tbody.innerHTML = '';
		(d.summary||[]).forEach(s => {
			const tr = document.createElement('tr');
			tr.innerHTML = `<td>${s.name||''}</td><td>${s.store_id||''}</td><td>${s.pages||0}</td><td>${s.inserted||0}</td><td>${s.errors||0}</td><td>${s.expected_total||0}</td><td>${s.fetched||0}</td>`;
			tbody.appendChild(tr);
		});
		document.getElementById('cronSummaryBox').style.display = '';
		if (window.Swal) { Swal.fire({ toast:true, position:'top-end', icon:'info', title:'Cron tamamlandı', showConfirmButton:false, timer:2500 }); }
	} catch (e) {
		this.disabled = false;
		this.innerText = 'Cronu Tetikle';
		alert('Cron tetiklenemedi: '+e.message);
	}
});

// Mağaza senkron: tek mağaza içe alma

document.querySelectorAll('.btnStoreImport')?.forEach(btn => {
	btn.addEventListener('click', async function(){
		const sid = this.getAttribute('data-store') || '';
		if (!sid) return;
		if (!confirm(`#${sid} mağazası için içe alma başlatılsın mı?`)) return;
		try {
			this.disabled = true;
			const form = new FormData();
			form.append('store_id', sid);
			form.append('per', '200');
			form.append('max_pages', '100');
			const res = await fetch('/admin/trendyolgo/urunler/import', { method:'POST', body: form });
			const d = await res.json();
			this.disabled = false;
			if (d.success) {
				if (window.Swal) Swal.fire({ toast:true, position:'top-end', icon:'success', title:`#${sid} mağaza: ${d.inserted_or_updated} ürün güncellendi`, showConfirmButton:false, timer:2500 });
				location.reload();
			} else {
				alert('İçe alma hatası: ' + (d.error||'bilinmeyen'));
			}
		} catch (e) {
			this.disabled = false;
			alert('İçe alma başarısız: ' + e.message);
		}
	});
});
</script>
<script>
// Loglar aynı kaldı
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
				<td style="max-width:420px;word-break:break-all;"><pre class="bg-light p-2 mb-0" style="white-space:pre-wrap;max-height:140px;overflow:auto;">${row.body_preview || row.message || ''}</pre></td>`;
			tbody.appendChild(tr);
		});
	} catch (e) { console.error(e); }
}
document.getElementById('btnLogsYenile')?.addEventListener('click', loadLogs);
document.getElementById('btnLogsTemizle')?.addEventListener('click', async function(){
	if (!confirm('Logları temizlemek istediğinize emin misiniz?')) return;
	const res = await fetch('/admin/trendyolgo/loglar/temizle', { method: 'POST' });
	const d = await res.json();
	if (d.success) { if (window.Swal) { Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Loglar temizlendi', showConfirmButton:false, timer:2500 }); } loadLogs(); }
});
loadLogs();
</script>

