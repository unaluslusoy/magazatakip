<?php

$title=" <h2>Yeni Kullanıcı Ekle</h2>";
$link = "Kullanıcı Tanımlama" ;
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

    <form id="kt_modal_new_target_form" class="form" method="post" action="/admin/kullanici/store">
        <!--begin::Heading-->
        <div class="mb-13 text-center">
            <!--begin::Title-->
            <h1 class="mb-3">Kullanıcı Ekleme</h1>
            <!--end::Title-->
            <!--begin::Description-->
            <div class="text-muted fw-semibold fs-5">Mağaza kullanıcılarınızı ve yönetim paneli kullanıcılarını bu ekranda tanımlayabilirsiniz.
                .</div>
            <!--end::Description-->
        </div>
        <!--end::Heading-->
        <!--end::Input group-->
        <div class="d-flex flex-column mb-8 fv-row fv-plugins-icon-container">
            <label>
                <input type="text" class="form-control form-control-solid" placeholder="Adı Soyadı"  id="ad" name="ad">
            </label>
            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
        </div>
        <!--begin::Input group-->
        <!--end::Input group-->
        <div class="d-flex flex-column mb-8 fv-row fv-plugins-icon-container">
            <label>
                <input type="text" class="form-control form-control-solid" placeholder="E-Psota" id="email" name="email">
            </label>
            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
        </div>
        <div class="d-flex flex-column mb-8 fv-row fv-plugins-icon-container">
            <label>
                <input type="password" class="form-control form-control-solid" placeholder="Şifre" id="password" name="password">
            </label>
            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
        </div>
        <!--begin::Input group-->
        <div class="row g-9 mb-8">
            <!--begin::Col-->
            <div class="col-md-4 fv-row">
                <label class="required fs-6 fw-semibold mb-2">Kullanıcı Rölü</label>
                    <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Rol Seçiniz" id="rol" name="rol">
                        <option>Secim Yapınız</option>
                        <option value="0">Mağaza Personeli</option>
                        <option value="1">Yönetici</option>
                    </select>

            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-8 fv-row">
                    <label class="required fs-6 fw-semibold mb-2">Mağaza Listesi </label>
                    <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Seçim Yap"   id="magaza_id" name="magaza_id" >
                        <option value='0'>Secim Yapınız</option>
                        <?php foreach($magazalar as $magaza): ?>
                            <option value='<?= $magaza['id']; ?>'><?= $magaza['ad']; ?></option>
                        <?php endforeach; ?>
                    </select>

            </div>
            <!--end::Col-->
        </div>


        <!--end::Input group-->


        <!--begin::Actions-->
        <div class="text-center">

            <button type="submit" id="kt_modal_new_target_submit" class="btn btn-primary">
                <span class="indicator-label">Kullanıcı Ekle</span>
                <span class="indicator-progress">Lütfen Bekleyiniz...
				<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
        <!--end::Actions-->
    </form>
<?php
require_once 'app/Views/layouts/footer.php';