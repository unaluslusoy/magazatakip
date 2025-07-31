<?php require_once 'app/Views/kullanici/layout/header.php'; ?>
<?php require_once 'app/Views/kullanici/layout/navbar.php'; ?>

<div id="kt_app_content_container" class="app-container  container-fluid ">
    <div class="card  mb-5 mb-xl-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Yeni Fatura Talebi Oluştur</h3>
            </div>
        </div>
        <!--end::Card header-->
        <!--begin::Content-->
        <div class="card-body border-top p-9">
            <!--begin::Card body-->
            <form class="form fv-plugins-bootstrap5 fv-plugins-framework" method="post" action="/fatura_talep/duzenle/<?php echo htmlspecialchars($faturaTalep['id']); ?>">
                <div class="d-flex flex-column mb-5 fv-row fv-plugins-icon-container">
                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                        <label for="magaza_id" class="d-flex align-items-center fs-5 fw-semibold mb-2">
                            <span class="required">Mağaza</span>
                        </label>
                        <select name="magaza_id" id="magaza_id" class="form-select form-select-solid " onchange="setMagazaAd()" required>
                            <option value="">Seçim Yapınız</option>
                            <?php foreach ($magazalar as $magaza): ?>
                                <option value="<?= htmlspecialchars($magaza['id']) ?>" <?= $magaza['id'] == $faturaTalep['magaza_id'] ? 'selected' : '' ?>><?= htmlspecialchars($magaza['ad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="magaza_ad" id="magaza_ad">
                        <!--end::Input-->
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                        <label for="musteri_ad" class="required fs-5 fw-semibold mb-2">Adı Soyadı & Ticari Ünvan</label>
                        <input type="text" name="musteri_ad" id="musteri_ad" value="<?= htmlspecialchars($faturaTalep['musteri_ad']) ?>" class="form-control form-control-solid" >
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 fv-row fv-plugins-icon-container">

                        <label for="musteri_vergi_no" class="required fs-5 fw-semibold mb-2">Vergi Numarası:</label>
                        <input type="text" name="musteri_vergi_no" id="musteri_vergi_no" class="form-control form-control-solid" value="<?= htmlspecialchars($faturaTalep['musteri_vergi_no']) ?>" >
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label for="musteri_vergi_dairesi" class="required fs-5 fw-semibold mb-2">Vergi Dairesi:</label>
                        <input type="text" name="musteri_vergi_dairesi" id="musteri_vergi_dairesi" class="form-control form-control-solid" value="<?= htmlspecialchars($faturaTalep['musteri_vergi_dairesi']) ?>" >
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 fv-row fv-plugins-icon-container">

                        <label for="musteri_telefon" class="required fs-5 fw-semibold mb-2">Müşteri Telefon:</label>
                        <input type="text" name="musteri_telefon" id="musteri_telefon" class="form-control form-control-solid" value="<?= htmlspecialchars($faturaTalep['musteri_telefon']) ?>" >
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                        <label for="musteri_email" class="required fs-5 fw-semibold mb-2">Müşteri Eposta:</label>
                        <input type="text" name="musteri_email" id="musteri_email" class="form-control form-control-solid" value="<?= htmlspecialchars($faturaTalep['musteri_email']) ?>" >
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                    </div>
                </div>
                <!--begin::Input group-->
                <div class="d-flex flex-column mb-5 fv-row fv-plugins-icon-container">
                    <label for="musteri_adres" class="required fs-5 fw-semibold mb-2">Müşteri Adresi:</label>
                    <textarea name="musteri_adres" id="musteri_adres" class="form-control form-control-solid" value="<?= htmlspecialchars($faturaTalep['musteri_adres']) ?>" ><?= htmlspecialchars($faturaTalep['musteri_adres']) ?></textarea>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <div class="d-flex flex-column mb-5 fv-row fv-plugins-icon-container">
                    <label for="aciklama" class="required fs-5 fw-semibold mb-2">Fatura için açıklama</label>
                    <textarea name="aciklama" id="aciklama" class="form-control form-control-solid" value="<?= htmlspecialchars($faturaTalep['aciklama']) ?>" ><?= htmlspecialchars($faturaTalep['aciklama']) ?></textarea>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                </div>
                <!--end::Input group-->
                <div class="modal-footer flex-lg-end">
                    <!--begin::Button-->
                    <a href="/fatura_talep/listesi" id="kt_modal_new_address_cancel" class="btn btn-light me-3">
                        Geri
                    </a>
                    <button type="submit"  class="btn btn-primary">
                            <span class="indicator-label">
                                Gönder
                            </span>
                        <span class="indicator-progress">
                            Lütfen bekleyin...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
                <!--end::Card body-->
        </div>
        <!--end::Content-->
    </div>
</div>

<?php require_once 'app/Views/kullanici/layout/footer.php'; ?>
