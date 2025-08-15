<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Tamsoft ERP - Dashboard</h3>
		</div>
		<div class="card-body">
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
			<div class="mt-4">
				<h5>Son İşlemler</h5>
				<ul id="sumLogs" class="mb-0"></ul>
			</div>
			<pre id="respBox" class="mt-3 d-none"></pre>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
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
// basit auto sync (UI tarafı)
let syncTimer = null;
const auto = document.getElementById('autoSync');
const inp = document.getElementById('intervalSec');
auto.addEventListener('change', ()=>{
	if (auto.checked) {
		const sec = parseInt(inp.value || '900', 10);
		if (syncTimer) clearInterval(syncTimer);
		syncTimer = setInterval(()=>document.getElementById('btnRefresh').click(), Math.max(15, sec) * 1000);
	} else {
		if (syncTimer) clearInterval(syncTimer);
		syncTimer = null;
	}
});
document.getElementById('btnTokenTest').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/token-test', { method:'POST' });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnDepoSync').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/sync', { method:'POST' });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnDepoPreview').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/depolar/preview');
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
document.getElementById('btnStokPreview').addEventListener('click', async ()=>{
	const r = await fetch('/admin/tamsoft-stok/stok/preview', { method:'POST' });
	const d = await r.json();
	document.getElementById('respBox').textContent = JSON.stringify(d, null, 2);
});
</script>


