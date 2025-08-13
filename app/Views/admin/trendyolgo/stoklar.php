<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Trendyol Go - Şube Stokları</h3>
			<div class="d-flex gap-2">
				<a href="/admin/trendyolgo" class="btn btn-sm btn-light">Anasayfa</a>
				<a href="/admin/trendyolgo/urunler" class="btn btn-sm btn-light">Ürün Listesi</a>
				<a href="/admin/trendyolgo/ayarlar" class="btn btn-sm btn-light">Ayarlar</a>
			</div>
		</div>
		<div class="card-body">
			<div class="row g-3 align-items-end mb-3">
				<div class="col-md-4">
					<label class="form-label">Şube</label>
					<select id="storeSelect" class="form-select">
						<option value="">Şube seçin</option>
						<?php foreach (($stores ?? []) as $s): $sid = (string)($s['store_id'] ?? ''); ?>
							<option value="<?= htmlspecialchars($sid) ?>" <?= ((string)($store_id ?? '') === $sid ? 'selected' : '') ?>><?= htmlspecialchars(($s['magaza_adi'] ?? '') . ' (#' . $sid . ')') ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-8">
					<div id="storeInfo" class="text-muted small"></div>
				</div>
			</div>
			<div id="noDataAlert" class="alert alert-info" style="display:none">Şube seçin ve listeleyin.</div>
			<div class="table-responsive">
				<table class="table table-row-dashed align-middle" id="stoklarTable">
					<thead>
						<tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
							<th>Görsel</th>
							<th>Ürün Adı</th>
							<th>Barkod</th>
							<th>Kategori</th>
							<th>Marka</th>
							<th>SKU</th>
							<th>Stok</th>
							<th>Trendyol Fiyatı</th>
							<th>Liste Fiyatı</th>
							<th>Durum</th>
							<th>Açıklama</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
(function(){
	const initialStoreId = '<?= htmlspecialchars($store_id ?? '') ?>';
	function initTable(storeId){
		if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
		const $table = jQuery('#stoklarTable');
		if ($table.hasClass('dataTable')) { $table.DataTable().destroy(); $table.find('tbody').empty(); }
		jQuery('#noDataAlert').toggle(!storeId);
		if (!storeId) return;
		$table.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				url: '/admin/trendyolgo/stoklar/data',
				type: 'GET',
				data: function(d){ d.store_id = storeId; }
			},
			columns: [
				{ data: 'imageUrl', render: (d)=> d ? '<img src="'+d+'" style="width:56px;height:56px;object-fit:cover;border-radius:6px;"/>' : '-' },
				{ data: 'name' },
				{ data: 'barcode' },
				{ data: null, render: (_d,_t,row)=> (row.categoryName? (row.categoryName + ' (#' + (row.categoryId||'-') + ')') : (row.categoryId||'-')) },
				{ data: 'brand' },
				{ data: 'sku', defaultContent: '-' },
				{ data: 'stock', defaultContent: '-' },
				{ data: 'trendyolPrice', render: (d)=> (typeof d==='number'? d.toLocaleString('tr-TR',{minimumFractionDigits:2}) : '-') },
				{ data: 'listPrice', render: (d)=> (typeof d==='number'? d.toLocaleString('tr-TR',{minimumFractionDigits:2}) : '-') },
				{ data: 'status' },
				{ data: 'description', render: (d)=> `<div style="max-width:380px;white-space:normal;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">${d||'-'}</div>` }
			],
			pageLength: 50,
			lengthMenu: [[25,50,100,200],[25,50,100,200]],
			order: [[1,'asc']]
		});
	}
	document.addEventListener('DOMContentLoaded', function(){
		const $sel = document.getElementById('storeSelect');
		$sel.addEventListener('change', function(){ initTable(this.value); });
		if (initialStoreId) { initTable(initialStoreId); }
	});
})();
</script>

