<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>
    <div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">
        <!--begin::Main-->
        <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
            <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-fluid px-3 px-lg-6">
                <!--begin::Navbar-->
                <div class="card mb-3 mb-lg-5 mb-xl-10">
                    <div class="card-body pt-6 pt-lg-9 pb-0 px-3 px-lg-6">
                        <!--begin::Details-->
                        <div class="d-flex flex-wrap flex-sm-nowrap">
                            <!--begin: Pic-->
                            <div class="me-4 me-lg-7 mb-4">
                                <div class="symbol symbol-80px symbol-lg-100px symbol-xl-160px symbol-fixed position-relative">
                                    <img src="public/media/avatars/300-1.jpg" alt="resim">
                                    <div
                                            class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"
                                    ></div>
                                </div>
                            </div>
                            <!--end::Pic-->
                            <!--begin::Info-->
                            <div class="flex-grow-1">
                                <!--begin::Title-->
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <!--begin::User-->
                                    <div class="d-flex flex-column">
                                        <!--begin::Name-->
                                        <div class="d-flex align-items-center mb-2">
                                            <a href="#" class="text-gray-900 text-hover-primary fs-3 fs-lg-2 fw-bold me-1"
                                            ><?= $kullanici['ad']; ?></a
                                            >
                                            <a href="#">
                                                <i class="ki-outline ki-verify fs-1 text-primary"></i>
                                            </a>
                                        </div>
                                        <!--end::Name-->
                                        <!--begin::Info-->
                                        <div class="d-flex flex-wrap fw-semibold fs-7 fs-lg-6 mb-4 pe-2">
                                            <a href="#"
                                               class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2"
                                            >
                                                <i class="ki-outline ki-profile-circle fs-4 me-1"></i>Mağaza Personeli
                                            </a>
                                            <a
                                                    href="#"
                                                    class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2"
                                            >
                                                <i class="ki-outline ki-geolocation fs-4 me-1"></i><?= $kullanici['magaza_id']; ?>
                                                -<?= $kullanici['magaza_isim']; ?>
                                            </a>
                                            <a
                                                    href="#"
                                                    class="d-flex align-items-center text-gray-500 text-hover-primary mb-2"
                                            >
                                                <i class="ki-outline ki-sms fs-4"></i><?= $kullanici['email']; ?>
                                            </a>
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::User-->

                                </div>
                                <!--end::Title-->

                            </div>
                            <!--end::Info-->
                        </div>
                        <!--end::Details-->
                        <!--begin::Navs-->
                        <ul
                                class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold"
                        >
                            <!--begin::Nav item-->
                            <li class="nav-item mt-2">
                                <a
                                        class="nav-link text-active-primary ms-0 me-10 py-5 active"
                                        href="account/overview.html"
                                >Genel Bakış</a
                                >
                            </li>
                            <!--end::Nav item-->

                        </ul>
                        <!--begin::Navs-->
                    </div>
                </div>
                <!--end::Navbar-->
                <!--begin::details View-->
                <div class="card mb-3 mb-lg-5 mb-xl-10" id="kt_profile_details_view">
                    <!--begin::Card header-->
                    <div class="card-header cursor-pointer px-3 px-lg-6">
                        <!--begin::Card title-->
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0 fs-4 fs-lg-3">Profil Detayları</h3>
                        </div>
                        <!--end::Card title-->
                        <!--begin::Action-->
                        <a href="/profil/guncelle" class="btn btn-sm btn-primary align-self-center fs-7 fs-lg-base py-2 px-3"
                        >Profili Düzenle</a
                        >
                        <!--end::Action-->
                    </div>
                    <!--begin::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body p-3 p-lg-6 p-xl-9">
                        <!--begin::Row-->
                        <div class="row mb-7">
                            <!--begin::Label-->
                            <label class="col-lg-4 fw-semibold text-muted">Ad Soyad</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?= $kullanici['ad']; ?></span>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->
                        <!--begin::Input group-->
                        <div class="row mb-7">
                            <!--begin::Label-->
                            <label class="col-lg-4 fw-semibold text-muted">Şirket</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <span class="fw-semibold text-gray-800 fs-6">Palmiye Gurme Kuruyemis Aktar</span>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-7">
                            <!--begin::Label-->
                            <label class="col-lg-4 fw-semibold text-muted"
                            >İletişim Telefonu
                                <span
                                        class="ms-1"
                                        data-bs-toggle="tooltip"
                                        aria-label="Telefon numarası aktif olmalıdır"
                                        data-bs-original-title="Telefon numarası aktif olmalıdır"
                                        data-kt-initialized="1"
                                >
                <i class="ki-outline ki-information fs-7"></i>
              </span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 d-flex align-items-center">
                            <span class="fw-bold fs-6 text-gray-800 me-2">0850 532 0756</span>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-7">
                            <!--begin::Label-->
                            <label class="col-lg-4 fw-semibold text-muted">Şirket Sitesi</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8">
                                <a href="#"
                                   class="fw-semibold fs-6 text-gray-800 text-hover-primary"><?= $kullanici['email']; ?></a>

                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row mb-7">
                            <!--begin::Label-->
                            <label class="col-lg-4 fw-semibold text-muted">Görev Şubeniz</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?= $kullanici['magaza_isim']; ?></span>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::details View-->
            </div>
            <!--end::Content container-->

        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    </div>
    <!--End::Main-->
    </div>
<?php
require_once 'app/Views/kullanici/layout/footer.php';


