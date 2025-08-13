<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Trendyol Go</h3>
            <div class="d-flex gap-2">
                <a href="/admin/trendyolgo" class="btn btn-sm btn-light">Anasayfa</a>
                <a href="/admin/trendyolgo/urunler" class="btn btn-sm btn-light">Ürün Listesi</a>
                <a href="/admin/trendyolgo/magazalar" class="btn btn-sm btn-light">Mağaza Listesi</a>
                <a href="/admin/trendyolgo/ayarlar" class="btn btn-sm btn-primary">Ayarlar</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="fw-bold mb-2">Servis Durumu</div>
                            <div><b>Aktif:</b> <?= !empty($health['enabled']) ? 'Evet' : 'Hayır' ?></div>
                            <div><b>Çalışıyor:</b> <?= !empty($health['service_ok']) ? 'Evet' : 'Hayır' ?></div>
                            <div><b>Base URL:</b> <?= htmlspecialchars($health['base_url'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-3">
                    <a class="card bg-light-primary hoverable d-block text-decoration-none" href="/admin/trendyolgo/urunler">
                                <div class="card-body">
                                    <div class="fw-bold">Ürünler</div>
                                    <div class="text-muted fs-7">Trendyol ürünlerini listele</div>
                                </div>
                            </a>
                        </div>
                <div class="col-md-3">
                    <a class="card bg-light-success hoverable d-block text-decoration-none" href="/admin/trendyolgo/stoklar">
                        <div class="card-body">
                            <div class="fw-bold">Şube Stokları</div>
                            <div class="text-muted fs-7">Şubeye göre stok ve fiyatlar</div>
                        </div>
                    </a>
                </div>
                        <div class="col-md-3">
                            <a class="card bg-light-info hoverable d-block text-decoration-none" href="/admin/trendyolgo/magazalar">
                                <div class="card-body">
                                    <div class="fw-bold">Mağazalar</div>
                                    <div class="text-muted fs-7">Store ID eşleştirmeleri</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a class="card bg-light-warning hoverable d-block text-decoration-none" href="#" onclick="alert('Sipariş ekranı yakında');return false;">
                                <div class="card-body">
                                    <div class="fw-bold">Siparişler</div>
                                    <div class="text-muted fs-7">Yeni/aktif siparişler</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a class="card bg-light-danger hoverable d-block text-decoration-none" href="#" onclick="alert('İptal ekranı yakında');return false;">
                                <div class="card-body">
                                    <div class="fw-bold">İptal Edilenler</div>
                                    <div class="text-muted fs-7">İptal/iade işlemleri</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

