<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Depolar</h3>
			
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-striped align-middle" id="tbDepo">
					<thead class="table-dark"><tr><th>ID</th><th>Depo Adı</th><th>Aktif</th></tr></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
(async function(){
	async function load(){
		const tb = document.querySelector('#tbDepo tbody'); tb.innerHTML='';
		try {
			const resp = await fetch('/admin/tamsoft-stok/depolar/data', { headers: { 'Accept': 'application/json' } });
			if (!resp.ok) { throw new Error('HTTP '+resp.status); }
			const d = await resp.json();
			if (d && d.success && Array.isArray(d.rows)) {
				(d.rows||[]).forEach(row=>{
					const tr = document.createElement('tr');
					tr.innerHTML = `
						<td>${row.id}</td>
						<td>${row.depo_adi ?? ''}</td>
						<td><input type="checkbox" class="form-check-input" data-id="${row.id}" ${row.aktif? 'checked':''}></td>
					`;
					tb.appendChild(tr);
				});
			} else {
				console.error('Depolar veri hatası:', d);
				showError('Depo listesi yüklenemedi. Lütfen sayfayı yenileyin.');
			}
		} catch (e) {
			console.error('Depolar fetch hatası:', e);
			showError('Oturum süresi dolmuş olabilir veya ağ hatası. Lütfen sayfayı yenileyin.');
		}
	}
	function showError(msg){
		let el = document.getElementById('depoErr');
		if(!el){
			el = document.createElement('div');
			el.id = 'depoErr';
			el.className = 'alert alert-warning my-3';
			document.querySelector('.card-body').prepend(el);
		}
		el.textContent = msg;
	}
	const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
	document.addEventListener('change', async (e)=>{
		const el = e.target; if (el && el.matches('input[type="checkbox"][data-id]')){
			const fd = new FormData(); fd.append('id', el.getAttribute('data-id')); fd.append('aktif', el.checked ? '1':'0'); fd.append('_csrf', CSRF);
			const r = await fetch('/admin/tamsoft-stok/depolar/set-active', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
			try{ const d = await r.json(); showToast(d.success? 'Güncellendi':'Hata oluştu','info'); }catch(e){}
		}
	});
	const btnSync = document.getElementById('btnSync');
	if (btnSync) btnSync.addEventListener('click', async ()=>{
		btnSync.disabled = true; const old = btnSync.innerText; btnSync.innerText = 'Senkronize ediliyor...';
		const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
		btnSync.disabled = false; btnSync.innerText = old; load();
		try{ const d = await r.json(); showToast(d.success? 'Depolar güncellendi':'Hata oluştu','success'); }catch(e){}
	});
	load();
})();
</script>



