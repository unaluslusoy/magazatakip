<?php
$title = "Bildirim Detayı";
$link = "Bildirim Detayı";

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
    <!--begin::Content container-->
    <div class="container-fluid" id="kt_app_content_container">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>Bildirim Detayı</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <a href="/admin/bildirimler" class="btn btn-light me-3">
                            <i class="ki-outline ki-arrow-left fs-2"></i>Geri
                        </a>
                        <a href="/admin/bildirimler/sil/<?= $bildirim['id'] ?>" class="btn btn-danger" onclick="return confirm('Bu bildirimi silmek istediğinizden emin misiniz?')">
                            <i class="ki-outline ki-trash fs-2"></i>Sil
                        </a>
                    </div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Bildirim Başlığı:</label>
                            <div class="form-control form-control-solid">
                                <?= htmlspecialchars($bildirim['baslik']) ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Durum:</label>
                            <div>
                                <?php
                                $durumClass = 'light-primary';
                                $durumText = 'Beklemede';
                                switch ($bildirim['durum']) {
                                    case 'gonderildi':
                                        $durumClass = 'light-success';
                                        $durumText = 'Gönderildi';
                                        break;
                                    case 'basarisiz':
                                        $durumClass = 'light-danger';
                                        $durumText = 'Başarısız';
                                        break;
                                }
                                ?>
                                <span class="badge badge-<?= $durumClass ?> fs-7"><?= $durumText ?></span>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-12">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Bildirim Mesajı:</label>
                            <div class="form-control form-control-solid" style="min-height: 100px;">
                                <?= nl2br(htmlspecialchars($bildirim['mesaj'])) ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Gönderim Kanalı:</label>
                            <div class="form-control form-control-solid">
                                <?= ucfirst($bildirim['gonderim_kanali']) ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Alıcı Tipi:</label>
                            <div class="form-control form-control-solid">
                                <?= ucfirst($bildirim['alici_tipi']) ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Öncelik:</label>
                            <div class="form-control form-control-solid">
                                <?= ucfirst($bildirim['oncelik']) ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Gönderim Tarihi:</label>
                            <div class="form-control form-control-solid">
                                <?= date('d.m.Y H:i:s', strtotime($bildirim['gonderim_tarihi'])) ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Yönlendirme URL:</label>
                            <div class="form-control form-control-solid">
                                <?= !empty($bildirim['url']) ? htmlspecialchars($bildirim['url']) : '<span class="text-muted">Belirtilmemiş</span>' ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">İkon URL:</label>
                            <div class="form-control form-control-solid">
                                <?= !empty($bildirim['icon']) ? htmlspecialchars($bildirim['icon']) : '<span class="text-muted">Belirtilmemiş</span>' ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Gönderen:</label>
                            <div class="form-control form-control-solid">
                                <?= !empty($bildirim['gonderici_adi']) ? htmlspecialchars($bildirim['gonderici_adi'] . ' ' . $bildirim['gonderici_soyadi']) : 'Sistem' ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-6">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Okundu:</label>
                            <div class="form-control form-control-solid">
                                <?= $bildirim['okundu'] ? 'Evet' : 'Hayır' ?>
                                <?php if ($bildirim['okundu'] && !empty($bildirim['okunma_tarihi'])): ?>
                                    <br><small class="text-muted">(<?= date('d.m.Y H:i:s', strtotime($bildirim['okunma_tarihi'])) ?>)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-12">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Etiketler:</label>
                            <div class="form-control form-control-solid">
                                <?= !empty($bildirim['etiketler']) ? htmlspecialchars($bildirim['etiketler']) : '<span class="text-muted">Belirtilmemiş</span>' ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-9">
                    <!--begin::Col-->
                    <div class="col-12">
                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Ekstra Veriler:</label>
                            <div class="form-control form-control-solid">
                                <?php if (!empty($bildirim['ekstra_veri'])): ?>
                                    <pre class="mb-0" style="font-size: 12px;"><?= htmlspecialchars($bildirim['ekstra_veri']) ?></pre>
                                <?php else: ?>
                                    <span class="text-muted">Belirtilmemiş</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<?php require_once 'app/Views/layouts/footer.php'; ?> 