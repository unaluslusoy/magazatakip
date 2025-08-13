<?php
$title = "Bildirim Detayı";
$link = "Bildirim Detayı";

require_once __DIR__ . '/../layouts/layout/header.php';
require_once __DIR__ . '/../layouts/layout/navbar.php';
?>

<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
    <!--begin::Content container-->
    <div class="container-fluid" id="kt_app_content_container">
        
        <!--begin::Header-->
        <div class="d-flex align-items-center justify-content-between mb-6">
            <div class="d-flex align-items-center">
                <a href="/kullanici/bildirimler" class="btn btn-icon btn-light me-3" title="Geri">
                    <i class="ki-outline ki-arrow-left fs-2"></i>
                </a>
                <div>
                    <h1 class="fs-2hx fw-bold text-gray-900 mb-1">Bildirim Detayı</h1>
                    <p class="text-muted fs-6 mb-0">Bildirim bilgilerini görüntüleyin</p>
                </div>
            </div>
            <div class="btn-group">
                <?php if (!$bildirim['okundu']): ?>
                    <button class="btn btn-warning btn-sm mark-as-read-btn" data-id="<?= $bildirim['id'] ?>">
                        <i class="ki-outline ki-check fs-2 me-1"></i>Okundu
                    </button>
                <?php endif; ?>
                <?php if (!empty($bildirim['url'])): ?>
                    <a href="<?= htmlspecialchars($bildirim['url']) ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="ki-outline ki-arrow-right fs-2 me-1"></i>Aç
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Bildirim Kartı-->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-6">
                
                <!--begin::Header Section-->
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div class="d-flex align-items-center flex-grow-1">
                        <!--begin::Avatar-->
                        <div class="symbol symbol-50px me-4">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-notification-on text-primary fs-1"></i>
                            </div>
                        </div>
                        <!--end::Avatar-->
                        
                        <!--begin::Content-->
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h2 class="fs-1 fw-bold text-gray-900 mb-0">
                                    <?= htmlspecialchars($bildirim['baslik']) ?>
                                </h2>
                                <?php if (!$bildirim['okundu']): ?>
                                    <span class="badge badge-light-warning ms-3">Yeni</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex align-items-center text-muted fs-6">
                                <i class="ki-outline ki-user fs-5 me-2"></i>
                                <span class="me-4">
                                    <?= htmlspecialchars($bildirim['gonderici_adi'] ?? 'Sistem') ?> <?= htmlspecialchars($bildirim['gonderici_soyadi'] ?? '') ?>
                                </span>
                                
                                <i class="ki-outline ki-calendar fs-5 me-2"></i>
                                <span>
                                    <?= date('d.m.Y H:i:s', strtotime($bildirim['gonderim_tarihi'])) ?>
                                </span>
                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                </div>
                <!--end::Header Section-->
                
                <!--begin::Message Section-->
                <div class="mb-6">
                    <div class="bg-light-primary bg-opacity-50 rounded p-4">
                        <h4 class="fs-5 fw-semibold text-gray-900 mb-3">Mesaj</h4>
                        <p class="text-gray-700 fs-6 mb-0 line-height-relaxed">
                            <?= nl2br(htmlspecialchars($bildirim['mesaj'])) ?>
                        </p>
                    </div>
                </div>
                <!--end::Message Section-->
                
                <!--begin::Details Grid-->
                <div class="row g-4">
                    <!--begin::Status-->
                    <div class="col-12 col-md-6">
                        <div class="card bg-light-info border-0 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-info">
                                            <i class="ki-outline ki-check text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Durum</div>
                                        <div class="fs-5 fw-bold text-gray-900">
                                            <?php if ($bildirim['okundu']): ?>
                                                <span class="badge badge-light-success">Okundu</span>
                                                <?php if (!empty($bildirim['okunma_tarihi'])): ?>
                                                    <br><small class="text-muted">(<?= date('d.m.Y H:i:s', strtotime($bildirim['okunma_tarihi'])) ?>)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-light-warning">Yeni</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Status-->
                    
                    <!--begin::Channel-->
                    <div class="col-12 col-md-6">
                        <div class="card bg-light-primary border-0 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-primary">
                                            <i class="ki-outline ki-arrow-right text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Gönderim Kanalı</div>
                                        <div class="fs-5 fw-bold text-gray-900">
                                            <?= ucfirst($bildirim['gonderim_kanali']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Channel-->
                    
                    <!--begin::URL-->
                    <?php if (!empty($bildirim['url'])): ?>
                    <div class="col-12 col-md-6">
                        <div class="card bg-light-success border-0 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-success">
                                            <i class="ki-outline ki-link text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Yönlendirme URL</div>
                                        <div class="fs-6 fw-bold text-gray-900 text-break">
                                            <a href="<?= htmlspecialchars($bildirim['url']) ?>" target="_blank" class="text-decoration-none">
                                                <?= htmlspecialchars($bildirim['url']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!--end::URL-->
                    
                    <!--begin::Tags-->
                    <?php if (!empty($bildirim['etiketler'])): ?>
                    <div class="col-12 col-md-6">
                        <div class="card bg-light-warning border-0 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-warning">
                                            <i class="ki-outline ki-tag text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Etiketler</div>
                                        <div class="fs-6 fw-bold text-gray-900">
                                            <?= htmlspecialchars($bildirim['etiketler']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!--end::Tags-->
                </div>
                <!--end::Details Grid-->
                
                <!--begin::Actions-->
                <div class="d-flex justify-content-between align-items-center mt-6 pt-4 border-top">
                    <div class="text-muted fs-7"><i class="ki-outline ki-information fs-5 me-1"></i>Bildirim ID: #<?= $bildirim['id'] ?></div>
                    <div class="d-flex gap-2">
                        <a href="/kullanici/bildirimler" class="btn btn-light-primary">
                            <i class="ki-outline ki-arrow-left fs-2 me-1"></i>Geri Dön
                        </a>
                    </div>
                </div>
                <!--end::Actions-->
            </div>
        </div>
        <!--end::Bildirim Kartı-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?> 
<script>
// Detay sayfası açıldığında okunmadıysa otomatik okundu işareti
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const markBtn = document.querySelector('.mark-as-read-btn');
        if (markBtn) {
            const id = markBtn.getAttribute('data-id');
            // Arka planda API çağrısı
            try {
                await (window.bildirimApiService && window.bildirimApiService.markAsRead ? window.bildirimApiService.markAsRead(id) : Promise.resolve());
                // Butonu gizle
                markBtn.style.display = 'none';
            } catch (e) { /* sessizce yut */ }
        }
    } catch (e) {}
});
</script>