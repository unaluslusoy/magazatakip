<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/_top_nav.php'; ?>
<style>
/* Tablo iç boşluk ve hizalama */
#tb th, #tb td { padding: .6rem .9rem; }
#tb thead { position: sticky; top: 0; z-index: 6; }
#tb thead th { vertical-align: middle; text-align: center; position: sticky; top: 0; z-index: 6; }
#tb tbody td { vertical-align: middle; }
/* Ürün adı genişliği */
#tb td.col-name { width: 32%; }
/* DataTables üst sol (length) tek satır ve genişlik */
.dataTables_wrapper .dataTables_length { white-space: nowrap !important; min-width: 400px; }
.dataTables_wrapper .dataTables_length label { display: inline-flex !important; align-items: center; gap: .5rem; margin-bottom: 0 !important; flex-wrap: nowrap; }
.dataTables_wrapper .dataTables_length select { display: inline-block !important; width: auto !important; margin: 0 .25rem !important; }
/* İç scroll: başlık sabit kalsın */
.table-responsive { max-height: calc(100vh - 280px); overflow: auto; }
</style>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Stok Envanter</h3>
			<div class="d-flex gap-2">
				<button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#fltBox">Filtre</button>
				<button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#colModal">Sütunlar</button>
				<button type="button" id="btnExport" class="btn btn-sm btn-primary">Dışa Aktar (CSV)</button>
			</div>
		</div>
		<div class="card-body">
			<div class="collapse mb-3" id="fltBox">
				<div class="border rounded p-3">
					<div class="row g-2 align-items-end">
						<div class="col-md-4">
							<label class="form-label">Arama</label>
							<input type="text" id="fltSearch" class="form-control" placeholder="Kod / Ürün adı" />
						</div>
						<div class="col-md-3">
							<label class="form-label">Depo</label>
							<select id="fltDepo" class="form-select"><option value="">Tümü</option></select>
						</div>
						<div class="col-md-2">
							<div class="form-check mt-4">
								<input type="checkbox" class="form-check-input" id="fltOnlyPos" />
								<label for="fltOnlyPos" class="form-check-label">Sadece stoklu</label>
							</div>
						</div>
						<div class="col-md-3">
							<label class="form-label">Entegrasyon</label>
							<select id="fltHasInt" class="form-select">
								<option value="">Tümü</option>
								<option value="1">Var</option>
								<option value="0">Yok</option>
							</select>
						</div>
						<div class="col-12">
							<button type="button" id="btnApplyFilters" class="btn btn-primary">Uygula</button>
						</div>
					</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-striped align-middle" id="tb">
					<thead class="table-dark">
						<tr>
							<th>Kod</th>
							<th>Barkod</th>
							<th>Ürün Adı</th>
							<th>Birim</th>
							<th>KDV</th>
							<th>Fiyat</th>
							<th>Miktar</th>
							<th>Entegrasyon</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- Kolon Modal -->
