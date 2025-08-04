<?php require_once 'app/Views/kullanici/layout/header.php'; ?>
<?php require_once 'app/Views/kullanici/layout/navbar.php'; ?>

<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <div class="card-title">
                                <i class="ki-outline ki-badge fs-1 me-2"></i>
                                <h2>İş Emri Düzenle</h2>
                            </div>
                        </div>
                        <div class="card-body pt-5">
                            <?php if(isset($hata)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Hata!</strong> <?= htmlspecialchars($hata) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                            </div>
                            <?php endif; ?>

                            <form class="form fv-plugins-bootstrap5 fv-plugins-framework" method="post" action="/isemri/duzenle/<?= $isEmri['id'] ?>" enctype="multipart/form-data">
                                <div class="fv-row mb-7 fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold form-label mt-3">
                                        <span class="required">Başlık</span>
                                    </label>
                                    <input type="text" name="baslik" id="baslik" class="form-control form-control-solid" value="<?= htmlspecialchars($isEmri['baslik']) ?>" required>
                                </div>

                                <div class="fv-row mb-7">
                                    <label for="aciklama" class="form-label fs-6 fw-semibold form-label mt-3">
                                        <span class="required">Detay Mesaj</span>
                                    </label>
                                    <textarea name="aciklama" id="aciklama" class="form-control form-control-solid" required><?= htmlspecialchars($isEmri['aciklama']) ?></textarea>
                                </div>

                                <div class="fv-row mb-7">
                                    <label for="kategori" class="fs-6 fw-semibold form-label mt-3">Kategori</label>
                                    <select name="kategori" id="kategori" class="form-select form-control form-control-solid">
                                        <option value="">Kategori Seçiniz</option>
                                        <option value="Elektrik" <?= ($isEmri['kategori'] ?? '') == 'Elektrik' ? 'selected' : '' ?>>Elektrik</option>
                                        <option value="Su Tesisatı" <?= ($isEmri['kategori'] ?? '') == 'Su Tesisatı' ? 'selected' : '' ?>>Su Tesisatı</option>
                                        <option value="Klima" <?= ($isEmri['kategori'] ?? '') == 'Klima' ? 'selected' : '' ?>>Klima</option>
                                        <option value="Bilgisayar" <?= ($isEmri['kategori'] ?? '') == 'Bilgisayar' ? 'selected' : '' ?>>Bilgisayar</option>
                                        <option value="Temizlik" <?= ($isEmri['kategori'] ?? '') == 'Temizlik' ? 'selected' : '' ?>>Temizlik</option>
                                        <option value="Güvenlik" <?= ($isEmri['kategori'] ?? '') == 'Güvenlik' ? 'selected' : '' ?>>Güvenlik</option>
                                        <option value="Diğer" <?= ($isEmri['kategori'] ?? '') == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                                    </select>
                                </div>

                                <div class="row row-cols-1 row-cols-sm-2 rol-cols-md-1 row-cols-lg-2">
                                    <div class="col">
                                        <div class="fv-row mb-7 fv-plugins-icon-container">
                                            <label for="magaza_id" class="fs-6 fw-semibold form-label mt-3 required">Mağaza Listesi</label>
                                            <select name="magaza_id" id="magaza_id" class="form-select form-control form-control-solid" required>
                                                <?php foreach ($magazalar as $magaza): ?>
                                                    <option value="<?= $magaza['id'] ?>" 
                                                        <?= $magaza['id'] == $isEmri['magaza_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($magaza['ad']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fv-row mb-7 fv-plugins-icon-container">
                                            <label for="derece" class="fs-6 fw-semibold form-label mt-3">İstek Derecesi:</label>
                                            <select name="derece" id="derece" class="form-select form-control form-control-solid" required>
                                                <?php 
                                                $dereceler = ['ACİL', 'KRİTİK', 'YÜKSEK', 'ORTA', 'DÜŞÜK', 'İNCELENİYOR'];
                                                foreach ($dereceler as $derece): ?>
                                                    <option value="<?= $derece ?>" 
                                                        <?= $derece == $isEmri['derece'] ? 'selected' : '' ?>>
                                                        <?= $derece ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="form-label fs-6 fw-semibold">Mevcut Dosyalar</label>
                                    <div class="row" id="mevcut_dosyalar">
                                        <?php 
                                        $dosyalar = !empty($isEmri['dosyalar_json']) ? json_decode($isEmri['dosyalar_json'], true) : [];
                                        $dosyalar = is_array($dosyalar) ? $dosyalar : [];
                                        foreach ($dosyalar as $dosya): ?>
                                            <div class="col-md-3 mb-3">
                                                <div class="card">
                                                    <?php if(in_array(strtolower(pathinfo($dosya['dosya_adi'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                        <img src="/public/uploads/isemri/<?= htmlspecialchars($dosya['dosya_yolu']) ?>" 
                                                             class="card-img-top" 
                                                             alt="<?= htmlspecialchars($dosya['dosya_adi']) ?>">
                                                    <?php else: ?>
                                                        <div class="text-center p-3">
                                                            <i class="ki-outline ki-document fs-2x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="card-body p-2">
                                                        <small class="text-muted"><?= htmlspecialchars($dosya['dosya_adi']) ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="form-label fs-6 fw-semibold">Yeni Dosya Ekle</label>
                                    <input type="file" name="dosyalar[]" id="dosya_yukle" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="/isemri/listesi" type="reset" class="btn btn-light me-3">Kapat</a>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">Güncelle</span>
                                        <span class="indicator-progress">Lütfen bekleyiniz...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/kullanici/layout/footer.php'; ?> 