<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Trendyol Go - Ürün Listesi</h3>
			<div class="d-flex gap-2">
				<a href="/admin/trendyolgo" class="btn btn-sm btn-light">Anasayfa</a>
				<a href="/admin/trendyolgo/magazalar" class="btn btn-sm btn-light">Mağaza Listesi</a>
				<a href="/admin/trendyolgo/ayarlar" class="btn btn-sm btn-light">Ayarlar</a>
				<a href="/admin/trendyolgo/stoklar<?= ($store_id? ('?store_id=' . urlencode($store_id)) : '') ?>" class="btn btn-sm btn-primary">Şube Stokları</a>
			</div>
		</div>
		<div class="card-body">
			<div class="alert alert-secondary mb-3">
				<div class="text-dark"><b>Mağaza:</b> <?= htmlspecialchars(($store_name ?? '') !== '' ? ($store_name . ' (#' . ($store_id ?? '-') . ')') : ($store_id ?? '-')) ?></div>
				<div class="text-dark"><b>Toplam ürün:</b> <?= (int)($total ?? count($items)) ?></div>
			</div>
			<div id="noDataAlert" class="alert alert-info" style="display:none">Henüz veri yok.</div>
			<div class="table-responsive">
				<table class="table table-row-dashed align-middle" id="urunlerTable">
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
	const storeId = '<?= htmlspecialchars($store_id ?? '') ?>';
	document.addEventListener('DOMContentLoaded', function(){
		if (window.jQuery && jQuery.fn && jQuery.fn.DataTable){
			jQuery('#urunlerTable').DataTable({
				serverSide: true,
				processing: true,
				ajax: {
					url: '/admin/trendyolgo/urunler/data',
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
					{ data: 'description', render: (d)=> `<div style=\"max-width:380px;white-space:normal;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;\">${d||'-'}</div>` }
				],
				pageLength: 50,
				lengthMenu: [ [25,50,100,200], [25,50,100,200] ],
				order: [[1,'asc']],
				initComplete: function(settings, json){ if (!json || !json.data || !json.data.length) { document.getElementById('noDataAlert').style.display=''; } }
			});
		}
	});
})();
</script>

