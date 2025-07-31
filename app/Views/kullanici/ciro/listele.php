<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>
<div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">
    <!--begin::Main-->
    <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
        <!--begin::Content wrapper-->

        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6 ">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                            Ciro işlemleri
                        </h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="/anasayfa" class="text-muted text-hover-primary">
                                    Anasayfa
                                </a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-500 w-5px h-2px"></span>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                Ciro işlemleri
                            </li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <!--begin::Primary button-->
                        <a href="/ciro/ekle" class="btn btn-sm fw-bold btn-primary" >
                            Ciro Ekle
                        </a>
                        <!--end::Primary button-->
                    </div>

                </div>
                <!--end::Toolbar container-->
             </div>
            <!--end::Toolbar-->
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content  flex-column-fluid ">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container  container-xxl ">
                    <!--begin::Products-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <!--begin::Card title-->
                            <div class="card-title">

                            </div>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                <div class="w-100 mw-150px">
                                    <!--begin::Select2-->
                                    <select class="form-select form-select-solid select2-hidden-accessible" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status" data-select2-id="select2-data-9-lmpj" tabindex="-1" aria-hidden="true" data-kt-initialized="1">
                                        <option data-select2-id="select2-data-11-ja9s"></option>
                                        <option value="all">All</option>
                                        <option value="published">Published</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="inactive">Inactive</option>
                                    </select><span class="select2 select2-container select2-container--bootstrap5" dir="ltr" data-select2-id="select2-data-10-yvq2" style="width: 100%;"><span class="selection"><span class="select2-selection select2-selection--single form-select form-select-solid" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-gwdi-container" aria-controls="select2-gwdi-container"><span class="select2-selection__rendered" id="select2-gwdi-container" role="textbox" aria-readonly="true" title="Status"><span class="select2-selection__placeholder">Status</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                                    <!--end::Select2-->
                                </div>
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div id="kt_ecommerce_products_table_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                                <div id="" class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_ecommerce_products_table" style="width: 1125.5px;">
                                        <colgroup>
                                            <col data-dt-column="0" style="width: 36.3906px;">
                                            <col data-dt-column="1" style="width: 10px;">
                                            <col data-dt-column="2" style="width: 20px;">
                                            <col data-dt-column="3" style="width: 50px;">
                                            <col data-dt-column="4" style="width: 50px;">
                                            <col data-dt-column="5" style="width: 50px;">
                                            <col data-dt-column="6" style="width: 50px;">
                                            <col data-dt-column="7" style="width: 50px;">
                                            <col data-dt-column="8" style="width: 50px;">
                                            <col data-dt-column="9" style="width: 50px">
                                            <col data-dt-column="10" style="width: 140.672px;">

                                        </colgroup>
                                        <thead>
                                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0" role="row">
                                            <th class="w-10px pe-2 dt-orderable-none" data-dt-column="0" rowspan="1" colspan="1" aria-label="">
                                                <span class="dt-column-title">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_products_table .form-check-input" value="1">
                                                    </div>
                                                </span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="min-w-20px dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1" colspan="1" aria-label="Product: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Şube</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="2" rowspan="1" colspan="1" aria-label="SKU: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Ekleme Tarihi</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-70px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1" colspan="1" aria-label="Qty: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Gün</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-70px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="4" rowspan="1" colspan="1" aria-label="Price: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Nakit</span>
                                                <span class="dt-column-order"> </span>
                                            </th>
                                            <th class="text-end min-w-70px dt-orderable-asc dt-orderable-desc" data-dt-column="5" rowspan="1" colspan="1" aria-label="Rating: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Kredi Kartı</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-70px dt-orderable-asc dt-orderable-desc" data-dt-column="6" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">YemekSepeti</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-70px dt-orderable-asc dt-orderable-desc" data-dt-column="7" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Getir Çarşı</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-70px dt-orderable-asc dt-orderable-desc" data-dt-column="8" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">TrendyolGO</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-70px dt-orderable-asc dt-orderable-desc" data-dt-column="9" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Durum</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-100px dt-orderable-none" data-dt-column="7" rowspan="1" colspan="10" aria-label="Actions">
                                                <span class="dt-column-title">Aksiyon</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">

                                        <?php if (!empty($ciroListesi)) : ?>
                                            <?php foreach ($ciroListesi as $ciro) : ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" value="1">
                                                        </div>
                                                    </td>
                                                    <td><?= $ciro['magaza_id'] ?></td>
                                                    <td><?= $ciro['ekleme_tarihi'] ?></td>
                                                    <td><?= $ciro['gun'] ?></td>
                                                    <td><?= $ciro['nakit'] ?></td>
                                                    <td><?= $ciro['kredi_karti'] ?></td>
                                                    <td><?= $ciro['carliston'] ?></td>
                                                    <td><?= $ciro['getir_carsi'] ?></td>
                                                    <td><?= $ciro['trendyolgo'] ?></td>
                                                    <td><?= $ciro['durum'] ?></td>
                                                    <td class="text-end">
                                                        <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                            İşlemler
                                                            <i class="ki-outline ki-down fs-5 ms-1"></i>                    </a>
                                                        <!--begin::Menu-->
                                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                            <!--begin::Menu item-->
                                                            <div class="menu-item px-3">
                                                                <a href="#" class="menu-link px-3">
                                                                    Düzenle
                                                                </a>
                                                            </div>
                                                            <!--end::Menu item-->

                                                            <!--begin::Menu item-->
                                                            <div class="menu-item px-3">
                                                                <a href="#" class="menu-link px-3" data-kt-ecommerce-product-filter="delete_row">
                                                                    Sil
                                                                </a>
                                                            </div>
                                                            <!--end::Menu item-->
                                                        </div>
                                                        <!--end::Menu-->
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td class="text-center" colspan="9">
                                                    <i class="ki-duotone ki-information-3 w-30px h-30px ">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i> Veriyok
                                                </td>
                                            </tr>
                                        <?php endif; ?>
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
                                                    <a class="page-link previous" aria-controls="kt_ecommerce_products_table" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1"><i class="previous"></i></a>
                                                </li>
                                                <li class="dt-paging-button page-item active">
                                                    <a href="#" class="page-link" aria-controls="kt_ecommerce_products_table" aria-current="page" data-dt-idx="0" tabindex="0">1</a>
                                                </li>
                                                <li class="dt-paging-button page-item">
                                                    <a href="#" class="page-link next" aria-controls="kt_ecommerce_products_table" aria-label="Next" data-dt-idx="next" tabindex="0"><i class="next"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Products-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->
    </div>
    <!--End::Main-->
</div>


<?php require_once 'app/Views/kullanici/layout/footer.php';?>

