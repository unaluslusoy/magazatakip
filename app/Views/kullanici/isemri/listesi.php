<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>
    <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
            <div class="card mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">
                            <?php 
                            switch($seciliDurum) {
                                case 'Yeni':
                                    echo 'Başlamayan İş Emirleri';
                                    break;
                                case 'Devam Ediyor':
                                    echo 'Devam Eden İş Emirleri';
                                    break;
                                case 'Tamamlandı':
                                    echo 'Tamamlanan İş Emirleri';
                                    break;
                                default:
                                    echo 'Tüm İş Emirleri';
                            }
                            ?>
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Mevcut iş emirlerinizin listesi</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="/isemri/olustur" class="btn btn-sm btn-light-primary me-2">
                            <i class="ki-outline ki-plus fs-2"></i>Yeni İş Emri
                        </a>
                        <button class="btn btn-sm btn-light-info" data-bs-toggle="collapse" data-bs-target="#filtre-alani">
                            <i class="ki-outline ki-filter fs-2"></i>Filtrele
                        </button>
                    </div>
                </div>

                <div id="filtre-alani" class="collapse <?= (!empty($seciliDurum) || !empty($seciliDerece) || !empty($tarih_baslangic) || !empty($tarih_bitis)) ? 'show' : '' ?>">
                    <div class="card-body">
                        <form method="get" action="/isemri/listesi">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Durum</label>
                                    <select name="durum" class="form-select">
                                        <option value="">Tüm Durumlar</option>
                                        <option value="Yeni" <?= $seciliDurum == 'Yeni' ? 'selected' : '' ?>>Başlamayan</option>
                                        <option value="Devam Ediyor" <?= $seciliDurum == 'Devam Ediyor' ? 'selected' : '' ?>>Devam Eden</option>
                                        <option value="Tamamlandı" <?= $seciliDurum == 'Tamamlandı' ? 'selected' : '' ?>>Tamamlanan</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Derece</label>
                                    <select name="derece" class="form-select">
                                        <option value="">Tüm Dereceler</option>
                                        <?php foreach($dereceler as $derece): ?>
                                            <option value="<?= $derece ?>" <?= $seciliDerece == $derece ? 'selected' : '' ?>><?= $derece ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Başlangıç Tarihi</label>
                                    <input type="date" name="tarih_baslangic" class="form-control" value="<?= $tarih_baslangic ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bitiş Tarihi</label>
                                    <input type="date" name="tarih_bitis" class="form-control" value="<?= $tarih_bitis ?>">
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ki-outline ki-filter fs-2 me-2"></i>Filtrele
                                    </button>
                                    <a href="/isemri/listesi" class="btn btn-light">
                                        <i class="ki-outline ki-refresh fs-2 me-2"></i>Temizle
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-325px rounded-start">Başlık</th>
                                    <th class="min-w-125px">Mağaza</th>
                                    <th class="min-w-125px">Derece</th>
                                    <th class="min-w-150px">Durum</th>
                                    <th class="min-w-150px">Oluşturma Tarihi</th>
                                    <th class="min-w-150px">Bitiş Tarihi</th>
                                    <th class="min-w-150px text-end rounded-end">Aksiyonlar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($isEmirleri)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="alert alert-info">
                                                <?php 
                                                if ($seciliDurum || $seciliDerece || $tarih_baslangic || $tarih_bitis) {
                                                    echo "Seçilen kriterlere uygun iş emri bulunmamaktadır.";
                                                } else {
                                                    echo "Henüz hiç iş emri oluşturulmamış.";
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($isEmirleri as $isEmri): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6"><?= $isEmri['baslik'] ?></a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-900 fw-bold d-block mb-1 fs-6"><?= $isEmri['magaza'] ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-900 fw-bold d-block mb-1 fs-6"><?= $isEmri['derece'] ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $durum_renkleri = [
                                                    'Yeni' => 'badge-light-info',
                                                    'Devam Ediyor' => 'badge-light-primary',
                                                    'Tamamlandı' => 'badge-light-success'
                                                ];
                                                $durum_sinifi = $durum_renkleri[$isEmri['durum']] ?? 'badge-light-secondary';
                                                ?>
                                                <span class="badge <?= $durum_sinifi ?> fs-7 fw-bold"><?= $isEmri['durum'] ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-900 fw-bold d-block mb-1 fs-6"><?= $isEmri['tarih'] ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-900 fw-bold d-block mb-1 fs-6"><?= $isEmri['bitis_tarihi'] ?? '-' ?></span>
                                            </td>
                                    <td>
                                        <?php 
                                        $dosyalar = json_decode($isEmri["dosyalar_json"] ?? "[]", true);
                                        if (!empty($dosyalar)): ?>
                                            <a href="#" class="btn btn-sm btn-icon btn-light-primary" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#dosya_onizleme_modal_<?= $isEmri["id"] ?>">
                                                <i class="ki-outline ki-file-up fs-2"></i>
                                            </a>

                                            <!-- Dosya Önizleme Modal -->
                                            <div class="modal fade" tabindex="-1" id="dosya_onizleme_modal_<?= $isEmri["id"] ?>">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h3 class="modal-title">Dosya Listesi</h3>
                                                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                                                                <i class="ki-outline ki-cross fs-2x"></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <?php foreach ($dosyalar as $dosya): ?>
                                                                    <div class="col-md-3 mb-3">
                                                                        <div class="card">
                                                                            <?php 
                                                                            $uzanti = strtolower(pathinfo($dosya["dosya_adi"], PATHINFO_EXTENSION));
                                                                            $resimUzantilari = ["jpg", "jpeg", "png", "gif", "webp"];
                                                                            ?>
                                                                            <?php if (in_array($uzanti, $resimUzantilari)): ?>
                                                                                <img src="/public/uploads/isemri/<?= htmlspecialchars($dosya["dosya_yolu"]) ?>" 
                                                                                     class="card-img-top" 
                                                                                     alt="<?= htmlspecialchars($dosya["dosya_adi"]) ?>">
                                                                            <?php else: ?>
                                                                                <div class="text-center p-3">
                                                                                    <i class="ki-outline ki-document fs-2x text-muted"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            <div class="card-body p-2">
                                                                                <small class="text-muted"><?= htmlspecialchars($dosya["dosya_adi"]) ?></small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                            <td class="text-end">
                                                <a href="/isemri/duzenle/<?= $isEmri['id'] ?>" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                    <i class="ki-outline ki-pencil fs-2"></i>
                                                </a>
                                                <a href="/isemri/sil/<?= $isEmri['id'] ?>" onclick="return confirm('Bu iş emrini silmek istediğinizden emin misiniz?');" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                                                    <i class="ki-outline ki-trash fs-2"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
require_once 'app/Views/kullanici/layout/footer.php';
?>

