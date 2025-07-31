<?php
$title = "<h2>Personel Listesi</h2>";
$link = "Personel";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-customer-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Personel Ara">
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-outline ki-filter fs-2"></i>Filter
                    </button>
                    <!--begin::Menu 1-->
                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true" id="kt-toolbar-filter">
                        <!--begin::Header-->
                        <div class="px-7 py-5">
                            <div class="fs-4 text-gray-900 fw-bold">Flitrele</div>
                        </div>
                        <!--end::Header-->
                        <!--begin::Separator-->
                        <div class="separator border-gray-200"></div>
                        <!--end::Separator-->
                        <!--begin::Content-->
                        <div class="px-7 py-5">
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <!--begin::Label-->
                                <label class="form-label fs-5 fw-semibold mb-3">Ay:</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select class="form-select form-select-solid fw-bold select2-hidden-accessible" data-kt-select2="true" data-placeholder="Select option" data-allow-clear="true" data-kt-customer-table-filter="month" data-dropdown-parent="#kt-toolbar-filter" data-select2-id="select2-data-7-t32f" tabindex="-1" aria-hidden="true" data-kt-initialized="1">
                                    <option data-select2-id="select2-data-9-renb"></option>
                                    <option value="aug">August</option>
                                    <option value="sep">September</option>
                                    <option value="oct">October</option>
                                    <option value="nov">November</option>
                                    <option value="dec">December</option>
                                </select><span class="select2 select2-container select2-container--bootstrap5" dir="ltr" data-select2-id="select2-data-8-u9fo" style="width: 100%;"><span class="selection"><span class="select2-selection select2-selection--single form-select form-select-solid fw-bold" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-8283-container" aria-controls="select2-8283-container"><span class="select2-selection__rendered" id="select2-8283-container" role="textbox" aria-readonly="true" title="Select option"><span class="select2-selection__placeholder">Select option</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" data-kt-customer-table-filter="reset">Sıfırla</button>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true" data-kt-customer-table-filter="filter">Uygula</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Menu 1-->
                    <!--end::Filter-->
                    <!--begin::Export-->
                    <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_customers_export_modal">
                        <i class="ki-outline ki-exit-up fs-2"></i>Dışarı Aktar
                    </button>
                    <!--end::Export-->
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_personel_ekle">Yeni Personel Ekle</button>
                    <!--end::Add customer-->
                </div>
                <!--end::Toolbar-->
                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-customer-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-customer-table-select="selected_count">1</span>Seçildi
                    </div>
                    <button type="button" class="btn btn-danger" data-kt-customer-table-select="delete_selected">Seçili Olanları Sil</button>
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
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_customers_table" style="width: 1466.5px;">
                        <colgroup>
                            <col data-dt-column="0" style="width: 36.3906px;">
                            <col data-dt-column="1" style="width: 207.109px;">
                            <col data-dt-column="2" style="width: 257.047px;">
                            <col data-dt-column="3" style="width: 296.297px;">
                            <col data-dt-column="4" style="width: 216.406px;">
                            <col data-dt-column="5" style="width: 278.984px;">
                            <col data-dt-column="6" style="width: 174.266px;">
                        </colgroup>
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0" role="row">
                            <th class="w-10px pe-2 dt-orderable-none" data-dt-column="0" rowspan="1" colspan="1" aria-label="">
                                <span class="dt-column-title">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_customers_table .form-check-input" value="1">
                                    </div>
                                </span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1" colspan="1" aria-label="Personel ID" tabindex="0">
                                <span class="dt-column-title" role="button">Personel ID</span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="2" rowspan="1" colspan="1" tabindex="0">
                                <span class="dt-column-title" role="button">Adı Soyadı</span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1" colspan="1" aria-label="Eposta" tabindex="0">
                                <span class="dt-column-title" role="button">Eposta</span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="4" rowspan="1" colspan="1" aria-label="GSM Numarası" tabindex="0">
                                <span class="dt-column-title" role="button">GSM Numarası</span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="5" rowspan="1" colspan="1" aria-label="Görevi" tabindex="0">
                                <span class="dt-column-title" role="button">Görevi</span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="6" rowspan="1" colspan="1" aria-label="İşe Başlangıcı" tabindex="0">
                                <span class="dt-column-title" role="button">İşe Başlangıcı</span>
                                <span class="dt-column-order"></span>
                            </th>
                            <th class="text-end min-w-70px dt-orderable-none" data-dt-column="7" rowspan="1" colspan="1" aria-label="İşlem">
                                <span class="dt-column-title">İşlem</span>
                                <span class="dt-column-order"></span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        <?php foreach ($personeller as $personel): ?>
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1">
                                    </div>
                                </td>
                                <td><?= $personel['id']; ?></td>
                                <td><?= $personel['ad'] . ' ' . $personel['soyad']; ?></td>
                                <td><?= $personel['eposta']; ?></td>
                                <td><?= $personel['telefon']; ?></td>
                                <td><?= $personel['pozisyon']; ?></td>
                                <td><?= $personel['ise_baslama_tarihi']; ?></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">İşlemler
                                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-250px py-4" data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-6">

                                            <a href="/admin/personel/detay/<?= $personel['id']; ?>" class="menu-link px-4"><i class="bi bi-person-vcard-fill p-2 "> </i>  Personel Profil</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-6">
                                            <a href="/admin/personel/guncelle/<?= $personel['id']; ?>" class="menu-link px-4 ql-color-green"><i class=" bi bi-pencil-square p-2"></i>Düzenle</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-6">
                                            <a href="/admin/personel/sil/<?= $personel['id']; ?>" class="menu-link px-4 ql-color-red"><i class="bi bi-trash3-fill p-2"></i>Sil</a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="" class="row">
                    <div id="" class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar">
                        <div>
                            <select name="kt_customers_table_length" aria-controls="kt_customers_table" class="form-select form-select-solid form-select-sm" id="dt-length-0">
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
                                    <a class="page-link previous" aria-controls="kt_customers_table" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1">
                                        <i class="previous"></i>
                                    </a>
                                </li>
                                <li class="dt-paging-button page-item active">
                                    <a href="#" class="page-link" aria-controls="kt_customers_table" aria-current="page" data-dt-idx="0" tabindex="0">1</a>
                                </li>
                                <li class="dt-paging-button page-item disabled">
                                    <a class="page-link next" aria-controls="kt_customers_table" aria-disabled="true" aria-label="Next" data-dt-idx="next" tabindex="-1">
                                        <i class="next"></i>
                                    </a>
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


    <!--begin::Modal - Personel Ekle-->
    <div class="modal fade" id="kt_modal_personel_ekle" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header py-5">
                    <!--begin::Modal title-->
                    <h2>Personel Ekle</h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">


                    <!--begin::Form-->
                    <form id="kt_modal_personel_ekle_form" class="form" action="/admin/personel/ekle" method="POST">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-semibold mb-2">Adı</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Adı" name="ad" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-semibold mb-2">Soyadı</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Soyadı" name="soyad" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-semibold mb-2">E-posta</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="email" class="form-control form-control-solid" placeholder="E-posta" name="email" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-semibold mb-2">Telefon</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Telefon" name="telefon" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-semibold mb-2">Görevi</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" placeholder="Görevi" name="pozisyon" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Actions-->
                        <div class="text-center pt-15">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Kapat</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Kaydet</span>
                                <span class="indicator-progress">Lütfen bekleyin...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal - Personel Ekle-->
