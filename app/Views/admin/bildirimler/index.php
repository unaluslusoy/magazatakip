<?php
$title = "Bildirimler";
$link = "Bildirimler";

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
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>Gönderilen Bildirimler</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <a href="/admin/bildirim_gonder" class="btn btn-primary">
                            <i class="ki-outline ki-plus fs-2"></i>Yeni Bildirim
                        </a>
                    </div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Filters-->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Tarih Filtresi</label>
                        <select class="form-select" id="dateFilter">
                            <option value="all" <?= $dateFilter === 'all' ? 'selected' : '' ?>>Tümü</option>
                            <option value="today" <?= $dateFilter === 'today' ? 'selected' : '' ?>>Bugün</option>
                            <option value="week" <?= $dateFilter === 'week' ? 'selected' : '' ?>>Son 7 Gün</option>
                            <option value="month" <?= $dateFilter === 'month' ? 'selected' : '' ?>>Son 30 Gün</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alıcı Tipi</label>
                        <select class="form-select" id="aliciFilter">
                            <option value="all" <?= $aliciFilter === 'all' ? 'selected' : '' ?>>Tümü</option>
                            <option value="tum" <?= $aliciFilter === 'tum' ? 'selected' : '' ?>>Tüm Kullanıcılar</option>
                            <option value="bireysel" <?= $aliciFilter === 'bireysel' ? 'selected' : '' ?>>Bireysel</option>
                            <option value="grup" <?= $aliciFilter === 'grup' ? 'selected' : '' ?>>Grup</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Durum</label>
                        <select class="form-select" id="durumFilter">
                            <option value="all" <?= $durumFilter === 'all' ? 'selected' : '' ?>>Tümü</option>
                            <option value="gonderildi" <?= $durumFilter === 'gonderildi' ? 'selected' : '' ?>>Gönderildi</option>
                            <option value="beklemede" <?= $durumFilter === 'beklemede' ? 'selected' : '' ?>>Beklemede</option>
                            <option value="basarisiz" <?= $durumFilter === 'basarisiz' ? 'selected' : '' ?>>Başarısız</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Arama</label>
                        <input type="text" class="form-control" id="searchTerm" placeholder="Başlık veya mesaj ara..." value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                </div>
                <!--end::Filters-->

                <!--begin::Stats-->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-primary">
                                            <i class="ki-outline ki-notification-on text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Toplam</div>
                                        <div class="fs-4 fw-bold text-gray-900"><?= $notificationCounts['toplam'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-success">
                                            <i class="ki-outline ki-check text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Gönderildi</div>
                                        <div class="fs-4 fw-bold text-gray-900"><?= $notificationCounts['gonderildi'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-warning">
                                            <i class="ki-outline ki-time text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Beklemede</div>
                                        <div class="fs-4 fw-bold text-gray-900"><?= $notificationCounts['beklemede'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-danger">
                                            <i class="ki-outline ki-cross text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Başarısız</div>
                                        <div class="fs-4 fw-bold text-gray-900"><?= $notificationCounts['basarisiz'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-info">
                                            <i class="ki-outline ki-calendar text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fs-6 text-gray-600">Bugün</div>
                                        <div class="fs-4 fw-bold text-gray-900"><?= $notificationCounts['bugun'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Stats-->

                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_bildirimler">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Başlık</th>
                                <th class="min-w-125px">Mesaj</th>
                                <th class="min-w-125px">Gönderen</th>
                                <th class="min-w-125px">Alıcı Tipi</th>
                                <th class="min-w-125px">Durum</th>
                                <th class="min-w-125px">Tarih</th>
                                <th class="text-end min-w-100px">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php if (!empty($bildirimler)): ?>
                                <?php foreach ($bildirimler as $bildirim): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold"><?= htmlspecialchars($bildirim['baslik']) ?></span>
                                                <?php if (!empty($bildirim['url'])): ?>
                                                    <span class="text-muted fs-7"><?= htmlspecialchars($bildirim['url']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-600"><?= htmlspecialchars(substr($bildirim['mesaj'], 0, 50)) ?><?= strlen($bildirim['mesaj']) > 50 ? '...' : '' ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-35px me-3">
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                        <?= strtoupper(substr($bildirim['gonderici_adi'] ?? 'A', 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-900 fw-bold"><?= htmlspecialchars($bildirim['gonderici_adi'] ?? 'Sistem') ?> <?= htmlspecialchars($bildirim['gonderici_soyadi'] ?? '') ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-primary"><?= ucfirst($bildirim['alici_tipi']) ?></span>
                                        </td>
                                        <td>
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
                                            <span class="badge badge-<?= $durumClass ?>"><?= $durumText ?></span>
                                        </td>
                                        <td>
                                            <span class="text-gray-600"><?= date('d.m.Y H:i', strtotime($bildirim['gonderim_tarihi'])) ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                İşlemler
                                                <i class="ki-outline ki-down fs-5 m-0"></i>
                                            </a>
                                            <!--begin::Menu-->
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="/admin/bildirimler/detay/<?= $bildirim['id'] ?>" class="menu-link px-3">
                                                        <i class="ki-outline ki-eye fs-2"></i>Detay
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="/admin/bildirimler/sil/<?= $bildirim['id'] ?>" class="menu-link px-3 text-danger" onclick="return confirm('Bu bildirimi silmek istediğinizden emin misiniz?')">
                                                        <i class="ki-outline ki-trash fs-2"></i>Sil
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                            </div>
                                            <!--end::Menu-->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-outline ki-notification-off fs-3x text-muted mb-3"></i>
                                            <span class="text-muted">Henüz bildirim gönderilmemiş</span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtre değişikliklerini dinle
    const filters = ['dateFilter', 'aliciFilter', 'durumFilter'];
    filters.forEach(filterId => {
        document.getElementById(filterId).addEventListener('change', applyFilters);
    });
    
    // Arama input'unu dinle
    let searchTimeout;
    document.getElementById('searchTerm').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
    
    function applyFilters() {
        const dateFilter = document.getElementById('dateFilter').value;
        const aliciFilter = document.getElementById('aliciFilter').value;
        const durumFilter = document.getElementById('durumFilter').value;
        const searchTerm = document.getElementById('searchTerm').value;
        
        const params = new URLSearchParams();
        if (dateFilter !== 'all') params.append('date_filter', dateFilter);
        if (aliciFilter !== 'all') params.append('alici_filter', aliciFilter);
        if (durumFilter !== 'all') params.append('durum_filter', durumFilter);
        if (searchTerm) params.append('search', searchTerm);
        
        const url = '/admin/bildirimler' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?> 