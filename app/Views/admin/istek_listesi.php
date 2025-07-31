<?php
$title = "<h2>İş Emri Listesi</h2>";
$link = "İş Emirleri";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';

function getBadgeClass($durum) {
    switch ($durum) {
        case 'Yeni':
            return 'badge-light-primary';
        case 'Beklemede':
            return 'badge-light-warning';
        case 'Devam Ediyor':
            return 'badge-light-info';
        case 'Tamamlandı':
            return 'badge-light-success';
        case 'Durduruldu':
            return 'badge-light-danger';
        case 'Gözden Geçiriliyor':
            return 'badge-light-info';
        case 'Onay Bekliyor':
            return 'badge-light-warning';
        case 'Red Edildi':
            return 'badge-light-danger';
        case 'Revize Ediliyor':
            return 'badge-light-info';
        case 'Erteleme':
            return 'badge-light-warning';
        case 'İptal Edildi':
            return 'badge-light-danger';
        case 'Sorun Var':
            return 'badge-light-danger';
        case 'Tekrar Açıldı':
            return 'badge-light-primary';
        default:
            return 'badge-light-secondary';
    }
}
?>
<!--begin::Card-->
<div class="card" data-select2-id="select2-data-122-rqwu">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6" data-select2-id="select2-data-121-i5an">
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" id="searchInput" class="form-control form-control-solid w-250px ps-13" placeholder="Arama">
            </div>
            <!--end::Search-->

        </div>
        <!--begin::Card title-->



        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex  justify-content-md-end">
                <div class="col-lg-12">
                    <div class="btn-light-primary me-3" data-select2-id="select2-data-119-ixp4">
                        <select id="magazaFilter" class="form-select form-select-solid" data-control="select2" data-placeholder="Mağaza" aria-hidden="true">
                            <option value="">Tüm Mağazalar</option>
                            <?php foreach ($magazalar as $magaza): ?>
                                <option value="<?= htmlspecialchars($magaza['ad']); ?>"><?= htmlspecialchars($magaza['ad']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class=" btn-light-primary me-3" data-select2-id="select2-data-119-ixp4">
                        <select id="durumFilter" class="form-select form-select-solid" data-control="select2" data-placeholder="Durum" aria-hidden="true">
                            <option value="">Tüm Durumlar</option>
                            <option value="Yeni">Yeni</option>
                            <option value="Beklemede">Beklemede</option>
                            <option value="Devam Ediyor">Devam Ediyor</option>
                            <option value="Tamamlandı">Tamamlandı</option>
                            <option value="Durduruldu">Durduruldu</option>
                            <option value="Gözden Geçiriliyor">Gözden Geçiriliyor</option>
                            <option value="Onay Bekliyor">Onay Bekliyor</option>
                            <option value="Red Edildi">Red Edildi</option>
                            <option value="Revize Ediliyor">Revize Ediliyor</option>
                            <option value="Erteleme">Erteleme</option>
                            <option value="İptal Edildi">İptal Edildi</option>
                            <option value="Sorun Var">Sorun Var</option>
                            <option value="Tekrar Açıldı">Tekrar Açıldı</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base" data-select2-id="select2-data-120-pbmy">

                <!--end::Filter-->
                <!--begin::Export-->
                <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_customers_export_modal">
                    <i class="ki-outline ki-exit-up fs-2"></i>Dışa Aktar</button>
                <!--end::Export-->
                <!--begin::Add customer-->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_istek">İş Emri Ekle</button>
                <!--end::Add customer-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Group actions-->
            <div class="d-flex justify-content-end align-items-center d-none" data-kt-customer-table-toolbar="selected">
                <div class="fw-bold me-5">
                    <span class="me-2" data-kt-customer-table-select="selected_count">1</span>Seçildi</div>
                <button type="button" class="btn btn-danger" data-kt-customer-table-select="delete_selected">Seçili Olan İş Listesini Sil</button>
            </div>
            <!--end::Group actions-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Table-->
        <div id="kt_customers_table_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
            <div id="" class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_customers_table">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0" role="row">
                        <th class="w-10px pe-2 dt-orderable-none">
                                <span class="dt-column-title">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_customers_table .form-check-input" value="1">
                                    </div>
								</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Şube K.ID</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Mağaza</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Şube İstek</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Şube Açıklama</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">İş Önemi</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Oluşturma Tarihi</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Yönetici Açıklaması</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Durum</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="min-w-125px dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Görevli</span>
                            <span class="dt-column-order"></span>
                        </th>
                        <th class="text-end min-w-70px dt-orderable-none">
                            <span class="dt-column-title">İşlemler</span>
                            <span class="dt-column-order"></span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    <?php foreach($istekler as $istek): ?>
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="<?= $istek['id']; ?>">
                                </div>
                            </td>
                            <td><?= $istek['kullanici_id']; ?></td>
                            <td><span class="text-gray-800 mb-1"><?= $istek['magaza']; ?></span></td>
                            <td><span class="text-gray-600 mb-1"><?= $istek['baslik']; ?></span></td>
                            <td><span class="text-gray-500 mb-1"><?= $istek['aciklama']; ?></span></td>
                            <td><span class="badge badge-light fw-bold"><?= $istek['derece']; ?></span></td>
                            <td><span class="text-gray-500 mb-1"><?= $istek['tarih']; ?></span></td>
                            <td><span class="text-gray-500 mb-1"><?= $istek['is_aciklamasi']; ?></span></td>
                            <td>
                                <div class="badge <?= getBadgeClass($istek['durum']); ?>"><?= htmlspecialchars($istek['durum']); ?></div>
                            </td>
                            <td><?= $istek['personel_adi']; ?> <?= $istek['personel_soyad']; ?></td>
                            <td class="text-end">
                                <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">İşlemler
                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="#" type="button"  data-bs-toggle="modal" data-bs-target="#isAtama_<?= $istek['id']; ?>" class="menu-link px-3">Güncelle</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="/admin/istek/sil/<?= $istek['id']; ?>" class="menu-link px-3" data-kt-customer-table-filter="delete_row">
                                            <i class="fas fa-trash fs-4 me-2"></i> Sil
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="isAtama_<?= $istek['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered mw-650px">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">İş Emri Güncelle</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" action="/admin/istek/guncelle/<?= $istek['id']; ?>">
                                            <div class="mb-3">
                                                <label for="baslik" class="form-label">Başlık:</label>
                                                <input type="text" name="baslik" id="baslik" class="form-control" value="<?= $istek['baslik']; ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label for="aciklama" class="form-label">Açıklama:</label>
                                                <textarea name="aciklama" id="aciklama" class="form-control" disabled><?= $istek['aciklama']; ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="magaza" class="form-label">Mağaza:</label>
                                                <input type="text" name="magaza" id="magaza" class="form-control" value="<?= $istek['magaza']; ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label for="derece" class="form-label">İstek Derecesi:</label>
                                                <input type="text" name="derece" id="derece" class="form-control" value="<?= $istek['derece']; ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label for="durum" class="form-label">Durum:</label>
                                                <select name="durum" id="durum" class="form-select" required>
                                                    <option value="Yeni" <?= $istek['durum'] == 'Yeni' ? 'selected' : ''; ?>>Yeni</option>
                                                    <option value="Beklemede" <?= $istek['durum'] == 'Beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                                                    <option value="Devam Ediyor" <?= $istek['durum'] == 'Devam Ediyor' ? 'selected' : ''; ?>>Devam Ediyor</option>
                                                    <option value="Tamamlandı" <?= $istek['durum'] == 'Tamamlandı' ? 'selected' : ''; ?>>Tamamlandı</option>
                                                    <option value="Durduruldu" <?= $istek['durum'] == 'Durduruldu' ? 'selected' : ''; ?>>Durduruldu</option>
                                                    <option value="Gözden Geçiriliyor" <?= $istek['durum'] == 'Gözden Geçiriliyor' ? 'selected' : ''; ?>>Gözden Geçiriliyor</option>
                                                    <option value="Onay Bekliyor" <?= $istek['durum'] == 'Onay Bekliyor' ? 'selected' : ''; ?>>Onay Bekliyor</option>
                                                    <option value="Red Edildi" <?= $istek['durum'] == 'Red Edildi' ? 'selected' : ''; ?>>Red Edildi</option>
                                                    <option value="Revize Ediliyor" <?= $istek['durum'] == 'Revize Ediliyor' ? 'selected' : ''; ?>>Revize Ediliyor</option>
                                                    <option value="Red Edildi" <?= $istek['durum'] == 'Red Edildi' ? 'selected' : ''; ?>>Red Edildi</option>
                                                    <option value="Erteleme" <?= $istek['durum'] == 'bekliyor' ? 'selected' : ''; ?>>Erteleme</option>
                                                    <option value="İptal Edildi" <?= $istek['durum'] == 'İptal Edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                                                    <option value="Sorun Var" <?= $istek['durum'] == 'Sorun Var' ? 'selected' : ''; ?>>Sorun Var</option>
                                                    <option value="Tekrar Açıldı" <?= $istek['durum'] == 'Tekrar Açıldı' ? 'selected' : ''; ?>>Tekrar Açıldı</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="personel_id" class="form-label">Görevli:</label>
                                                <select name="personel_id" id="personel_id" class="form-select">
                                                    <?php if (!empty($personeller)): ?>
                                                        <?php foreach ($personeller as $personel): ?>
                                                            <option value="<?= $personel['id']; ?>" <?= $personel['id'] == $istek['personel_id'] ? 'selected' : ''; ?>><?= $personel['ad']; ?></option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">Görevli atanmamış</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="is_aciklamasi" class="form-label">İş Açıklaması:</label>
                                                <textarea name="is_aciklamasi" id="is_aciklamasi" class="form-control"><?= $istek['is_aciklamasi']; ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi:</label>
                                                <input type="date" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control" value="<?= $istek['baslangic_tarihi']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi:</label>
                                                <input type="date" name="bitis_tarihi" id="bitis_tarihi" class="form-control" value="<?= $istek['bitis_tarihi']; ?>">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Güncelle</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>

<div class="modal fade" id="kt_modal_add_istek" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <?= include 'app/Views/admin/istek_ekle.php'; ?>
        </div>
    </div>
</div>

<?php
require_once 'app/Views/layouts/footer.php';
?>

<script>
    $(document).ready(function() {
        var table = $('#kt_customers_table').DataTable();

        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        $('#magazaFilter').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(2).search(val ? '^' + val + '$' : '', true, false).draw();
        });

        $('#durumFilter').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(8).search(val ? '^' + val + '$' : '', true, false).draw();
        });
    });
</script>