<script>
    var KTModalPersonelEkle = function () {
        // Elements
        var modal;
        var modalEl;
        var form;
        var submitButton;
        var nextButton;
        var validator;

        // Private functions
        var initForm = function () {
            // Form validation
            validator = FormValidation.formValidation(
                form,
                {
                    fields: {
                        'ad': {
                            validators: {
                                notEmpty: {
                                    message: 'Adı gereklidir.'
                                }
                            }
                        },
                        'soyad': {
                            validators: {
                                notEmpty: {
                                    message: 'Soyadı gereklidir.'
                                }
                            }
                        },
                        'email': {
                            validators: {
                                notEmpty: {
                                    message: 'E-posta gereklidir.'
                                },
                                emailAddress: {
                                    message: 'Geçerli bir e-posta adresi giriniz.'
                                }
                            }
                        },
                        'telefon': {
                            validators: {
                                notEmpty: {
                                    message: 'Telefon gereklidir.'
                                }
                            }
                        },
                        'pozisyon': {
                            validators: {
                                notEmpty: {
                                    message: 'Görev gereklidir.'
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: ''
                        })
                    }
                }
            );

            // Handle form submit
            submitButton.addEventListener('click', function (e) {
                e.preventDefault();

                if (validator) {
                    validator.validate().then(function (status) {
                        if (status == 'Valid') {
                            form.submit();
                        }
                    });
                }
            });

            // Handle form next button
            nextButton.addEventListener('click', function (e) {
                e.preventDefault();

                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        stepper.goNext();
                    }
                });
            });
        }

        return {
            // Public functions
            init: function () {
                modalEl = document.querySelector('#kt_modal_personel_ekle');
                if (modalEl) {
                    modal = new bootstrap.Modal(modalEl);
                }

                form = document.querySelector('#kt_modal_personel_ekle_form');
                submitButton = form.querySelector('[type="submit"]');
                nextButton = form.querySelector('[data-kt-element="type-next"]');

                initForm();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function () {
        KTModalPersonelEkle.init();
    });


</script>

<?php
require_once 'app/Views/layouts/footer.php';