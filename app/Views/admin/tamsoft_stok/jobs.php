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
				<button class="btn btn-sm btn-outline-secondary" id="btnDebug">Debug</button>
			</div>
		</div>
		<div class="card-body">
			<h5 class="mb-2">Hızlı Aksiyonlar</h5>
			<div class="d-flex flex-wrap gap-2 mb-4">
				<button class="btn btn-sm btn-outline-primary" id="btnScheduleNow">Due Jobları Kuyruğa Al</button>
				<button class="btn btn-sm btn-outline-secondary" id="btnLoadCron">Sunucu Cron'u Göster</button>
				<div class="input-group input-group-sm" style="width:320px">
					<span class="input-group-text">Tarih</span>
					<input type="text" class="form-control" id="inpEcomDate" placeholder="YYYY-MM-DD (opsiyonel)">
					<button class="btn btn-success" id="btnEnqActiveDepots" type="button">Aktif Depoları Kuyruğa Al</button>
				</div>
			</div>
			<h5 class="mb-2">Planlanmış İşler</h5>
			<div class="table-responsive mb-4">
				<table class="table table-striped table-sm align-middle" id="tbJobs">
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
			<style>
				/* Tablo hücre iç boşlukları (padding) */
				#tbJobs th, #tbJobs td { padding: .4rem .75rem; }
			</style>
			<h5 class="mb-2">Son Çalıştırmalar</h5>
			<pre id="runsBox" class="bg-light p-3" style="max-height:300px; overflow:auto"></pre>
			<div class="mt-3"></div>
			<div class="mt-4">
				<h5 class="mb-2">Sunucu Cron (okunur)</h5>
				<div class="d-flex gap-2 mb-2">
					<button class="btn btn-sm btn-light" id="btnCopyCron">Kopyala</button>
				</div>
				<pre id="cronBox" class="bg-light p-3" style="max-height:260px; overflow:auto"></pre>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
