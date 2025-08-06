<?php
$title = "OneSignal Test";
$link = "OneSignal Test";

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
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
        <!--begin::Row-->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <!--begin::Card-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">OneSignal Ayarları</span>
                            </div>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-column flex-grow-1">
                            <div class="d-flex flex-wrap">
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="fs-6 fw-bold text-gray-700">App ID</div>
                                    <div class="fw-semibold text-gray-500">
                                        <?= !empty($ayarlar['onesignal_app_id']) ? substr($ayarlar['onesignal_app_id'], 0, 20) . '...' : 'Ayarlanmamış' ?>
                                    </div>
                                </div>
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="fs-6 fw-bold text-gray-700">API Key</div>
                                    <div class="fw-semibold text-gray-500">
                                        <?= !empty($ayarlar['onesignal_api_key']) ? 'Ayarlandı' : 'Ayarlanmamış' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <!--begin::Card-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">Kullanıcı Durumu</span>
                            </div>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-column flex-grow-1">
                            <div class="d-flex flex-wrap">
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="fs-6 fw-bold text-gray-700">Toplam Kullanıcı</div>
                                    <div class="fw-semibold text-gray-500"><?= count($kullanicilar) ?></div>
                                </div>
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="fs-6 fw-bold text-gray-700">Token'lı Kullanıcı</div>
                                    <div class="fw-semibold text-gray-500">
                                        <?= count(array_filter($kullanicilar, function($k) { return !empty($k['cihaz_token']); })) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->

        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>Test Bildirimi Gönder</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Form-->
                <form action="/admin/onesignal-test/gonder" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="kullanici_id">Test Kullanıcısı:</label>
                            <select class="form-select form-select-solid" id="kullanici_id" name="kullanici_id" required>
                                <option value="">Kullanıcı seçiniz</option>
                                <?php foreach ($kullanicilar as $kullanici): ?>
                                    <option value="<?= $kullanici['id'] ?>">
                                        <?= htmlspecialchars($kullanici['ad'] . ' ' . $kullanici['soyad']) ?> 
                                        (<?= htmlspecialchars($kullanici['email']) ?>)
                                        <?= !empty($kullanici['cihaz_token']) ? ' - Token: ✓' : ' - Token: ✗' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="baslik">Bildirim Başlığı:</label>
                            <input class="form-control form-control-solid" type="text" id="baslik" name="baslik" value="Test Bildirimi" required>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="mesaj">Bildirim Mesajı:</label>
                            <textarea class="form-control form-control-solid" id="mesaj" name="mesaj" rows="3" required>Bu bir test bildirimidir. OneSignal entegrasyonu çalışıyor!</textarea>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Card footer-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-send fs-2"></i>Test Bildirimi Gönder
                        </button>
                    </div>
                    <!--end::Card footer-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

        <!--begin::Card-->
        <div class="card mt-8">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>Kullanıcı Listesi</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_kullanicilar">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Kullanıcı</th>
                                <th class="min-w-125px">E-posta</th>
                                <th class="min-w-125px">Cihaz Token</th>
                                <th class="min-w-125px">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php if (!empty($kullanicilar)): ?>
                                <?php foreach ($kullanicilar as $kullanici): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-35px me-3">
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                        <?= strtoupper(substr($kullanici['ad'] ?? 'A', 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-900 fw-bold"><?= htmlspecialchars($kullanici['ad'] . ' ' . $kullanici['soyad']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-600"><?= htmlspecialchars($kullanici['email']) ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($kullanici['cihaz_token'])): ?>
                                                <span class="badge badge-light-success">Token Mevcut</span>
                                                <br><small class="text-muted"><?= substr($kullanici['cihaz_token'], 0, 20) ?>...</small>
                                            <?php else: ?>
                                                <span class="badge badge-light-danger">Token Yok</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($kullanici['cihaz_token'])): ?>
                                                <span class="badge badge-light-success">Hazır</span>
                                            <?php else: ?>
                                                <span class="badge badge-light-warning">Token Gerekli</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-outline ki-user-tick fs-3x text-muted mb-3"></i>
                                            <span class="text-muted">Bildirim izni olan kullanıcı bulunamadı</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<?php require_once 'app/Views/layouts/footer.php'; ?> 