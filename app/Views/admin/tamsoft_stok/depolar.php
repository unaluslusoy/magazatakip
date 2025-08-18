<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Depolar</h3>
			<div class="d-flex gap-2">
				<button class="btn btn-sm btn-outline-secondary" id="btnDebug">Debug</button>
			</div>
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
(async function(){
	let lastDebug = null;
	const dbgModal = new bootstrap.Modal(document.getElementById('dbgModal'));
	const dbgPre = document.getElementById('dbgPre');
	function showDebug(data){ try{ dbgPre.textContent = (typeof data==='string')?data:JSON.stringify(data,null,2); dbgModal.show(); }catch(e){ dbgPre.textContent = String(data); dbgModal.show(); } }
	document.getElementById('btnDebug')?.addEventListener('click', ()=>{ showDebug(lastDebug || 'Debug verisi yok'); });
	async function load(){
		const tb = document.querySelector('#tbDepo tbody'); tb.innerHTML='';
		try {
			const resp = await fetch('/admin/tamsoft-stok/depolar/data', { headers: { 'Accept': 'application/json' } });
			if (!resp.ok) { throw new Error('HTTP '+resp.status); }
			const d = await resp.json(); lastDebug = d;
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
			try{ const d = await r.json(); lastDebug = d; showToast(d.success? 'Güncellendi':'Hata oluştu','info'); }catch(e){}
		}
	});
	const btnSync = document.getElementById('btnSync');
	if (btnSync) btnSync.addEventListener('click', async ()=>{
		btnSync.disabled = true; const old = btnSync.innerText; btnSync.innerText = 'Senkronize ediliyor...';
		const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
		btnSync.disabled = false; btnSync.innerText = old; load();
		try{ const d = await r.json(); lastDebug = d; showToast(d.success? 'Depolar güncellendi':'Hata oluştu','success'); }catch(e){}
	});
	load();
})();
</script>



