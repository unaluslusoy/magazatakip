<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Tamsoft ERP - Dashboard</h3>
			<div class="d-flex gap-2">
				<a href="/admin/tamsoft-stok/jobs" class="btn btn-sm btn-light">Job Manager</a>
				<button class="btn btn-sm btn-outline-secondary" id="btnDebug">Debug</button>
			</div>
		</div>
		<div class="card-body">
			<div class="d-flex flex-wrap gap-2 mb-3">
				<!-- Depo Listesi (DepoListesi) -->
				<button class="btn btn-sm btn-outline-primary" id="btnDepoListPreview">Depo Listesi Önizleme (4)</button>
				<button class="btn btn-sm btn-primary" id="btnDepoListRun">Depo Listesini Çek (manuel)</button>
				<!-- Master Stok (StokListesi) -->
				<button class="btn btn-sm btn-outline-primary" id="btnMasterPreview">Master Stok Önizleme (4)</button>
				<button class="btn btn-sm btn-primary" id="btnMasterRun">Master Stoku Çek (manuel)</button>
				<!-- Fiyat Güncelleme (StokListesi) -->
				<button class="btn btn-sm btn-outline-success" id="btnPricePreview">Fiyat Önizleme (4)</button>
				<button class="btn btn-sm btn-success" id="btnPriceRefreshManual">Fiyat Güncelle (manuel)</button>
				<!-- E-ticaret Stok (EticaretStokListesi) -->
				<button class="btn btn-sm btn-outline-primary" id="btnEcomPreview">E-ticaret Stok Önizleme (4)</button>
				<button class="btn btn-sm btn-primary" id="btnEcomRun">E-ticaret Stok Çek (aktif depolar)</button>
			</div>

			<div class="row g-3">
				<div class="col-md-3">
					<div class="card border">
						<div class="card-body">
							<div class="fw-bold">Ürün Sayısı</div>
							<div id="sumUrun" class="fs-2">-</div>
							<div class="text-muted small">Son Master: <span id="sumMaster">-</span></div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="card border">
						<div class="card-body">
							<div class="fw-bold">Depo Sayısı</div>
							<div id="sumDepo" class="fs-2">-</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="card border">
						<div class="card-body">
							<div class="fw-bold">İPT Ürün</div>
							<div id="sumIpt" class="fs-2">-</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="card border">
						<div class="card-body">
							<div class="fw-bold">BK Ürün</div>
							<div id="sumBk" class="fs-2">-</div>
							<div class="text-muted small">Son Stok Senk: <span id="sumLastSync">-</span></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="d-flex gap-3 align-items-center mt-4">
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" id="autoSync" />
					<label class="form-check-label" for="autoSync">Senkron Aktif/Pasif</label>
				</div>
				<input type="number" id="intervalSec" class="form-control" style="width:200px" placeholder="İstek Aralığı (sn)" />
				<div id="sumInfo" class="text-muted"></div>
				<span id="svcBadge" class="badge bg-secondary">Servis: -</span>
			</div>
			<div class="row g-3 mt-3">
				<div class="col-md-6">
					<div class="card border h-100">
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<h5 class="mb-0">Kuyruk Durumu</h5>
								<button class="btn btn-sm btn-light" id="btnQueueReload">Yenile</button>
							</div>
							<div class="d-flex gap-3">
								<div>Beklemede: <span id="qPending">-</span></div>
								<div>Rezerve: <span id="qReserved">-</span></div>
								<div>Başarısız (24s): <span id="qFailed">-</span></div>
								<div>Vadesi Gelen İşler: <span id="qDue">-</span></div>
							</div>

							<div class="mt-3 small text-muted">Son Çalıştırmalar</div>
							<pre id="qRuns" class="bg-light p-2" style="max-height:220px; overflow:auto"></pre>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card border h-100">
						<div class="card-body">
							<h5>Son İşlemler</h5>
							<ul id="sumLogs" class="mb-0"></ul>
						</div>
					</div>
				</div>
			</div>
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
let dbgModal;
try{ dbgModal = new bootstrap.Modal(document.getElementById('dbgModal')); }
catch(_){
	// Bootstrap JS yoksa manuel fallback ile modal göster
	dbgModal = { show: () => {
		const m = document.getElementById('dbgModal');
		if (!m) return;
		m.style.display = 'block';
		m.classList.add('show');
		document.body.classList.add('modal-open');
		let backdrop = document.getElementById('dbgBackdrop');
		if (!backdrop){
			backdrop = document.createElement('div');
			backdrop.id = 'dbgBackdrop';
			backdrop.className = 'modal-backdrop fade show';
			document.body.appendChild(backdrop);
		}
	}};
}
const dbgPre = document.getElementById('dbgPre');
function showDebug(data){
	try{
		let txt;
		if (typeof data === 'string') { txt = data; }
		else { txt = JSON.stringify(data, null, 2) || String(data ?? ''); }
		if (!txt || txt.trim() === '') { txt = 'Boş yanıt'; }
		box.textContent = txt;
		dbgPre.textContent = txt;
		dbgModal.show();
	}catch(e){ const txt = String(data ?? ''); box.textContent = txt; dbgPre.textContent = txt; dbgModal.show(); }
}
// Basit toast fallback (eğer global yoksa)
if (typeof window.showToast !== 'function') {
	window.showToast = function(message, type){
		try {
			const colors = { success: '#198754', danger: '#dc3545', info: '#0d6efd', warning: '#ffc107' };
			const el = document.createElement('div');
			el.textContent = message || '';
			el.style.position = 'fixed'; el.style.right = '16px'; el.style.top = '16px'; el.style.zIndex = 1080;
			el.style.background = (colors[type]||'#0d6efd'); el.style.color = '#fff'; el.style.padding = '10px 12px';
			el.style.borderRadius = '6px'; el.style.boxShadow = '0 2px 10px rgba(0,0,0,.2)';
			document.body.appendChild(el);
			setTimeout(()=>{ el.remove(); }, 3000);
		} catch (e) { alert(message); }
	}
}
async function fetchJsonOrText(url, options){ const r = await fetch(url, options); const t = await r.text(); try{ return JSON.parse(t); }catch(_){ return t; } }
async function fetchJsonOrTextInfo(url, options){
	const r = await fetch(url, options);
	const status = r.status; const redirected = !!r.redirected; const finalUrl = r.url;
	const text = await r.text();
	let isJson = false; let data;
	try{ data = JSON.parse(text); isJson = true; }catch(_){ data = text; }
	return { data, isJson, status, redirected, url: finalUrl };
}
document.getElementById('btnDebug')?.addEventListener('click', ()=>{ const txt = box.textContent || 'Debug verisi yok'; dbgPre.textContent = txt; dbgModal.show(); });
async function loadSummary(){
	try{
		const r = await fetch('/admin/tamsoft-stok/summary');
		const d = await r.json();
		if(d && d.success){
			document.getElementById('sumUrun').textContent = d.urun_sayisi;
			document.getElementById('sumDepo').textContent = d.depo_sayisi;
			document.getElementById('sumIpt').textContent = d.ipt_urun_sayisi;
			document.getElementById('sumBk').textContent = d.bk_urun_sayisi;
			document.getElementById('autoSync').checked = !!d.sync_active;
			document.getElementById('intervalSec').value = d.request_interval_sec || '';
			document.getElementById('sumInfo').textContent = `Senkron: ${d.sync_active? 'Aktif':'Pasif'} · Sıklık: ${d.request_interval_sec || '-'} sn · Depo Bazlı: ${d.sync_by_depot? 'Evet':'Hayır'}`;
			document.getElementById('sumMaster').textContent = d.last_master_sync_at || '-';
			document.getElementById('sumLastSync').textContent = d.last_sync_at || '-';
			const ul = document.getElementById('sumLogs');
			ul.innerHTML = '';
			(d.latest_logs||[]).forEach(l=>{
				const li = document.createElement('li');
				li.textContent = `[${l.created_at}] ${l.type || ''} - ${l.message}`;
				ul.appendChild(li);
			});
			// servis durumu
			try{
				const r2 = await fetch('/admin/tamsoft-stok/depolar/preview');
				const d2 = await r2.json();
				const svc = document.getElementById('svcBadge');
				svc.textContent = `Servis: ${d2.health && d2.health.service ? d2.health.service : '-'}`;
				svc.className = 'badge ' + ((d2.health && d2.health.service==='online')?'bg-success':'bg-danger');
			}catch(e){}
		}
	}catch(e){/* yut */}
}
loadSummary();
// Kuyruk özetini yükle
async function loadQueue(){
    try{
        const r = await fetch('/admin/tamsoft-stok/queue/summary');
        const d = await r.json();
        if (d && d.success){
            document.getElementById('qPending').textContent = d.queue?.pending ?? '-';
            document.getElementById('qReserved').textContent = d.queue?.reserved ?? '-';
            document.getElementById('qFailed').textContent = d.queue?.failed ?? '-';
            document.getElementById('qDue').textContent = d.due_jobs ?? '-';
            document.getElementById('qRuns').textContent = JSON.stringify(d.last_runs||[], null, 2);
        }
    }catch(e){}
}
loadQueue();
document.getElementById('btnQueueReload')?.addEventListener('click', loadQueue);
// basit auto sync (UI tarafı)
let syncTimer = null;
const auto = document.getElementById('autoSync');
const inp = document.getElementById('intervalSec');
auto.addEventListener('change', ()=>{
	if (auto.checked) {
		const sec = parseInt(inp.value || '900', 10);
		if (syncTimer) clearInterval(syncTimer);
		syncTimer = setInterval(()=>document.getElementById('btnRefresh')?.click(), Math.max(15, sec) * 1000);
	} else {
		if (syncTimer) clearInterval(syncTimer);
		syncTimer = null;
	}
});
const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
document.getElementById('btnTokenTest')?.addEventListener('click', async ()=>{
	try{ const d = await fetchJsonOrText('/admin/tamsoft-stok/token-test'); showDebug(d); }catch(e){ showToast('Hata','danger'); }
});
document.getElementById('btnDepoSync')?.addEventListener('click', async ()=>{
	try{ const d = await fetchJsonOrText('/admin/tamsoft-stok/depolar/sync'); showDebug(d); }catch(e){ showToast('Hata','danger'); }
});
document.getElementById('btnDepoPreview')?.addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/preview');
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnStokPreview')?.addEventListener('click', async ()=>{
	try{ const d = await fetchJsonOrText('/admin/tamsoft-stok/stok/preview?limit=4'); showDebug(d); }catch(e){ showToast('Hata','danger'); }
});
// Manuel fiyat güncelle
document.getElementById('btnPriceRefreshManual')?.addEventListener('click', async ()=>{
    const btn = document.getElementById('btnPriceRefreshManual');
    btn.disabled = true; const old = btn.innerText; btn.innerText = 'Çalışıyor...';
    try{
        const fd = new FormData();
        const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
        fd.append('_csrf', CSRF);
        const res = await fetch('/admin/tamsoft-stok/price-refresh', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF } });
        const txt = await res.text(); let data=null; try{ data = JSON.parse(txt); }catch(_){ data = txt; }
        showDebug(data);
        if (typeof data==='object' && data && data.success){ showToast(`Fiyat güncellendi. updated=${data.updated||0}`,'success'); }
        else { showToast('Çalıştırıldı','info'); }
    }catch(e){ showToast('Hata', 'danger'); }
    finally{ btn.disabled=false; btn.innerText = old; }
});

