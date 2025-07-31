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
                
                <!--begin::Profile Header Card-->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <!--begin::Details-->
                        <div class="d-flex flex-wrap flex-sm-nowrap">
                            <!--begin::Avatar-->
                            <div class="me-7 mb-4">
                                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                    <?php 
                                    // Ad soyaddan baş harfleri al
                                    $name_parts = explode(' ', trim($kullanici['ad']));
                                    $first_initial = !empty($name_parts[0]) ? strtoupper(substr($name_parts[0], 0, 1)) : '';
                                    $last_initial = !empty($name_parts[1]) ? strtoupper(substr($name_parts[1], 0, 1)) : '';
                                    $initials = $first_initial . $last_initial;
                                    ?>
                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-1"><?= $initials ?></div>
                                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                                </div>
                            </div>
                            <!--end::Avatar-->
                            
                            <!--begin::Info-->
                            <div class="flex-grow-1">
                                <!--begin::Title-->
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <!--begin::Name-->
                                        <div class="d-flex align-items-center mb-2">
                                            <h1 class="text-gray-900 fw-bold fs-2 me-3"><?= htmlspecialchars($kullanici['ad']) ?></h1>
                                            <span class="badge badge-<?= $kullanici['yonetici'] ? 'success' : 'primary' ?> fs-7 fw-bold">
                                                <?= $kullanici['yonetici'] ? 'Yönetici' : 'Mağaza Personeli' ?>
                                            </span>
                                        </div>
                                        <!--end::Name-->
                                        
                                        <!--begin::Quick Info-->
                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <span class="d-flex align-items-center text-gray-600 me-5 mb-2">
                                                <i class="ki-outline ki-geolocation fs-4 me-1"></i>
                                                <?= htmlspecialchars($kullanici['magaza_isim'] ?? 'Mağaza Atanmamış') ?>
                                            </span>
                                            <span class="d-flex align-items-center text-gray-600 mb-2">
                                                <i class="ki-outline ki-sms fs-4 me-1"></i>
                                                <?= htmlspecialchars($kullanici['email']) ?>
                                            </span>
                                        </div>
                                        <!--end::Quick Info-->
                                    </div>
                                    
                                    <!--begin::Actions-->

                                    <!--end::Actions-->
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Info-->
                        </div>
                        <!--end::Details-->
                        
                        <!--begin::Separator-->
                        <div class="separator my-6"></div>
                        <!--end::Separator-->
                        
                        <!--begin::Nav-->
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#profil_tab" role="tab" aria-controls="profil_tab" aria-selected="true">
                                    <i class="ki-outline ki-profile-user fs-4 me-1"></i>
                                    Profil Bilgileri
                                </a>
                            </li>
                            <?php if($personel): ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#personel_tab" role="tab" aria-controls="personel_tab" aria-selected="false">
                                    <i class="ki-outline ki-badge fs-4 me-1"></i>
                                    Personel Bilgileri
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <!--end::Nav-->
                    </div>
                </div>
                <!--end::Profile Header Card-->
                <!--begin::Tab Content-->
                <div class="tab-content" id="myTabContent">
                    <!--begin::Tab Pane - Profil-->
                    <div class="tab-pane fade show active" id="profil_tab" role="tabpanel" aria-labelledby="profil_tab-tab">
                        <!--begin::Details Content-->
                        <div class="row gy-5 g-xl-8">
                    <!--begin::Personal Info Card-->
                    <div class="col-xl-6">
                        <div class="card card-xl-stretch mb-5 mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">
                                        <i class="ki-outline ki-profile-user fs-2 text-primary me-2"></i>
                                        Kullanıcı Bilgiler
                                    </span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Hesap bilgileri</span>
                                </h3>
                                 <div class="card-toolbar">
                                   <!-- <a href="/profil/guncelle" class="btn btn-sm btn-light-primary d-flex align-items-center">
                                         <i class="ki-outline ki-pencil fs-4 me-2"></i>
                                         Düzenle
                                     </a>--> 
                                 </div>
                            </div>
                            <!--end::Header-->
                            
                            <!--begin::Body-->
                            <div class="card-body py-3">
                                <!--begin::Table container-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table table-row-borderless table-row-gray-100 align-middle gs-0 gy-3">
                                        <tbody>
                                            <tr>
                                                <td class="w-150px">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-user fs-2 text-gray-600 me-2"></i>
                                                        <span class="text-muted fw-semibold">Ad Soyad</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-gray-900 fw-bold fs-6"><?= htmlspecialchars($kullanici['ad']) ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-sms fs-2 text-gray-600 me-2"></i>
                                                        <span class="text-muted fw-semibold">E-posta</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-gray-900 fw-bold fs-6"><?= htmlspecialchars($kullanici['email']) ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-profile-circle fs-2 text-gray-600 me-2"></i>
                                                        <span class="text-muted fw-semibold">Kullanıcı ID</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-gray-900 fw-bold fs-6">#<?= htmlspecialchars($kullanici['id']) ?></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->
                            </div>
                            <!--end::Body-->
                        </div>
                    </div>
                    <!--end::Personal Info Card-->
                    
                    <!--begin::Work Info Card-->
                    <div class="col-xl-6">
                        <div class="card card-xl-stretch mb-5 mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">
                                        <i class="ki-outline ki-office-bag fs-2 text-success me-2"></i>
                                        Atanan Şube Bilgisi
                                    </span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Görev ve çalışma alanı</span>
                                </h3>
                            </div>
                            <!--end::Header-->
                            
                            <!--begin::Body-->
                            <div class="card-body py-3">
                                <!--begin::Table container-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table table-row-borderless table-row-gray-100 align-middle gs-0 gy-3">
                                        <tbody>
                                            <tr>
                                                <td class="w-150px">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-crown fs-2 text-gray-600 me-2"></i>
                                                        <span class="text-muted fw-semibold">Ünvan</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $kullanici['yonetici'] ? 'success' : 'primary' ?> fs-7">
                                                        <?= $kullanici['yonetici'] ? 'Yönetici' : 'Mağaza Personeli' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-geolocation fs-2 text-gray-600 me-2"></i>
                                                        <span class="text-muted fw-semibold">Çalıştığı Mağaza</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-gray-900 fw-bold fs-6"><?= htmlspecialchars($kullanici['magaza_isim'] ?? 'Atanmamış') ?></span>
                                                    <?php if($kullanici['magaza_id']): ?>
                                                        <span class="text-muted fs-7">(ID: <?= $kullanici['magaza_id'] ?>)</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-home fs-2 text-gray-600 me-2"></i>
                                                        <span class="text-muted fw-semibold">Şirket</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-gray-900 fw-bold fs-6">Palmiye Gurme Kuruyemiş</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->
                            </div>
                            <!--end::Body-->
                    </div>
                </div>
                    <!--end::Work Info Card-->
                </div>
                <!--end::Details Content-->
                
                <!--begin::Contact Info Card-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card Header-->
                    <div class="card-header border-0 cursor-pointer">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">
                                <i class="ki-outline ki-phone fs-2 text-warning me-2"></i>
                                İletişim Bilgileri
                            </h3>
                        </div>
                    </div>
                    <!--end::Card Header-->
                    
                    <!--begin::Card Body-->
                    <div class="card-body border-top p-9">
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6 text-muted">
                                <i class="ki-outline ki-phone fs-4 text-primary me-2"></i>
                                Şirket Telefonu
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <span class="fw-bold fs-6">0850 532 0756</span>
                                <span class="text-muted fs-7 ms-2">(Merkez Hat)</span>
                            </div>
                            <!--end::Col-->
                        </div>
                        
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6 text-muted">
                                <i class="ki-outline ki-sms fs-4 text-primary me-2"></i>
                                E-posta Adresi
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <a href="mailto:<?= htmlspecialchars($kullanici['email']) ?>" class="fw-bold fs-6 text-hover-primary">
                                    <?= htmlspecialchars($kullanici['email']) ?>
                                </a>
                            </div>
                            <!--end::Col-->
                        </div>
                        
                        <div class="row">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6 text-muted">
                                <i class="ki-outline ki-global fs-4 text-primary me-2"></i>
                                Sistem Durumu
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <div class="d-flex align-items-center">
                                    <span class="bullet bullet-dot bg-success me-2"></span>
                                    <span class="fw-semibold text-success">Aktif</span>
                                    <span class="text-muted fs-7 ms-2">- Sisteme erişim mevcut</span>
                                </div>
                            </div>
                            <!--end::Col-->
                        </div>
                    </div>
                    <!--end::Card Body-->
                </div>
                <!--end::Contact Info Card-->
                        </div>
                        <!--end::Details Content-->
                    </div>
                    <!--end::Tab Pane - Profil-->
                    
                    <?php if($personel): ?>
                    <!--begin::Tab Pane - Personel-->
                    <div class="tab-pane fade" id="personel_tab" role="tabpanel" aria-labelledby="personel_tab-tab">
                        <!--begin::Personel Employee Badge/ID Card-->
                        <div class="card mb-5 mb-xl-10 border-2 border-primary">
                            <!--begin::Card Header-->
                            <div class="card-header border-0 bg-light-primary">
                                <div class="card-title m-0 w-100">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <h2 class="fw-bold m-0 text-primary">
                                                <i class="ki-outline ki-badge fs-1 text-primary me-3"></i>
                                                PERSONEL KÜNYESİ
                                            </h2>
                                            <span class="text-muted fs-6">Çalışan Kimlik Kartı & İş Bilgileri</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary fs-7">Personel No:</div>
                                            <div class="fw-bold fs-3 text-primary">
                                                <?php if(!empty($personel['calisan_no'])): ?>
                                                    #<?= htmlspecialchars($personel['calisan_no']) ?>
                                                <?php else: ?>
                                                    #<?= str_pad($personel['id'] ?? '0', 4, '0', STR_PAD_LEFT) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted fs-8">
                                                Durum: 
                                                <span class="badge badge-<?= ($personel['durum'] ?? 'aktif') == 'aktif' ? 'success' : (($personel['durum'] ?? 'aktif') == 'pasif' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($personel['durum'] ?? 'Aktif') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card Header-->
                        </div>

                        <!--begin::Employee Details Grid-->
                        <div class="row gy-5 g-xl-8">
                            <!--begin::Personal Identity Card-->
                            <div class="col-xl-4">
                                <div class="card card-xl-stretch mb-5 mb-xl-8 border border-success">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-5 bg-light-success">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bold fs-3 mb-1 text-success">
                                                <i class="ki-outline ki-user-square fs-2 text-success me-2"></i>
                                                Kimlik Bilgileri
                                            </span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">Kişisel Kimlik Detayları</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    
                                    <!--begin::Body-->
                                    <div class="card-body py-3">
                                        <form class="form">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-user fs-4 text-success me-2"></i>Ad
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['ad'] ?? '') ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-user fs-4 text-success me-2"></i>Soyad
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['soyad'] ?? '') ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-profile-circle fs-4 text-success me-2"></i>TC Kimlik No
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['tc_kimlik_no'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-calendar fs-4 text-success me-2"></i>Doğum Tarihi
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="date" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['dogum_tarihi'] ?? '') ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-geolocation fs-4 text-success me-2"></i>Doğum Yeri
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['dogum_yeri'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-user-tick fs-4 text-success me-2"></i>Cinsiyet
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="erkek" <?= ($personel['cinsiyet'] ?? '') == 'erkek' ? 'selected' : '' ?>>Erkek</option>
                                                        <option value="kadın" <?= ($personel['cinsiyet'] ?? '') == 'kadın' ? 'selected' : '' ?>>Kadın</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-heart fs-4 text-success me-2"></i>Kan Grubu
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="A Rh+" <?= ($personel['kan_grubu'] ?? '') == 'A Rh+' ? 'selected' : '' ?>>A Rh+</option>
                                                        <option value="A Rh-" <?= ($personel['kan_grubu'] ?? '') == 'A Rh-' ? 'selected' : '' ?>>A Rh-</option>
                                                        <option value="B Rh+" <?= ($personel['kan_grubu'] ?? '') == 'B Rh+' ? 'selected' : '' ?>>B Rh+</option>
                                                        <option value="B Rh-" <?= ($personel['kan_grubu'] ?? '') == 'B Rh-' ? 'selected' : '' ?>>B Rh-</option>
                                                        <option value="AB Rh+" <?= ($personel['kan_grubu'] ?? '') == 'AB Rh+' ? 'selected' : '' ?>>AB Rh+</option>
                                                        <option value="AB Rh-" <?= ($personel['kan_grubu'] ?? '') == 'AB Rh-' ? 'selected' : '' ?>>AB Rh-</option>
                                                        <option value="0 Rh+" <?= ($personel['kan_grubu'] ?? '') == '0 Rh+' ? 'selected' : '' ?>>0 Rh+</option>
                                                        <option value="0 Rh-" <?= ($personel['kan_grubu'] ?? '') == '0 Rh-' ? 'selected' : '' ?>>0 Rh-</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-0">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-flag fs-4 text-success me-2"></i>Uyruk
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['uyruk'] ?? 'TC') ?>" />
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            <!--end::Personal Identity Card-->
                            
                            <!--begin::Job Information Card-->
                            <div class="col-xl-4">
                                <div class="card card-xl-stretch mb-5 mb-xl-8 border border-primary">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-5 bg-light-primary">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bold fs-3 mb-1 text-primary">
                                                <i class="ki-outline ki-office-bag fs-2 text-primary me-2"></i>
                                                Görev Bilgileri
                                            </span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">Pozisyon ve Departman</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    
                                    <!--begin::Body-->
                                    <div class="card-body py-3">
                                        <form class="form">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-crown fs-4 text-primary me-2"></i>Pozisyon
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['pozisyon'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-home-2 fs-4 text-primary me-2"></i>Departman
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['departman'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-calendar-add fs-4 text-primary me-2"></i>İşe Başlama
                                                </label>
                            <div class="col-lg-8">
                                                    <input type="date" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['ise_baslama_tarihi'] ?? '') ?>" />
                                                    <?php if($personel['ise_baslama_tarihi']): 
                                                        $start_date = new DateTime($personel['ise_baslama_tarihi']);
                                                        $current_date = new DateTime();
                                                        $interval = $start_date->diff($current_date);
                                                        echo '<div class="form-text text-primary fw-bold">Kıdem: ' . $interval->y . ' yıl, ' . $interval->m . ' ay</div>';
                                                    endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-people fs-4 text-primary me-2"></i>Bağlı Yönetici
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['raporlama_yoneticisi'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                            </div>
                        </div>
                                            
                                            <div class="row mb-0">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-time fs-4 text-primary me-2"></i>Çalışma Saatleri
                                                </label>
                            <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['calisma_saatleri'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            <!--end::Job Information Card-->
                            
                            <!--begin::Employment Details Card-->
                            <div class="col-xl-4">
                                <div class="card card-xl-stretch mb-5 mb-xl-8 border border-warning">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-5 bg-light-warning">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bold fs-3 mb-1 text-warning">
                                                <i class="ki-outline ki-document fs-2 text-warning me-2"></i>
                                                İstihdam Bilgileri
                                            </span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">Sözleşme ve Çalışma Şekli</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    
                                    <!--begin::Body-->
                                    <div class="card-body py-3">
                                        <form class="form">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-time fs-4 text-warning me-2"></i>Çalışma Şekli
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="tam_zamanli" <?= ($personel['calisma_sekli'] ?? '') == 'tam_zamanli' ? 'selected' : '' ?>>Tam Zamanlı</option>
                                                        <option value="yari_zamanli" <?= ($personel['calisma_sekli'] ?? '') == 'yari_zamanli' ? 'selected' : '' ?>>Yarı Zamanlı</option>
                                                        <option value="proje_bazli" <?= ($personel['calisma_sekli'] ?? '') == 'proje_bazli' ? 'selected' : '' ?>>Proje Bazlı</option>
                                                        <option value="stajyer" <?= ($personel['calisma_sekli'] ?? '') == 'stajyer' ? 'selected' : '' ?>>Stajyer</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-calendar-2 fs-4 text-warning me-2"></i>Sözleşme Tarihi
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="date" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['calisma_sozlesmesi_tarihi'] ?? '') ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-timer fs-4 text-warning me-2"></i>Sözleşme Süresi
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="belirsiz" <?= ($personel['sozlesme_suresi'] ?? '') == 'belirsiz' ? 'selected' : '' ?>>Belirsiz Süreli</option>
                                                        <option value="1_yil" <?= ($personel['sozlesme_suresi'] ?? '') == '1_yil' ? 'selected' : '' ?>>1 Yıl</option>
                                                        <option value="2_yil" <?= ($personel['sozlesme_suresi'] ?? '') == '2_yil' ? 'selected' : '' ?>>2 Yıl</option>
                                                        <option value="6_ay" <?= ($personel['sozlesme_suresi'] ?? '') == '6_ay' ? 'selected' : '' ?>>6 Ay</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-security-user fs-4 text-warning me-2"></i>SGK Numarası
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['sgk_no'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-calculator fs-4 text-warning me-2"></i>Vergi Numarası
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['vergi_no'] ?? '') ?>" 
                                                           placeholder="Belirtilmemiş" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-0">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-bank fs-4 text-warning me-2"></i>IBAN Numarası
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['iban_no'] ?? '') ?>" 
                                                           placeholder="TR00 0000 0000 0000 0000 0000 00"
                                                           maxlength="32" />
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            <!--end::Employment Details Card-->
                        </div>
                        <!--end::Employee Details Grid-->
                        
                                                <!--begin::Work Related Information-->
                        <div class="row gy-5 g-xl-8">
                            <!--begin::Contact & Location Card-->
                            <div class="col-xl-6">
                                <div class="card card-xl-stretch mb-5 mb-xl-8 border border-info">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-5 bg-light-info">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bold fs-3 mb-1 text-info">
                                                <i class="ki-outline ki-map fs-2 text-info me-2"></i>
                                                İletişim & Konum
                                            </span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">İletişim Bilgileri & Adres</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    
                                    <!--begin::Body-->
                                    <div class="card-body py-3">
                                        <form class="form">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-phone fs-4 text-info me-2"></i>İş Telefonu
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="tel" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['telefon'] ?? '') ?>" 
                                                           placeholder="0 (___) ___ __ __" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-phone fs-4 text-info me-2"></i>Cep Telefonu
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="tel" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['cep_telefonu'] ?? '') ?>" 
                                                           placeholder="0 (___) ___ __ __" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-sms fs-4 text-info me-2"></i>E-posta
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="email" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['eposta'] ?? '') ?>" 
                                                           placeholder="ornek@email.com" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-geolocation fs-4 text-info me-2"></i>Ev Adresi
                                                </label>
                            <div class="col-lg-8">
                                                    <textarea class="form-control form-control-lg form-control-solid" readonly 
                                                              rows="3" placeholder="Adres bilgisi giriniz..."><?= htmlspecialchars($personel['ev_adresi'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="separator my-6"></div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6 text-danger">
                                                    <i class="ki-outline ki-shield-tick fs-4 text-danger me-2"></i>Acil Durum Kişisi
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="text" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['acil_durum_kisi_adi'] ?? '') ?>" 
                                                           placeholder="Ad Soyad" />
                            </div>
                        </div>
                                            
                                            <div class="row mb-0">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6 text-danger">
                                                    <i class="ki-outline ki-phone fs-4 text-danger me-2"></i>Acil Durum Tel.
                                                </label>
                            <div class="col-lg-8">
                                                    <input type="tel" class="form-control form-control-lg form-control-solid" readonly 
                                                           value="<?= htmlspecialchars($personel['acil_durum_kisi_telefon'] ?? '') ?>" 
                                                           placeholder="0 (___) ___ __ __" />
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            <!--end::Contact & Location Card-->
                            
                            <!--begin::HR & Benefits Card-->
                            <div class="col-xl-6">
                                <div class="card card-xl-stretch mb-5 mb-xl-8 border border-danger">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-5 bg-light-danger">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label fw-bold fs-3 mb-1 text-danger">
                                                <i class="ki-outline ki-calendar-tick fs-2 text-danger me-2"></i>
                                                İK & Haklar
                                            </span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">İzin ve Puantaj Sistemi</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    
                                    <!--begin::Body-->
                                    <div class="card-body py-3">
                                        <form class="form">
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-calendar-search fs-4 text-danger me-2"></i>Yıllık İzin
                                                </label>
                                                <div class="col-lg-8">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control form-control-lg form-control-solid" readonly 
                                                               value="<?= htmlspecialchars($personel['izin_gunleri'] ?? '0') ?>" 
                                                               min="0" max="365" />
                                                        <span class="input-group-text">Gün</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-time fs-4 text-danger me-2"></i>Puantaj Sistemi
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="manuel" <?= ($personel['puantaj_sistemi'] ?? '') == 'manuel' ? 'selected' : '' ?>>Manuel</option>
                                                        <option value="kartli" <?= ($personel['puantaj_sistemi'] ?? '') == 'kartli' ? 'selected' : '' ?>>Kartlı Sistem</option>
                                                        <option value="parmak_izi" <?= ($personel['puantaj_sistemi'] ?? '') == 'parmak_izi' ? 'selected' : '' ?>>Parmak İzi</option>
                                                        <option value="mobil" <?= ($personel['puantaj_sistemi'] ?? '') == 'mobil' ? 'selected' : '' ?>>Mobil Uygulama</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-dollar fs-4 text-danger me-2"></i>Ücret Bilgisi
                                                </label>
                                                <div class="col-lg-8">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control form-control-lg form-control-solid" readonly 
                                                               value="<?= htmlspecialchars($personel['ucret'] ?? '') ?>" 
                                                               step="0.01" min="0" placeholder="0.00" />
                                                        <span class="input-group-text">₺</span>
                                                    </div>
                                                    <div class="form-text">
                                                        Ödeme Türü: 
                                                        <select class="form-select form-select-sm form-select-solid mt-2" disabled>
                                                            <option value="">Seçiniz</option>
                                                            <option value="aylik" <?= ($personel['maas_tipi'] ?? '') == 'aylik' ? 'selected' : '' ?>>Aylık</option>
                                                            <option value="gunluk" <?= ($personel['maas_tipi'] ?? '') == 'gunluk' ? 'selected' : '' ?>>Günlük</option>
                                                            <option value="saatlik" <?= ($personel['maas_tipi'] ?? '') == 'saatlik' ? 'selected' : '' ?>>Saatlik</option>
                                                            <option value="prim" <?= ($personel['maas_tipi'] ?? '') == 'prim' ? 'selected' : '' ?>>Prim</option>
                                                            <option value="komisyon" <?= ($personel['maas_tipi'] ?? '') == 'komisyon' ? 'selected' : '' ?>>Komisyon</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-6">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-heart fs-4 text-danger me-2"></i>Medeni Durum
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="bekar" <?= ($personel['medeni_durum'] ?? '') == 'bekar' ? 'selected' : '' ?>>Bekar</option>
                                                        <option value="evli" <?= ($personel['medeni_durum'] ?? '') == 'evli' ? 'selected' : '' ?>>Evli</option>
                                                        <option value="bosanmis" <?= ($personel['medeni_durum'] ?? '') == 'bosanmis' ? 'selected' : '' ?>>Boşanmış</option>
                                                        <option value="dul" <?= ($personel['medeni_durum'] ?? '') == 'dul' ? 'selected' : '' ?>>Dul</option>
                                                    </select>
                                                    <?php if(!empty($personel['cocuk_sayisi']) && $personel['cocuk_sayisi'] > 0): ?>
                                                        <div class="form-text text-primary fw-bold mt-2">
                                                            Çocuk Sayısı: <?= $personel['cocuk_sayisi'] ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-0">
                                                <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                                    <i class="ki-outline ki-car fs-4 text-danger me-2"></i>Ehliyet Sınıfı
                                                </label>
                                                <div class="col-lg-8">
                                                    <select class="form-select form-select-lg form-select-solid" disabled>
                                                        <option value="">Seçiniz</option>
                                                        <option value="A1" <?= ($personel['ehliyet_sinifi'] ?? '') == 'A1' ? 'selected' : '' ?>>A1 - Motosiklet</option>
                                                        <option value="A2" <?= ($personel['ehliyet_sinifi'] ?? '') == 'A2' ? 'selected' : '' ?>>A2 - Motosiklet</option>
                                                        <option value="A" <?= ($personel['ehliyet_sinifi'] ?? '') == 'A' ? 'selected' : '' ?>>A - Motosiklet</option>
                                                        <option value="B" <?= ($personel['ehliyet_sinifi'] ?? '') == 'B' ? 'selected' : '' ?>>B - Otomobil</option>
                                                        <option value="C" <?= ($personel['ehliyet_sinifi'] ?? '') == 'C' ? 'selected' : '' ?>>C - Kamyon</option>
                                                        <option value="D" <?= ($personel['ehliyet_sinifi'] ?? '') == 'D' ? 'selected' : '' ?>>D - Otobüs</option>
                                                        <option value="E" <?= ($personel['ehliyet_sinifi'] ?? '') == 'E' ? 'selected' : '' ?>>E - Tır</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!--end::Body-->
                                </div>
                            </div>
                            <!--end::HR & Benefits Card-->
                        </div>
                        <!--end::Work Related Information-->
                        
                        <!--begin::Skills & Qualifications-->
                        <div class="card mb-5 mb-xl-10 border border-dark">
                            <!--begin::Card Header-->
                            <div class="card-header border-0 bg-light-dark">
                                <div class="card-title m-0">

                                <span class="card-label fw-bold fs-3 mb-1 text-danger">
                                              
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1 text-dark">
                                            <i class="ki-outline ki-award fs-2 text-dark me-2"></i>
                                            Yetkinlikler & Nitelikler
                                        </span>
                                        <span class="text-muted mt-1 fw-semibold fs-7">Eğitim, Dil ve Özel Yetenekler</span>
                                    </h3>
                                 
                                </div>
                            </div>
                            <!--end::Card Header-->
                            
                            <!--begin::Card Body-->
                            <div class="card-body border-top p-6">
                                <div class="row">
                                    <div class="col-lg-4 mb-6">
                                        <label class="form-label fw-bold text-dark mb-3">
                                            <i class="ki-outline ki-teacher fs-3 text-primary me-2"></i>
                                            Eğitim Durumu
                                        </label>
                                        <div class="bg-light-primary p-4 rounded border-start border-4 border-primary">
                                            <span class="text-gray-900 fw-bold fs-6">
                                                <?= htmlspecialchars($personel['egitim_bilgileri'] ?? 'Belirtilmemiş') ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4 mb-6">
                                        <label class="form-label fw-bold text-dark mb-3">
                                            <i class="ki-outline ki-global fs-3 text-success me-2"></i>
                                            Dil Yetkinliği
                                        </label>
                                        <div class="bg-light-success p-4 rounded border-start border-4 border-success">
                                            <span class="text-gray-900 fw-bold fs-6">
                                                <?= htmlspecialchars($personel['dil_bilgisi'] ?? 'Belirtilmemiş') ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4 mb-6">
                                        <label class="form-label fw-bold text-dark mb-3">
                                            <i class="ki-outline ki-star fs-3 text-warning me-2"></i>
                                            Özel Yetenekler
                                        </label>
                                        <div class="bg-light-warning p-4 rounded border-start border-4 border-warning">
                                            <span class="text-gray-900 fw-bold fs-6">
                                                <?= htmlspecialchars($personel['ozel_yetenekler'] ?? 'Belirtilmemiş') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card Body-->
                        </div>
                        <!--end::Skills & Qualifications-->
                        
                        <!--begin::Additional Notes Card-->
                        <?php if(!empty($personel['notlar'])): ?>
                        <div class="card mb-5 mb-xl-10 border border-secondary">
                            <!--begin::Card Header-->
                            <div class="card-header border-0 bg-light-secondary">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0 text-secondary">
                                        <i class="ki-outline ki-notepad-edit fs-2 text-secondary me-2"></i>
                                        Özel Notlar & Açıklamalar
                                    </h3>
                            </div>
                        </div>
                            <!--end::Card Header-->
                            
                            <!--begin::Card Body-->
                            <div class="card-body border-top p-6">
                                <div class="bg-light-secondary p-6 rounded border-2 border-dashed border-secondary">
                                    <div class="text-gray-900 fw-semibold fs-6 lh-lg">
                                        <?= nl2br(htmlspecialchars($personel['notlar'])) ?>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card Body-->
                        </div>
                        <?php endif; ?>
                        <!--end::Additional Notes Card-->
                    </div>
                    <!--end::Tab Pane - Personel-->
                    <?php endif; ?>
                </div>
                <!--end::Tab Content-->
            </div>
            <!--end::Content container-->

        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    </div>
    <!--End::Main-->
    </div>

<style>
/* Tab geçiş animasyonları */
.tab-pane {
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease-in-out;
    display: none;
}

.tab-pane.active {
    opacity: 1;
    transform: translateY(0);
    display: block;
}

.tab-pane.show.active {
    opacity: 1;
    transform: translateY(0);
}

/* Nav link aktif durumu */
.nav-link {
    transition: all 0.2s ease;
}

.nav-link.active {
    color: var(--bs-primary) !important;
    border-bottom-color: var(--bs-primary) !important;
}

.nav-link:hover {
    color: var(--bs-primary) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    function switchTab(targetId) {
        // Remove active classes from all tabs and panes
        tabLinks.forEach(link => {
            link.classList.remove('active');
            link.setAttribute('aria-selected', 'false');
            link.parentElement.classList.remove('active');
        });
        
        tabPanes.forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        
        // Add active classes to target tab and pane
        const targetPane = document.getElementById(targetId.substring(1)); // Remove # from href
        const targetLink = document.querySelector(`[href="${targetId}"]`);
        
        if (targetPane && targetLink) {
            targetPane.classList.add('show', 'active');
            targetLink.classList.add('active');
            targetLink.setAttribute('aria-selected', 'true');
            targetLink.parentElement.classList.add('active');
        }
    }
    
    // Add click event listeners to tab links
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            switchTab(targetId);
        });
    });
    
    // Set initial active tab
    const firstTab = document.querySelector('[href="#profil_tab"]');
    if (firstTab) {
        switchTab('#profil_tab');
    }
    
    // Debug: Log tab system initialization
    console.log('Tab sistem başlatıldı. Tab sayısı:', tabLinks.length);
    console.log('Tab panelleri:', tabPanes.length);
    
    // Alternatif: Manual tab initialization (fallback)
    if (tabLinks.length === 0) {
        console.warn('Bootstrap tabları bulunamadı, manual tab sistemi aktifleştiriliyor...');
        
        // Manual tab links
        const manualTabLinks = document.querySelectorAll('.nav-link');
        manualTabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href && href.startsWith('#')) {
                    switchTab(href);
                }
            });
        });
    }
});
</script>

<?php
require_once 'app/Views/kullanici/layout/footer.php';


