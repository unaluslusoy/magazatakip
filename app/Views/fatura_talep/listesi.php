<?php require_once 'app/Views/kullanici/layout/header.php'; ?>
<?php require_once 'app/Views/kullanici/layout/navbar.php'; ?>

<div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">
    <!--begin::Main-->
    <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
        <!--begin::Content wrapper-->
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <!--begin::Products-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Fatura Talep Listesi</h3>
                </div>
                <!--end::Card title-->
                <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message'], $_SESSION['message_type']);
                        ?>
                    </div>
                <?php endif; ?>
                <!--begin::Card toolbar-->
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    <div class="w-100 mw-150px">

                    </div>
                    <!--begin::Add product-->
                    <a href="/fatura_talep/olustur" class="btn btn-primary">
                        Yeni Fatura Talebi Oluştur
                    </a>
                    <!--end::Add product-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table-->
                <div id="kt_ecommerce_products_table_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                    <div id="" class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_ecommerce_products_table" style="width: 2106.5px;">
                            <colgroup>
                                <col data-dt-column="0" style="20px" >
                                <col data-dt-column="1" style="width: 36px;">
                                <col data-dt-column="2" style="width: 554.125px;">
                                <col data-dt-column="3" style="width: 278.062px;">
                                <col data-dt-column="4" style="width: 254.125px;">
                                <col data-dt-column="5" style="width: 254.125px;">
                                <col data-dt-column="6" style="width: 254.125px;">
                                <col data-dt-column="7" style="width: 267.297px;">
                                <col data-dt-column="8" style="width: 267.297px;">
                                <col data-dt-column="9" style="width: 267.297px;">
                                <col data-dt-column="10" style="width: 267.297px;">
                                <col data-dt-column="11" style="width: 267.297px;">
                                <col data-dt-column="12" style="width: 267.297px;">
                            </colgroup>
                            <thead>

                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0" role="row">
                                <th class="w-10px pe-2 dt-orderable-none" data-dt-column="0" rowspan="1" colspan="1" aria-label=" ">
                                    <span class="dt-column-title">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_products_table .form-check-input" value="1">
                                        </div>
                                    </span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="dt-orderable-asc" data-dt-column="1" rowspan="1" colspan="1" aria-label="ID: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">ID</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-200px dt-type-numeric " data-dt-column="2" rowspan="1" colspan="1" aria-label="Müşteri Adı Soyadı/Ticari Ünvan: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Müşteri Adı Soyadı/Ticari Ünvan</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-70px dt-type-numeric " data-dt-column="3" rowspan="1" colspan="1" aria-label="Müşteri Telefon: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Müşteri Telefon</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px dt-type-numeric  " data-dt-column="4" rowspan="1" colspan="1" aria-label="Müşteri Email: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Müşteri Email</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px " data-dt-column="5" rowspan="1" colspan="1" aria-label="Vergi Numarası: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">TC/Vergi Numarası</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px " data-dt-column="6" rowspan="1" colspan="1" aria-label="Müşteri Dairesi: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Müşteri Dairesi</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px " data-dt-column="7" rowspan="1" colspan="1" aria-label="Müsteri Adresi: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Müsteri Adresi</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px " data-dt-column="8" rowspan="1" colspan="1" aria-label="Açıklama: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Açıklama</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px " data-dt-column="9" rowspan="1" colspan="1" aria-label="Drum: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Durum</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px " data-dt-column="10" rowspan="1" colspan="1" aria-label="Fatura: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">Fatura</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-center min-w-100px" data-dt-column="11" rowspan="1" colspan="1" aria-label="Oluşturulma Tarihi: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">O. Tarihi</span>
                                    <span class="dt-column-order"></span>
                                </th>
                                <th class="text-end min-w-100px " data-dt-column="12" rowspan="1" colspan="1" aria-label="İşlemler Dairesi: Activate to sort" tabindex="0">
                                    <span class="dt-column-title" role="button">İşlemler</span>
                                    <span class="dt-column-order"></span>
                                </th>

                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">

                                <?php foreach ($faturaTalepleri as $talep): ?>
                            <tr>
                                <td class="text-center">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($talep['id']); ?>">
                                    </div>
                                </td>
                                <td class="text-start pe-0 dt-type-numeric">
                                    <span class="fw-bold"><?php echo htmlspecialchars($talep['id']); ?></span>
                                </td>
                                <td class="text-center" >
                                    <div class="d-flex align-items-center">
                                        <div class="ms-5">
                                            <span class="text-gray-800 text-hover-primary fs-5 fw-bold"><?php echo htmlspecialchars($talep['musteri_ad'] ?? 'veri yok'); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center pe-0 dt-type-numeric">
                                    <span class="fw-bold"><?php echo htmlspecialchars($talep['musteri_telefon'] ?? 'veri yok'); ?></span>
                                </td>

                                <td><?php echo htmlspecialchars($talep['musteri_email'] ?? 'veri yok'); ?></td>

                                <td class="text-center pe-0 dt-type-numeric" >
                                    <span class="fw-bold ms-3">
                                        <?php echo htmlspecialchars($talep['musteri_vergi_no'] ?? 'veri yok'); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($talep['musteri_vergi_dairesi'] ?? 'veri yok'); ?></td>

                                <td class="text-center min-w-225px"><?php echo htmlspecialchars($talep['musteri_adres'] ?? 'veri yok'); ?></td>

                                <td class="text-center min-w-225px"><?php echo htmlspecialchars($talep['aciklama'] ?? 'veri yok'); ?></td>

                                <td class="text-center pe-0">
                                    <div class="badge badge-light-primary"><?php echo htmlspecialchars($talep['durum'] ?? 'veri yok'); ?></div>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($talep['fatura_pdf_path'])): ?>
                                        <a href="/public/uploads/<?php echo htmlspecialchars($talep['fatura_pdf_path']); ?>" target="_blank">Görüntüle</a>
                                    <?php else: ?>
                                        Veri yok
                                    <?php endif; ?>
                                </td>

                                <td class="text-center"><?php echo htmlspecialchars($talep['olusturulma_tarihi']); ?></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        İşlemler
                                        <i class="ki-outline ki-down fs-5 ms-1"></i>
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="/fatura_talep/duzenle/<?php echo htmlspecialchars($talep['id']); ?>" class="menu-link px-3">Düzenle</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item
                                        <form class="menu-item px-3" action="/fatura_talep/sil/<?php echo htmlspecialchars($talep['id']); ?>" method="post" style="display:inline-block;">
                                            <a type="submit" class="menu-link px-3btn">Sil</a>
                                        </form>
                                        end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>
                    <div id="" class="row">
                        <div id="" class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar"><div>
                                <select name="kt_ecommerce_products_table_length" aria-controls="kt_ecommerce_products_table" class="form-select form-select-solid form-select-sm" id="dt-length-0">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label for="dt-length-0"></label>
                            </div>
                        </div>
                        <div id="" class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                            <div class="dt-paging paging_simple_numbers">
                                <ul class="pagination">
                                    <li class="dt-paging-button page-item disabled">
                                        <a class="page-link previous" aria-controls="kt_ecommerce_products_table" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1">
                                            <i class="previous"></i>
                                        </a>
                                    </li>
                                    <li class="dt-paging-button page-item active">
                                        <a href="#" class="page-link" aria-controls="kt_ecommerce_products_table" aria-current="page" data-dt-idx="0" tabindex="0">1</a>
                                    </li>

                                    <li class="dt-paging-button page-item"><a href="#" class="page-link next" aria-controls="kt_ecommerce_products_table" aria-label="Next" data-dt-idx="next" tabindex="0">
                                            <i class="next"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Table-->    </div>
            <!--end::Card body-->
        </div>
        <!--end::Products-->        </div>
<!--end::Content wrapper-->
</div>
<!--End::Main-->
</div>
<?php require_once 'app/Views/kullanici/layout/footer.php'; ?>