// Depo listesi: önizleme ve çalıştırma
document.getElementById('btnDepoListPreview')?.addEventListener('click', async ()=>{
    try{ const res = await fetchJsonOrTextInfo('/admin/tamsoft-stok/depolar/preview'); if (res.redirected || res.status===302){ showToast('Oturum yönlendirildi. Lütfen yeniden giriş yapın.','warning'); } showDebug(res.data); }catch(e){ showToast('Hata','danger'); }
});
document.getElementById('btnDepoListRun')?.addEventListener('click', async ()=>{
	try{
		const fd = new FormData();
		const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
		fd.append('_csrf', CSRF);
		const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF } });
		const txt = await r.text(); let data=null; try{ data = JSON.parse(txt); }catch(_){ data = txt; }
		showDebug(data);
		const ok = (typeof data==='object' && data && data.success===true);
		showToast(ok?'Depo listesi güncellendi':'Çalıştırıldı','info');
	}catch(e){ showToast('Hata','danger'); }
});

// Master stok: önizleme ve çalıştırma
document.getElementById('btnMasterPreview')?.addEventListener('click', async ()=>{
    try{ const res = await fetchJsonOrTextInfo('/admin/tamsoft-stok/stok/preview?limit=4'); if (res.redirected || res.status===302){ showToast('Oturum yönlendirildi. Lütfen yeniden giriş yapın.','warning'); } showDebug(res.data); }catch(e){ showToast('Hata','danger'); }
});
document.getElementById('btnMasterRun')?.addEventListener('click', async ()=>{
	try{
		const fd = new FormData();
		const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
		fd.append('_csrf', CSRF);
		const r = await fetch('/admin/tamsoft-stok/cron/monthly-master', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF } });
		const txt = await r.text(); let data=null; try{ data = JSON.parse(txt); }catch(_){ data = txt; }
		showDebug(data);
		const ok = (typeof data==='object' && data && data.success===true);
		showToast(ok?'Master çekildi':'Çalıştırıldı','info');
	}catch(e){ showToast('Hata','danger'); }
});