(function(){
	// Basit toast yardımcıları (fallback)
	if (typeof window.showToast !== 'function') {
		window.showToast = function(message, type){
			try {
				const colors = { success: '#198754', danger: '#dc3545', info: '#0d6efd', warning: '#ffc107' };
				const box = document.createElement('div');
				box.textContent = message || '';
				box.style.position = 'fixed';
				box.style.right = '16px';
				box.style.top = '16px';
				box.style.zIndex = 1080;
				box.style.background = (colors[type]||'#0d6efd');
				box.style.color = '#fff';
				box.style.padding = '10px 12px';
				box.style.borderRadius = '6px';
				box.style.boxShadow = '0 2px 10px rgba(0,0,0,.2)';
				document.body.appendChild(box);
				setTimeout(()=>{ box.remove(); }, 3000);
			} catch (e) { alert(message); }
		}
	}
	const tb = document.querySelector('#tbJobs tbody');
	let lastDebug = null;
	const dbgModal = new bootstrap.Modal(document.getElementById('dbgModal'));
	const dbgPre = document.getElementById('dbgPre');
	// İlerleme modali
	let progressModal = null;
	try { progressModal = new bootstrap.Modal(document.getElementById('progressModal')); } catch (e) {}
	function showProgress(title, text){
		try{
			document.getElementById('progressTitle').textContent = title || 'İşlem çalışıyor';
			document.getElementById('progressText').textContent = text || '';
			document.getElementById('progressSummary').textContent = '';
			const badge = document.getElementById('progressStatus');
			badge.className = 'badge bg-secondary';
			badge.textContent = 'ÇALIŞIYOR';
			progressModal?.show();
		}catch(e){}
	}
	function completeProgress(success, summary){
		try{
			const el = document.getElementById('progressSummary');
			el.textContent = summary || (success? 'Tamamlandı' : 'Hata oluştu');
			const badge = document.getElementById('progressStatus');
			badge.className = 'badge ' + (success? 'bg-success' : 'bg-danger');
			badge.textContent = success? 'BAŞARILI' : 'HATA';
		}catch(e){}
	}
	// Overlay yardımcıları
	function showOverlay(){ try{ document.getElementById('pageLoaderOverlay')?.classList.remove('d-none'); }catch(e){} }
	function hideOverlay(){ try{ document.getElementById('pageLoaderOverlay')?.classList.add('d-none'); }catch(e){} }
	// Son işlem özeti alanı
	function setLastSummary(html){
		try{
			let box = document.getElementById('lastActionSummary');
			if (!box) {
				const host = document.querySelector('.card-body');
				const wrap = document.createElement('div');
				wrap.className = 'mb-4';
				wrap.innerHTML = '<h6 class="mb-1">Son İşlem Özeti</h6><div id="lastActionSummary" class="small text-muted border rounded p-2 bg-light"></div>';
				host.insertBefore(wrap, host.querySelector('.table-responsive'));
				box = wrap.querySelector('#lastActionSummary');
			}
			box.innerHTML = html || '';
		}catch(e){}
	}
	function showDebug(data){ try{ dbgPre.textContent = (typeof data==='string')?data:JSON.stringify(data,null,2); dbgModal.show(); }catch(e){ dbgPre.textContent = String(data); dbgModal.show(); } }
	document.getElementById('btnDebug')?.addEventListener('click', ()=>{ showDebug(lastDebug || 'Debug verisi yok'); });
	async function load(){
		try{
			const r = await fetch('/admin/tamsoft-stok/jobs/list');
			if (!r.ok) { showToast('Jobs listesi alınamadı ('+r.status+')','danger'); return; }
			const d = await r.json(); lastDebug = d;
			tb.innerHTML = '';
			const rows = d && Array.isArray(d.rows) ? d.rows : [];
			if (rows.length === 0) { showToast('Planlanmış iş bulunamadı','warning'); }
			rows.forEach(j=>{
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
		} catch (e) {
			showToast('Jobs yükleme hatası','danger');
		}
	}
	async function loadRuns(){
		const r = await fetch('/admin/tamsoft-stok/jobs/runs?limit=50');
		const d = await r.json(); lastDebug = d;
		document.getElementById('runsBox').textContent = JSON.stringify(d.rows||[], null, 2);
	}
	const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
	document.addEventListener('change', async (e)=>{
		const el = e.target; if (el && el.classList.contains('toggle')){
			const fd = new FormData(); fd.append('job_key', el.getAttribute('data-key')); fd.append('enabled', el.checked?'1':'0'); fd.append('_csrf', CSRF);
			const r = await fetch('/admin/tamsoft-stok/jobs/toggle', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
			try{ const d = await r.json(); lastDebug = d; showToast(d.success?'Güncellendi':'Hata','info'); }catch(e){}
		}
	});
	document.addEventListener('click', async (e)=>{
		const el = e.target;
		if (el && el.classList.contains('run')){
			const fd = new FormData(); fd.append('job_key', el.getAttribute('data-key')); fd.append('_csrf', CSRF);
			el.disabled = true; const old=el.innerText; el.innerText = 'Çalışıyor...';
			showProgress('Job Kuyruğa Alınıyor', 'Job: '+(el.getAttribute('data-key')||''));
			let d = null; let ok=false; let err='';
			try{
				const r = await fetch('/admin/tamsoft-stok/jobs/run', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
				ok = r.ok;
				if (!r.ok) { err = 'HTTP '+r.status+' '+(r.statusText||''); try{ err += ' | '+(await r.text()).slice(0,200); }catch(e){} }
				else { try{ d = await r.json(); }catch(e){ err='Geçersiz JSON'; } }
			} catch(e){ err = String(e); }
			el.disabled=false; el.innerText=old;
			if (!ok || !d) { showToast('Hata: '+err, 'danger'); completeProgress(false, err); return; }
			lastDebug = d; showToast(d.success? 'Başarılı':'Hata: '+(d.error||''), d.success?'success':'danger');
			completeProgress(!!d.success, d.success ? 'Job kuyruğa alındı' : ('Hata: '+(d.error||'')) );
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
			try{ const d = await r.json(); lastDebug = d; showToast(d.success?'Silindi':('Hata: '+(d.error||'')), d.success?'success':'danger'); if(d.success){ load(); } }catch(e){}
		}
		if (el && el.id==='btnReload'){ load(); }
		if (el && el.id==='btnScheduleNow'){
			el.disabled = true; const old = el.innerText; el.innerText = 'Çalıştırılıyor...';
			const fd = new FormData(); fd.append('_csrf', CSRF);
			try{
				showProgress('Due Joblar Kuyruğa Alınıyor', 'Planlanan işler kontrol ediliyor...');
				const r = await fetch('/admin/tamsoft-stok/jobs/schedule-now', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF } });
				if (!r.ok) {
					let tx=''; try{ tx=(await r.text()).slice(0,200); }catch(e){}
					showToast('Hata: HTTP '+r.status, 'danger'); completeProgress(false, 'HTTP '+r.status+' '+(r.statusText||'')+' '+tx); return;
				}
				let d=null; try{ d = await r.json(); }catch(e){ showToast('Hata: JSON', 'danger'); completeProgress(false, 'Geçersiz JSON'); return; }
				lastDebug = d;
				showToast(d.success ? (`Kuyruğa alındı: ${d.enqueued||0}, güncellendi: ${d.updated||0}`) : ('Hata: '+(d.error||'')), d.success?'success':'danger');
				completeProgress(!!d.success, d.success ? (`Enqueued=${d.enqueued||0}, Updated=${d.updated||0}`) : ('Hata: '+(d.error||'')) );
				if (d.success) { load(); }
			}catch(e){ showToast('Hata', 'danger'); completeProgress(false, String(e)); }
			finally{ el.disabled=false; el.innerText = old; }
		}
		if (el && el.id==='btnEnqActiveDepots'){
			el.disabled = true; const old = el.innerText; el.innerText='Kuyruğa alınıyor...';
			const fd = new FormData();
			const dt = document.getElementById('inpEcomDate')?.value?.trim();
			if (dt) { fd.append('tarih', dt); }
			const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
			fd.append('_csrf', CSRF);
			try{
				showProgress('Aktif Depolar Kuyruğa Alınıyor', dt? ('Tarih: '+dt) : 'Varsayılan tarih');
				const r = await fetch('/admin/tamsoft-stok/jobs/enqueue-active-depots', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF }});
				if (!r.ok) {
					let tx=''; try{ tx=(await r.text()).slice(0,200); }catch(e){}
					showToast('Hata: HTTP '+r.status, 'danger'); completeProgress(false, 'HTTP '+r.status+' '+(r.statusText||'')+' '+tx); return;
				}
				let d=null; try{ d = await r.json(); }catch(e){ showToast('Hata: JSON','danger'); completeProgress(false, 'Geçersiz JSON'); return; }
				lastDebug=d;
				showToast(d.success?(`Enqueued: ${d.enqueued||0}`):('Hata: '+(d.error||'')), d.success?'success':'danger');
				const depots = Array.isArray(d.depots)? d.depots.slice(0,10).join(',') : '';
				completeProgress(!!d.success, d.success ? (`Enqueued=${d.enqueued||0}${depots? (' | Depolar: '+depots) : ''}`) : ('Hata: '+(d.error||'')) );
			}catch(e){ showToast('Hata','danger'); completeProgress(false, String(e)); }
			finally{ el.disabled=false; el.innerText=old; }
		}
		if (el && el.id==='btnPriceEnqueue'){
			el.disabled = true; const old = el.innerText; el.innerText = 'Kuyruğa ekleniyor...';
			try{
				const fd = new FormData(); fd.append('job_key','tamsoft_price_refresh'); fd.append('_csrf', CSRF);
				const r = await fetch('/admin/tamsoft-stok/jobs/run', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
				const d = await r.json(); lastDebug = d;
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
	// Cron listesi göster/kopyala
	document.getElementById('btnLoadCron')?.addEventListener('click', async ()=>{
		try{
			const r = await fetch('/admin/tamsoft-stok/cron/list');
			const d = await r.json();
			document.getElementById('cronBox').textContent = d && d.success ? (d.output||[]).join('\n') : 'Okunamadı';
		}catch(e){ document.getElementById('cronBox').textContent = 'Hata'; }
	});
	document.getElementById('btnCopyCron')?.addEventListener('click', ()=>{
		const txt = document.getElementById('cronBox').textContent || '';
		navigator.clipboard?.writeText(txt);
		showToast('Kopyalandı','info');
	});
})();
</script>
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

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="progressTitle">İşlem çalışıyor</h5>
				<span id="progressStatus" class="badge bg-secondary ms-2">ÇALIŞIYOR</span>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
			</div>
			<div class="modal-body">
				<p id="progressText" class="mb-2"></p>
				<pre id="progressSummary" class="bg-light p-3" style="max-height:40vh; overflow:auto"></pre>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
			</div>
		</div>
	</div>
</div>



