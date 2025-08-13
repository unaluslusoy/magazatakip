<?php require_once __DIR__ . '/../layouts/layout/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/layout/navbar.php'; ?>

<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Gider Düzenle
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/anasayfa" class="text-muted text-hover-primary">Anasayfa</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="/gider/listesi" class="text-muted text-hover-primary">Giderler</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Gider Düzenle</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h3>Gider Düzenle</h3>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <form method="post" action="/gider/duzenle/<?= $gider['id'] ?>">
                                <?= csrf_field(); ?>
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Gider Başlığı</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="baslik" class="form-control form-control-lg form-control-solid" placeholder="Gider başlığını giriniz" value="<?= htmlspecialchars($gider['baslik']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Miktar (₺)</label>
                                    <div class="col-lg-8">
                                        <input type="number" name="miktar" class="form-control form-control-lg form-control-solid" placeholder="0.00" step="0.01" min="0" value="<?= $gider['miktar'] ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tarih</label>
                                    <div class="col-lg-8">
                                        <input type="date" name="tarih" class="form-control form-control-lg form-control-solid" value="<?= $gider['tarih'] ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Kategori</label>
                                    <div class="col-lg-8">
                                        <select name="kategori" class="form-select form-select-lg form-select-solid">
                                            <option value="Genel" <?= $gider['kategori'] == 'Genel' ? 'selected' : '' ?>>Genel</option>
                                            <option value="Personel" <?= $gider['kategori'] == 'Personel' ? 'selected' : '' ?>>Personel</option>
                                            <option value="Kira" <?= $gider['kategori'] == 'Kira' ? 'selected' : '' ?>>Kira</option>
                                            <option value="Elektrik" <?= $gider['kategori'] == 'Elektrik' ? 'selected' : '' ?>>Elektrik</option>
                                            <option value="Su" <?= $gider['kategori'] == 'Su' ? 'selected' : '' ?>>Su</option>
                                            <option value="Doğalgaz" <?= $gider['kategori'] == 'Doğalgaz' ? 'selected' : '' ?>>Doğalgaz</option>
                                            <option value="İnternet" <?= $gider['kategori'] == 'İnternet' ? 'selected' : '' ?>>İnternet</option>
                                            <option value="Temizlik" <?= $gider['kategori'] == 'Temizlik' ? 'selected' : '' ?>>Temizlik</option>
                                            <option value="Bakım" <?= $gider['kategori'] == 'Bakım' ? 'selected' : '' ?>>Bakım</option>
                                            <option value="Diğer" <?= $gider['kategori'] == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Açıklama</label>
                                    <div class="col-lg-8">
                                        <textarea name="aciklama" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Gider açıklaması (opsiyonel)"><?= htmlspecialchars($gider['aciklama']) ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="/gider/listesi" class="btn btn-light me-3">İptal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">Gider Güncelle</span>
                                        <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?> 