<div class="modal fade" id="colModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Kolon Görünürlüğü</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="colToggleBody"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Kapat</button>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
// Export CSV (UTF-8 BOM ile)
document.addEventListener('click', async (e)=>{
	if(e.target && e.target.id==='btnExport'){
		const url = $('#tb').DataTable().ajax.url();
		const r = await fetch(url);
		const d = await r.json();
		const rows = (d.data||d.rows||[]);
		let csv = '\ufeff' + 'Kod;Barkod;Ürün Adı;Birim;KDV;Fiyat;Miktar\n';
		rows.forEach(row=>{
			csv += [row.ext_urun_id,row.barkod,row.urun_adi,row.birim,row.kdv,row.fiyat,row.miktari].map(v=>`"${(v??'').toString().replaceAll('"','""')}"`).join(';')+"\n";
		});
		const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
		const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'envanter.csv'; a.click();
	}
});
</script>
<script>
(function(){
	const tableEl = document.getElementById('tb');
	const headRow = tableEl.querySelector('thead tr');
	const togglesEl = document.getElementById('colToggleBody');
	const selDepo = document.getElementById('fltDepo');
	let dt;
	function fmtInt(v){ if(v===undefined||v===null||v==='') return ''; const n=parseFloat(v); if (isNaN(n)) return ''; return String(Math.round(n)); }
	function loadVisPrefs(){ try{ return JSON.parse(localStorage.getItem('tamsoft_envanter_cols')||'{}'); }catch(e){ return {}; } }
	function saveVisPrefs(p){ localStorage.setItem('tamsoft_envanter_cols', JSON.stringify(p)); }
	(async function init(){
		const params = new URLSearchParams(location.search);
		params.set('start', 0);
		params.set('length', 1);
		const r = await fetch('/admin/tamsoft-stok/envanter/data?'+params.toString());
		const meta = await r.json();
		const depots = meta.depots || [];
		depots.forEach(dp=>{ const opt=document.createElement('option'); opt.value=dp.id; opt.textContent = dp.depo_adi||('Depo '+dp.id); selDepo.appendChild(opt); });
		depots.forEach(dp=>{
			const th = document.createElement('th');
			th.textContent = dp.depo_adi ? dp.depo_adi : ('Depo '+dp.id);
			headRow.appendChild(th);
		});
		const columns = [
			{ data: 'ext_urun_id', title: 'Kod', className:'text-center align-middle' },
			{ data: 'barkod', title: 'Barkod', className:'text-center align-middle' },
			{ data: 'urun_adi', title: 'Ürün Adı', className:'col-name text-start align-middle' },
			{ data: 'birim', title: 'Birim', className:'text-center align-middle' },
			{ data: 'kdv', title: 'KDV', className:'text-center align-middle' },
			{ data: 'fiyat', title: 'Fiyat', className:'text-end align-middle', render:(d)=>fmtInt(d) },
			{ data: 'miktari', title: 'Miktar', className:'text-end align-middle', render:(d)=>fmtInt(d) },
			{ data: null, title: 'Entegrasyon', className:'text-center align-middle', orderable:false, searchable:false, render: (data,type,row)=>{
				const logos = [];
				if (row.trendyolgo_sku) logos.push('<img alt="TY" src="/public/media/logos/tgo-logo.png" height="18"/>');
				if (row.getir_code) logos.push('<img alt="Getir" src="/public/media/logos/getircarsi-logo.png" height="18"/>');
				if (row.yemeksepeti_code) logos.push('<img alt="YS" src="/public/media/logos/yemeksepeti-logo.png" height="18"/>');
				return logos.join(' ');
			}}
		];
		depots.forEach(dp=>{
			columns.push({ data: 'dp_'+dp.id, title: (dp.depo_adi||('Depo '+dp.id)), className:'text-end align-middle', orderable:true, render:(d)=>fmtInt(d) });
		});
		dt = $(tableEl).DataTable({
			processing: true,
			serverSide: true,
			searching: false,
			ordering: true,
			lengthChange: true,
			pageLength: 50,
			lengthMenu: [[50,100,250,500,1000],[50,100,250,500,1000]],
			language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json', lengthMenu: '_MENU_ Kayıt gösteriyor' },
			dom: "<'row'<'col-md-6'l><'col-md-6 text-end'>>rt<'row'<'col-md-6'i><'col-md-6'p>>",
			ajax: {
				url: '/admin/tamsoft-stok/envanter/data'+(location.search||''),
				type: 'GET'
			},
			columns: columns,
			order: [[2,'asc']]
		});
		const prefs = loadVisPrefs();
		const colLabels = columns.map(c=>c.title||'');
		colLabels.forEach((label, idx)=>{
			const id = 'col_'+idx;
			const wrap = document.createElement('div');
			wrap.className = 'form-check form-check-inline mb-2';
			const cb = document.createElement('input');
			cb.type = 'checkbox';
			cb.className = 'form-check-input';
			cb.id = id; cb.checked = prefs[id] !== undefined ? !!prefs[id] : true;
			cb.addEventListener('change', ()=>{
				dt.column(idx).visible(cb.checked);
				const p = loadVisPrefs(); p[id] = cb.checked; saveVisPrefs(p);
			});
			const lab = document.createElement('label'); lab.className = 'form-check-label'; lab.setAttribute('for', id); lab.textContent = ' '+label;
			wrap.appendChild(cb); wrap.appendChild(lab);
			document.getElementById('colToggleBody').appendChild(wrap);
			dt.column(idx).visible(cb.checked);
		});
		document.getElementById('colModal').addEventListener('show.bs.modal', ()=>{
			colLabels.forEach((_, idx)=>{
				const id = 'col_'+idx; const cb = document.getElementById(id);
				if (cb) cb.checked = dt.column(idx).visible();
			});
		});
		document.getElementById('btnApplyFilters').addEventListener('click', ()=>{
			const qs = new URLSearchParams(location.search);
			qs.set('start','0');
			qs.set('length', String(dt.page.len()));
			const s = document.getElementById('fltSearch').value.trim();
			if (s) qs.set('search', s); else qs.delete('search');
			const d = document.getElementById('fltDepo').value;
			if (d) qs.set('depo_id', d); else qs.delete('depo_id');
			const op = document.getElementById('fltOnlyPos').checked ? '1' : '';
			if (op) qs.set('only_positive', op); else qs.delete('only_positive');
			const hi = document.getElementById('fltHasInt').value;
			if (hi!=="") qs.set('has_integration', hi); else qs.delete('has_integration');
			dt.ajax.url('/admin/tamsoft-stok/envanter/data?'+qs.toString()).load();
		});
	})();
})();
</script>