// Fiyat önizleme (4)
document.getElementById('btnPricePreview')?.addEventListener('click', async ()=>{
    try{ const res = await fetchJsonOrTextInfo('/admin/tamsoft-stok/stok/preview?limit=4'); if (res.redirected || res.status===302){ showToast('Oturum yönlendirildi. Lütfen yeniden giriş yapın.','warning'); } showDebug(res.data); }catch(e){ showToast('Hata','danger'); }
});

// E-ticaret stok: önizleme ve aktif depoları kuyrukla
document.getElementById('btnEcomPreview')?.addEventListener('click', async ()=>{
    try{ const res = await fetchJsonOrTextInfo('/admin/tamsoft-stok/ecommerce/preview'); if (res.redirected || res.status===302){ showToast('Oturum yönlendirildi. Lütfen yeniden giriş yapın.','warning'); } showDebug(res.data); }catch(e){ showToast('Hata','danger'); }
});
document.getElementById('btnEcomRun')?.addEventListener('click', async ()=>{
	try{
		const fd = new FormData();
		const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
		fd.append('_csrf', CSRF);
		const r = await fetch('/admin/tamsoft-stok/depolar/refresh-parallel', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF } });
		const txt = await r.text(); let data=null; try{ data = JSON.parse(txt); }catch(_){ data = txt; }
		showDebug(data);
		const ok = (typeof data==='object' && data && data.success===true);
		showToast(ok?'E-ticaret stok işleri kuyruklandı':'Çalıştırıldı','info');
	}catch(e){ showToast('Hata','danger'); }
});
</script>


