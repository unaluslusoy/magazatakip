<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Tamsoft ERP - Dashboard</h3>
			<div class="d-flex gap-2">
				<a href="/admin/tamsoft-stok/jobs" class="btn btn-sm btn-light">Job Manager</a>
			</div>
		</div>
		<div class="card-body">
			<div class="d-flex gap-2 mb-3">
				<button class="btn btn-sm btn-outline-success" id="btnPriceRefreshManual">Fiyatları Güncelle (manuel)</button>
				<button class="btn btn-sm btn-outline-secondary" id="btnDebug">Debug</button>
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
			<div class="d-flex gap-2 mt-3">
				<a class="btn btn-sm btn-light" href="/admin/tamsoft-stok/envanter?filter=IPT">IPT Ürün Listesi</a>
				<a class="btn btn-sm btn-light" href="/admin/tamsoft-stok/envanter?filter=BK">BK Ürün Listesi</a>
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
								<div>Pending: <span id="qPending">-</span></div>
								<div>Reserved: <span id="qReserved">-</span></div>
								<div>Failed(24h): <span id="qFailed">-</span></div>
								<div>Due Jobs: <span id="qDue">-</span></div>
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
const dbgModal = new bootstrap.Modal(document.getElementById('dbgModal'));
const dbgPre = document.getElementById('dbgPre');
function showDebug(data){ try{ const txt = (typeof data==='string')?data:JSON.stringify(data,null,2); box.textContent = txt; dbgPre.textContent = txt; dbgModal.show(); }catch(e){ box.textContent = String(data); } }
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
	const r = await fetch('/admin/tamsoft-stok/token-test', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnDepoSync')?.addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnDepoPreview')?.addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/preview');
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnStokPreview')?.addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/stok/preview', { method:'POST', headers: { 'X-CSRF-Token': CSRF } });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
// Manuel fiyat güncelle
document.getElementById('btnPriceRefreshManual')?.addEventListener('click', async ()=>{
    const btn = document.getElementById('btnPriceRefreshManual');
    btn.disabled = true; const old = btn.innerText; btn.innerText = 'Çalışıyor...';
    try{
        const fd = new FormData(); fd.append('_csrf', CSRF);
        const r = await fetch('/admin/tamsoft-stok/price-refresh', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } });
        const d = await r.json();
        showToast(d.success ? (`Fiyat güncellendi. updated=${d.updated||0}`) : ('Hata: '+(d.error||'')), d.success?'success':'danger');
    }catch(e){ showToast('Hata', 'danger'); }
    finally{ btn.disabled=false; btn.innerText = old; }
});
</script>


