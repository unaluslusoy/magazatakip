<?php
$title = "Bildirimlerim";
$link = "Bildirimlerim";

require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>

<?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
    <!--begin::Content container-->
    <div class="container-fluid" id="kt_app_content_container">
        
        <!--begin::Stats Cards-->
        <div class="row g-4 mb-6">
            <div class="col-6 col-md-3">
                <div class="card bg-light-primary border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-primary">
                                    <i class="ki-outline ki-notification-on text-white fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fs-6 text-gray-600">Toplam</div>
                                <div class="fs-4 fw-bold text-gray-900"><?= $bildirimSayilari['toplam'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card bg-light-warning border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-warning">
                                    <i class="ki-outline ki-notification-off text-white fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fs-6 text-gray-600">Yeni</div>
                                <div class="fs-4 fw-bold text-gray-900"><?= $bildirimSayilari['okunmamis'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card bg-light-success border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-success">
                                    <i class="ki-outline ki-check text-white fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fs-6 text-gray-600">Okundu</div>
                                <div class="fs-4 fw-bold text-gray-900"><?= $bildirimSayilari['okundu'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card bg-light-info border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-info">
                                    <i class="ki-outline ki-calendar text-white fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fs-6 text-gray-600">Bugün</div>
                                <div class="fs-4 fw-bold text-gray-900"><?= $bildirimSayilari['bugun'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Stats Cards-->

        <!--begin::Filters-->
        <div class="card mb-6">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Durum Filtresi</label>
                        <select class="form-select form-select-solid" id="durumFilter">
                            <option value="all" <?= $durumFilter === 'all' ? 'selected' : '' ?>>Tümü</option>
                            <option value="okunmamis" <?= $durumFilter === 'okunmamis' ? 'selected' : '' ?>>Yeni Bildirimler</option>
                            <option value="okundu" <?= $durumFilter === 'okundu' ? 'selected' : '' ?>>Okunmuş</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold">Arama</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ki-outline ki-magnifier fs-3"></i>
                            </span>
                            <input type="text" class="form-control form-control-solid" id="searchTerm" placeholder="Başlık veya mesaj ara..." value="<?= htmlspecialchars($searchTerm) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Filters-->

        <!--begin::Bildirimler Listesi-->
        <div class="row g-4" id="bildirimler-container">
            <?php if (!empty($bildirimler)): ?>
                <?php foreach ($bildirimler as $bildirim): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100 <?= !$bildirim['okundu'] ? 'border-start border-warning border-4' : '' ?>">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-light-primary">
                                                <i class="ki-outline ki-notification-on text-primary fs-2"></i>
                                            </div>
                                        </div>
                                        <!--end::Avatar-->
                                        
                                        <!--begin::Content-->
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center mb-1">
                                                <h5 class="card-title mb-0 text-gray-900 fw-bold fs-6">
                                                    <?= htmlspecialchars($bildirim['baslik']) ?>
                                                </h5>
                                                <?php if (!$bildirim['okundu']): ?>
                                                    <span class="badge badge-light-warning ms-2">Yeni</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="text-muted fs-7 mb-2">
                                                <i class="ki-outline ki-user fs-5 me-1"></i>
                                                <?= htmlspecialchars($bildirim['gonderici_adi'] ?? 'Sistem') ?> <?= htmlspecialchars($bildirim['gonderici_soyadi'] ?? '') ?>
                                            </div>
                                            
                                            <div class="text-muted fs-7">
                                                <i class="ki-outline ki-calendar fs-5 me-1"></i>
                                                <?= date('d.m.Y H:i', strtotime($bildirim['gonderim_tarihi'])) ?>
                                            </div>
                                        </div>
                                        <!--end::Content-->
                                    </div>
                                    
                                    <!--begin::Actions-->
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-sm btn-light btn-active-light-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-outline ki-gear fs-2"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="/kullanici/bildirimler/detay/<?= $bildirim['id'] ?>">
                                                    <i class="ki-outline ki-eye fs-2 me-2"></i>Görüntüle
                                                </a>
                                            </li>
                                            <?php if (!$bildirim['okundu']): ?>
                                                <li>
                                                    <a class="dropdown-item mark-as-read" href="#" data-id="<?= $bildirim['id'] ?>">
                                                        <i class="ki-outline ki-check fs-2 me-2"></i>Okundu İşaretle
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <!--end::Actions-->
                                </div>
                                
                                <!--begin::Message-->
                                <div class="mb-3">
                                    <p class="text-gray-700 fs-6 mb-0">
                                        <?= htmlspecialchars(substr($bildirim['mesaj'], 0, 150)) ?>
                                        <?php if (strlen($bildirim['mesaj']) > 150): ?>
                                            <span class="text-muted">...</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <!--end::Message-->
                                
                                <!--begin::Footer-->
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-light-primary me-2">
                                            <i class="ki-outline ki-arrow-right fs-5 me-1"></i>
                                            <?= ucfirst($bildirim['gonderim_kanali']) ?>
                                        </span>
                                        <?php if (!empty($bildirim['url'])): ?>
                                            <span class="badge badge-light-info">
                                                <i class="ki-outline ki-link fs-5 me-1"></i>
                                                Link
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <a href="/kullanici/bildirimler/detay/<?= $bildirim['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="ki-outline ki-eye fs-2 me-1"></i>Detay
                                    </a>
                                </div>
                                <!--end::Footer-->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-8 text-center">
                            <div class="symbol symbol-100px mb-4">
                                <div class="symbol-label bg-light-muted">
                                    <i class="ki-outline ki-notification-off fs-1 text-muted"></i>
                                </div>
                            </div>
                            <h3 class="text-muted mb-2">Henüz bildiriminiz bulunmuyor</h3>
                            <p class="text-muted fs-6">Yeni bildirimler geldiğinde burada görünecek.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!--end::Bildirimler Listesi-->

        <!--begin::Load More Button-->
        <?php if (count($bildirimler) >= 10): ?>
            <div class="text-center mt-6">
                <button class="btn btn-light-primary btn-lg" id="loadMoreBtn">
                    <i class="ki-outline ki-arrow-down fs-2 me-2"></i>Daha Fazla Yükle
                </button>
            </div>
        <?php endif; ?>
        <!--end::Load More Button-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<?php require_once 'app/Views/kullanici/layout/footer.php'; ?> 