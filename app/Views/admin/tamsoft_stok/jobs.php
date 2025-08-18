<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Job Manager</h3>
			<div class="d-flex gap-2 align-items-center">
				<form id="frmNewJob" class="d-flex gap-2">
					<input type="text" class="form-control form-control-sm" name="job_key" placeholder="job_key" required style="width:160px"/>
					<input type="text" class="form-control form-control-sm" name="description" placeholder="Açıklama" style="width:220px"/>
					<input type="text" class="form-control form-control-sm" name="cron_expr" placeholder="*/30 * * * *" required style="width:160px"/>
					<button class="btn btn-sm btn-primary" type="submit">Ekle</button>
				</form>
				<button class="btn btn-sm btn-light" id="btnReload">Yenile</button>
			</div>
		</div>
		<div class="card-body">
			<div class="d-flex gap-2 mb-3">
				<button class="btn btn-sm btn-outline-primary" id="btnScheduleNow">Due Jobları Kuyruğa Al</button>
			</div>
			<div class="table-responsive">
				<table class="table table-striped align-middle" id="tbJobs">
					<thead class="table-dark">
					<tr>
						<th>Job</th>
						<th>Açıklama</th>
						<th>Cron</th>
						<th>Aktif</th>
						<th>Son Çalışma</th>
						<th>Sonraki</th>
						<th>Komut</th>
					</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<hr/>
			<h5>Son Çalıştırmalar</h5>
			<pre id="runsBox" class="bg-light p-3" style="max-height:300px; overflow:auto"></pre>
			<div class="mt-3"></div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
(function(){
	const tb = document.querySelector('#tbJobs tbody');
	async function load(){
		const r = await fetch('/admin/tamsoft-stok/jobs/list');
		const d = await r.json();
		tb.innerHTML = '';
		(d.rows||[]).forEach(j=>{
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td><code>${j.job_key}</code></td>
				<td>${j.description||''}</td>
				<td><code>${j.cron_expr}</code></td>
				<td><input type="checkbox" class="form-check-input toggle" data-key="${j.job_key}" ${j.enabled? 'checked':''}></td>
				<td>${j.last_run_at||'-'}</td>
				<td>${j.next_run_at||'-'}</td>
				<td>
					<button class="btn btn-sm btn-primary run" data-key="${j.job_key}">Çalıştır</button>
					<button class="btn btn-sm btn-outline-danger unlock" data-key="${j.job_key}">Kilit Kaldır</button>
					<button class="btn btn-sm btn-outline-secondary delete-job" data-key="${j.job_key}">Sil</button>
				</td>
			`;
			tb.appendChild(tr);
		});
		loadRuns();
	}
	async function loadRuns(){
		const r = await fetch('/admin/tamsoft-stok/jobs/runs?limit=50');
		const d = await r.json();
		document.getElementById('runsBox').textContent = JSON.stringify(d.rows||[], null, 2);
	}
	const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
	document.addEventListener('change', async (e)=>{
		const el = e.target; if (el && el.classList.contains('toggle')){
			const fd = new FormData(); fd.append('job_key', el.getAttribute('data-key')); fd.append('enabled', el.checked?'1':'0'); fd.append('_csrf', CSRF);
			const r = await fetch('/admin/tamsoft-stok/jobs/toggle', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
			try{ const d = await r.json(); showToast(d.success?'Güncellendi':'Hata','info'); }catch(e){}
		}
	});
	document.addEventListener('click', async (e)=>{
		const el = e.target;
		if (el && el.classList.contains('run')){
			const fd = new FormData(); fd.append('job_key', el.getAttribute('data-key')); fd.append('_csrf', CSRF);
			el.disabled = true; const old=el.innerText; el.innerText = 'Çalışıyor...';
			const r = await fetch('/admin/tamsoft-stok/jobs/run', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
			const d = await r.json(); el.disabled=false; el.innerText=old; showToast(d.success? 'Başarılı':'Hata: '+(d.error||''), d.success?'success':'danger');
			loadRuns();
		}
		if (el && el.classList.contains('unlock')){
			const fd = new FormData(); fd.append('job_key', el.getAttribute('data-key')); fd.append('_csrf', CSRF);
			await fetch('/admin/tamsoft-stok/jobs/lock/release', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
			showToast('Kilit kaldırıldı','success');
		}
		if (el && el.classList.contains('delete-job')){
			const key = el.getAttribute('data-key');
			const fd = new FormData(); fd.append('job_key', key); fd.append('_csrf', CSRF);
			el.disabled = true; const old = el.innerText; el.innerText='Siliniyor...';
			const r = await fetch('/admin/tamsoft-stok/jobs/delete', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
			el.disabled = false; el.innerText = old;
			try{ const d = await r.json(); showToast(d.success?'Silindi':('Hata: '+(d.error||'')), d.success?'success':'danger'); if(d.success){ load(); } }catch(e){}
		}
		if (el && el.id==='btnReload'){ load(); }
		if (el && el.id==='btnScheduleNow'){
			el.disabled = true; const old = el.innerText; el.innerText = 'Çalıştırılıyor...';
			const fd = new FormData(); fd.append('_csrf', CSRF);
			try{
				const r = await fetch('/admin/tamsoft-stok/jobs/schedule-now', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF } });
				const d = await r.json();
				showToast(d.success ? (`Kuyruğa alındı: ${d.enqueued||0}, güncellendi: ${d.updated||0}`) : ('Hata: '+(d.error||'')), d.success?'success':'danger');
				if (d.success) { load(); }
			}catch(e){ showToast('Hata', 'danger'); }
			finally{ el.disabled=false; el.innerText = old; }
		}
		if (el && el.id==='btnPriceEnqueue'){
			el.disabled = true; const old = el.innerText; el.innerText = 'Kuyruğa ekleniyor...';
			try{
				const fd = new FormData(); fd.append('job_key','tamsoft_price_refresh'); fd.append('_csrf', CSRF);
				const r = await fetch('/admin/tamsoft-stok/jobs/run', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
				const d = await r.json();
				showToast(d.success ? 'Fiyat güncelleme kuyruğa alındı' : ('Hata: '+(d.error||'')), d.success?'success':'danger');
				if (d.success) { loadRuns(); }
			}catch(e){ showToast('Hata', 'danger'); }
			finally{ el.disabled=false; el.innerText = old; }
		}
	});
	document.getElementById('frmNewJob').addEventListener('submit', async (e)=>{
		e.preventDefault();
		const fd = new FormData(e.currentTarget);
		fd.append('_csrf', CSRF);
		const btn = e.currentTarget.querySelector('button[type="submit"]');
		btn.disabled = true; const old = btn.innerText; btn.innerText = 'Ekleniyor...';
		const r = await fetch('/admin/tamsoft-stok/jobs/create', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
		btn.disabled = false; btn.innerText = old;
		try{ const d = await r.json(); showToast(d.success?'Job eklendi':('Hata: '+(d.error||'')), d.success?'success':'danger'); if (d.success) { e.currentTarget.reset(); load(); } }catch(e){}
	});
	load();
})();
</script>



