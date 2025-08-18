<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Import (Manuel) - Excel, CSV, XML, TXT, JSON</h3>
		</div>
		<div class="card-body">
			<form id="frmImport" class="row g-3" enctype="multipart/form-data">
				<div class="col-md-6">
					<label class="form-label">Dosya</label>
					<input type="file" name="file" class="form-control" accept=".xls,.xlsx,.csv,.xml,.txt,.json" required />
				</div>
				<div class="col-12">
					<button type="button" id="btnRun" class="btn btn-primary">Yükle ve İşle</button>
				</div>
			</form>
			<pre id="respBox" class="mt-3"></pre>
			<div class="text-muted mt-2">Kolonlar: ext_urun_id|UrunKodu, Barkod, UrunAdi, Birim, KDV, Fiyat, DepoID|depo_id, Miktar|miktar</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
document.getElementById('btnRun').addEventListener('click', async ()=>{
	const form = document.getElementById('frmImport');
	const fd = new FormData(form);
	const f = form.querySelector('input[type="file"][name="file"]').files[0];
	const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
	if (!f) { showToast('Dosya seçiniz', 'danger'); return; }
	if (f && f.size > 25*1024*1024) { showToast('Dosya çok büyük (25MB üzeri)', 'danger'); return; }
	fd.append('_csrf', CSRF);
	const r = await fetch('/admin/tamsoft-stok/import', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
</script>


