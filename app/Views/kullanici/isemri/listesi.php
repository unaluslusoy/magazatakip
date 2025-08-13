<?php
require_once __DIR__ . '/../layouts/layout/header.php';
require_once __DIR__ . '/../layouts/layout/navbar.php';
?>

<!-- API Service header'da yükleniyor -->

    <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
            <div class="card mb-5 mb-xl-8 isemri-listesi-container">
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
                        <a href="/isemri/olustur" class="btn btn-sm btn-light-primary me-2 d-flex align-items-center justify-content-center">
                            <i class="ki-outline ki-plus fs-2 me-2"></i>
                            <span class="fw-bold">Yeni Talep Oluştur</span>
                        </a>
                        <button class="btn btn-sm btn-light-info" data-bs-toggle="collapse" data-bs-target="#filtre-alani">
                            <i class="ki-outline ki-filter fs-2"></i>Filtrele
                        </button>
                    </div>
                </div>

                <div id="filtre-alani" class="collapse <?= (!empty($seciliDurum) || !empty($seciliDerece) || !empty($seciliKategori) || !empty($tarih_baslangic) || !empty($tarih_bitis)) ? 'show' : '' ?>">
                    <div class="card-body">
                        <form method="get" action="/isemri/listesi">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Durum</label>
                                    <select name="durum" class="form-select">
                                        <option value="">Tüm Durumlar</option>
                                        <option value="Yeni" <?= $seciliDurum == 'Yeni' ? 'selected' : '' ?>>Başlamayan</option>
                                        <option value="Devam Ediyor" <?= $seciliDurum == 'Devam Ediyor' ? 'selected' : '' ?>>Devam Eden</option>
                                        <option value="Tamamlandı" <?= $seciliDurum == 'Tamamlandı' ? 'selected' : '' ?>>Tamamlanan</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Derece</label>
                                    <select name="derece" class="form-select">
                                        <option value="">Tüm Dereceler</option>
                                        <?php foreach($dereceler as $derece): ?>
                                            <option value="<?= $derece ?>" <?= $seciliDerece == $derece ? 'selected' : '' ?>><?= $derece ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Kategori</label>
                                    <select name="kategori" class="form-select">
                                        <option value="">Tüm Kategoriler</option>
                                        <option value="Elektrik" <?= ($seciliKategori ?? '') == 'Elektrik' ? 'selected' : '' ?>>Elektrik</option>
                                        <option value="Su Tesisatı" <?= ($seciliKategori ?? '') == 'Su Tesisatı' ? 'selected' : '' ?>>Su Tesisatı</option>
                                        <option value="Klima" <?= ($seciliKategori ?? '') == 'Klima' ? 'selected' : '' ?>>Klima</option>
                                        <option value="Bilgisayar" <?= ($seciliKategori ?? '') == 'Bilgisayar' ? 'selected' : '' ?>>Bilgisayar</option>
                                        <option value="Temizlik" <?= ($seciliKategori ?? '') == 'Temizlik' ? 'selected' : '' ?>>Temizlik</option>
                                        <option value="Güvenlik" <?= ($seciliKategori ?? '') == 'Güvenlik' ? 'selected' : '' ?>>Güvenlik</option>
                                        <option value="Diğer" <?= ($seciliKategori ?? '') == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Başlangıç Tarihi</label>
                                    <input type="date" name="tarih_baslangic" class="form-control" value="<?= $tarih_baslangic ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Bitiş Tarihi</label>
                                    <input type="date" name="tarih_bitis" class="form-control" value="<?= $tarih_bitis ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ki-outline ki-filter fs-2 me-2"></i>Filtrele
                                    </button>
                                    <a href="/isemri/listesi" class="btn btn-light">
                                        <i class="ki-outline ki-refresh fs-2"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body py-3">
                    <!-- Accordion Style List -->
                    <div class="accordion accordion-icon-toggle" id="isemri_accordion">
                                <?php if (empty($isEmirleri)): ?>
                            <div class="text-center p-5">
                                            <div class="alert alert-info">
                                    <i class="ki-outline ki-information fs-2x mb-3 d-block"></i>
                                                <?php 
                                    if ($seciliDurum || $seciliDerece || $seciliKategori || $tarih_baslangic || $tarih_bitis) {
                                                    echo "Seçilen kriterlere uygun iş emri bulunmamaktadır.";
                                                } else {
                                                    echo "Henüz hiç iş emri oluşturulmamış.";
                                                }
                                                ?>
                                            </div>
                            </div>
                                <?php else: ?>
                                                                <?php foreach ($isEmirleri as $index => $isEmri): ?>
                                <?php 
                                // Durum renkleri ve ikonlar
                                $durum_styles = [
                                    'Yeni' => ['bg' => 'bg-light-info', 'text' => 'text-info', 'icon' => 'ki-outline ki-information'],
                                    'Devam Ediyor' => ['bg' => 'bg-light-primary', 'text' => 'text-primary', 'icon' => 'ki-outline ki-time'],
                                    'Tamamlandı' => ['bg' => 'bg-light-success', 'text' => 'text-success', 'icon' => 'ki-outline ki-check-circle'],
                                    'Beklemede' => ['bg' => 'bg-light-warning', 'text' => 'text-warning', 'icon' => 'ki-outline ki-pause'],
                                    'Sorun Var' => ['bg' => 'bg-light-danger', 'text' => 'text-danger', 'icon' => 'ki-outline ki-cross-circle']
                                ];
                                $durum_style = $durum_styles[$isEmri['durum']] ?? ['bg' => 'bg-light-secondary', 'text' => 'text-secondary', 'icon' => 'ki-outline ki-information'];
                                
                                // Derece renkleri
                                $derece_styles = [
                                    'ACİL' => ['bg' => 'bg-danger', 'text' => 'text-white'],
                                    'KRİTİK' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                    'YÜKSEK' => ['bg' => 'bg-primary', 'text' => 'text-white'],
                                    'ORTA' => ['bg' => 'bg-info', 'text' => 'text-white'],
                                    'DÜŞÜK' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                    'İNCELENYOR' => ['bg' => 'bg-secondary', 'text' => 'text-white']
                                ];
                                $derece_style = $derece_styles[$isEmri['derece']] ?? ['bg' => 'bg-light', 'text' => 'text-dark'];
                                
                                $dosyalar = json_decode($isEmri["dosyalar_json"] ?? "[]", true);
                                ?>
                                
                                <div class="accordion-item border border-gray-300 mb-3 rounded">
                                    <!-- HEADER: Sadece Başlık -->
                                    <h2 class="accordion-header border-bottom bg-light" id="heading_<?= $isEmri['id'] ?>">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse_<?= $isEmri['id'] ?>" 
                                                aria-expanded="false" 
                                                aria-controls="collapse_<?= $isEmri['id'] ?>">
                                            
                                            <div class="w-100 d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 fw-bold text-gray-900">
                                                    <?= htmlspecialchars($isEmri['baslik']) ?>
                                                </h5>
                                                <span class="badge bg-light text-muted ms-2">
                                                    #<?= $isEmri['id'] ?>
                                                </span>
                                            </div>
                                        </button>
                                    </h2>
                                    
                                    <!-- CONTENT: Açıklama -->
                                    <div class="accordion-content bg-light-gray p-3 border-bottom">
                                        <div class="text-gray-700 lh-lg">
                                            <?= nl2br(htmlspecialchars($isEmri['aciklama'])) ?>
                                        </div>
                                    </div>
                                    
                                    <!-- FOOTER: Etiketler -->
                                    <div class="accordion-footer bg-white p-3 d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <!-- Derece Etiketi -->
                                            <span class="badge <?= $derece_style['bg'] ?> <?= $derece_style['text'] ?> px-3 py-2">
                                                <?= $isEmri['derece'] ?>
                                            </span>
                                            
                                            <!-- Durum Etiketi -->
                                            <span class="badge <?= $durum_style['bg'] ?> <?= $durum_style['text'] ?> px-3 py-2">
                                                <i class="<?= $durum_style['icon'] ?> fs-7 me-1"></i>
                                                <?= $isEmri['durum'] ?>
                                            </span>
                                            
                                            <!-- Kategori Etiketi -->
                                            <?php if (!empty($isEmri['kategori'])): ?>
                                                <span class="badge bg-info text-white px-3 py-2">
                                                    <i class="ki-outline ki-category fs-7 me-1"></i>
                                                    <?= $isEmri['kategori'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Tarih Etiketi -->
                                        <div class="text-end">
                                            <small class="text-muted d-block">
                                                <i class="ki-outline ki-calendar fs-7 me-1"></i>
                                                <?= date('d.m.Y H:i', strtotime($isEmri['tarih'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div id="collapse_<?= $isEmri['id'] ?>" 
                                         class="accordion-collapse collapse" 
                                         aria-labelledby="heading_<?= $isEmri['id'] ?>" 
                                         data-bs-parent="#isemri_accordion">
                                        
                                        <div class="accordion-body p-4 bg-light-gray">
                                            <div class="row">
                                                <!-- Sol Kısım: Detaylar -->
                                                <div class="col-md-8">
                                                    <!-- Tam Açıklama -->
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-2">
                                                            <i class="ki-outline ki-notepad fs-5 me-2"></i>Detaylı Açıklama
                                                        </h6>
                                                        <div class="p-3 bg-white rounded border">
                                                            <?= nl2br(htmlspecialchars($isEmri['aciklama'])) ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Yönetici Bilgileri -->
                                                    <?php if (!empty($isEmri['is_aciklamasi']) || !empty($isEmri['personel_id']) || !empty($isEmri['baslangic_tarihi'])): ?>
                                                        <div class="mb-4">
                                                            <h6 class="fw-bold mb-3">
                                                                <i class="ki-outline ki-user-edit fs-5 me-2 text-primary"></i>Yönetici Bilgileri
                                                            </h6>
                                                            
                                                            <div class="card border-primary">
                                                                <div class="card-body p-3">
                                                                    <!-- Yönetici Görüşü -->
                                                                    <?php if (!empty($isEmri['is_aciklamasi'])): ?>
                                                                        <div class="mb-3">
                                                                            <div class="d-flex align-items-start">
                                                                                <i class="ki-outline ki-message-text-2 fs-4 text-success me-3 mt-1"></i>
                                                                                <div class="flex-grow-1">
                                                                                    <small class="text-muted d-block mb-1">Yönetici Görüşü</small>
                                                                                    <div class="bg-light-success p-2 rounded text-success">
                                                                                        <?= nl2br(htmlspecialchars($isEmri['is_aciklamasi'])) ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    
                                                                    <!-- Atanan Personel -->
                                                                    <?php if (!empty($isEmri['personel_id'])): ?>
                                                                        <div class="mb-3">
                                                <div class="d-flex align-items-center">
                                                                                <i class="ki-outline ki-profile-user fs-4 text-warning me-3"></i>
                                                                                <div>
                                                                                    <small class="text-muted d-block">Atanan Kişi</small>
                                                                                    <span class="fw-bold text-warning">
                                                                                        <?php 
                                                                                        // Personel bilgisini çekmek için basit sorgu
                                                                                        $db = \core\Database::getInstance()->getConnection();
                                                                                        $stmt = $db->prepare("SELECT CONCAT(ad, ' ', soyad) as tam_ad FROM personel WHERE id = :id");
                                                                                        $stmt->execute(['id' => $isEmri['personel_id']]);
                                                                                        $personel = $stmt->fetch(\PDO::FETCH_ASSOC);
                                                                                        echo $personel ? htmlspecialchars($personel['tam_ad']) : 'Personel #' . $isEmri['personel_id'];
                                                                                        ?>
                                                                                    </span>
                                                    </div>
                                                </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    
                                                                    <!-- İş Süreç Bilgileri -->
                                                                    <div class="row">
                                                                        <?php if (!empty($isEmri['baslangic_tarihi'])): ?>
                                                                            <div class="col-6">
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="ki-outline ki-calendar-tick fs-4 text-info me-3"></i>
                                                                                    <div>
                                                                                        <small class="text-muted d-block">Başlangıç Tarihi</small>
                                                                                        <span class="fw-bold text-info">
                                                                                            <?= date('d.m.Y', strtotime($isEmri['baslangic_tarihi'])) ?>
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($isEmri['bitis_tarihi'])): ?>
                                                                            <div class="col-6">
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="ki-outline ki-calendar-8 fs-4 text-success me-3"></i>
                                                                                    <div>
                                                                                        <small class="text-muted d-block">Bitiş Tarihi</small>
                                                                                        <span class="fw-bold text-success">
                                                                                            <?= date('d.m.Y', strtotime($isEmri['bitis_tarihi'])) ?>
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    
                                                                    <!-- Tamamlanma Süresi Hesaplama -->
                                                                    <?php if (!empty($isEmri['baslangic_tarihi']) && !empty($isEmri['bitis_tarihi'])): ?>
                                                <?php 
                                                                        $baslangic = new DateTime($isEmri['baslangic_tarihi']);
                                                                        $bitis = new DateTime($isEmri['bitis_tarihi']);
                                                                        $fark = $baslangic->diff($bitis);
                                                                        ?>
                                                                        <div class="mt-3 p-2 bg-light-primary rounded">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="ki-outline ki-timer fs-4 text-primary me-3"></i>
                                                                                <div>
                                                                                    <small class="text-muted d-block">Tamamlanma Süresi</small>
                                                                                    <span class="fw-bold text-primary">
                                                                                        <?= $fark->days ?> gün 
                                                                                        <?= $fark->h ?> saat 
                                                                                        <?= $fark->i ?> dakika
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Mağaza ve Kategori Bilgileri -->
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-3">
                                                            <i class="ki-outline ki-information-5 fs-5 me-2"></i>Genel Bilgiler
                                                        </h6>
                                                        
                                                        <div class="row">
                                                            <!-- Mağaza Bilgisi -->
                                                            <div class="col-md-6 mb-3">
                                                                <div class="card border-primary h-100">
                                                                    <div class="card-body p-3">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="ki-outline ki-geolocation fs-2x text-primary me-3"></i>
                                                                            <div class="flex-grow-1">
                                                                                <small class="text-muted d-block">Mağaza</small>
                                                                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($isEmri['magaza']) ?></h6>
                                                                                <?php if (!empty($isEmri['magaza_id'])): ?>
                                                                                    <small class="text-muted">ID: <?= $isEmri['magaza_id'] ?></small>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Kategori Bilgisi -->
                                                            <div class="col-md-6 mb-3">
                                                                <div class="card border-info h-100">
                                                                    <div class="card-body p-3">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="ki-outline ki-category fs-2x text-info me-3"></i>
                                                                            <div class="flex-grow-1">
                                                                                <small class="text-muted d-block">Kategori</small>
                                                                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($isEmri['kategori'] ?? 'Belirlenmemiş') ?></h6>
                                        <?php 
                                                                                $kategori_icons = [
                                                                                    'Elektrik' => '⚡ Elektriksel Arızalar',
                                                                                    'Su Tesisatı' => '💧 Su ve Kanalizasyon',
                                                                                    'Klima' => '❄️ İklimlendirme',
                                                                                    'Bilgisayar' => '💻 IT Ekipmanları',
                                                                                    'Temizlik' => '🧽 Temizlik İşleri',
                                                                                    'Güvenlik' => '🔒 Güvenlik Sistemleri',
                                                                                    'Diğer' => '📋 Genel İşler'
                                                                                ];
                                                                                if (!empty($isEmri['kategori'])): 
                                                                                ?>
                                                                                    <small class="text-info">
                                                                                        <?= $kategori_icons[$isEmri['kategori']] ?? '📋 ' . $isEmri['kategori'] ?>
                                                                                    </small>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Detaylı İstatistikler -->
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-3">
                                                            <i class="ki-outline ki-chart-line fs-5 me-2"></i>İş Emri İstatistikleri
                                                        </h6>
                                                        
                                                        <div class="row">
                                                            <!-- Öncelik Bilgisi -->
                                                            <div class="col-md-4 mb-3">
                                                                <div class="card bg-light-warning">
                                                                    <div class="card-body p-3 text-center">
                                                                        <i class="ki-outline ki-crown fs-2x text-warning mb-2"></i>
                                                                        <h6 class="fw-bold"><?= $isEmri['derece'] ?></h6>
                                                                        <small class="text-muted">Derece</small>
                                                                        <?php if (isset($isEmri['oncelik_puani'])): ?>
                                                                            <div class="mt-2">
                                                                                <div class="progress" style="height: 6px;">
                                                                                    <div class="progress-bar bg-warning" 
                                                                                         style="width: <?= $isEmri['oncelik_puani'] ?>%"></div>
                                                            </div>
                                                                                <small class="text-muted"><?= $isEmri['oncelik_puani'] ?>/100 puan</small>
                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Durum Bilgisi -->
                                                            <div class="col-md-4 mb-3">
                                                                <div class="card <?= $durum_style['bg'] ?>">
                                                                    <div class="card-body p-3 text-center">
                                                                        <i class="<?= $durum_style['icon'] ?> fs-2x <?= $durum_style['text'] ?> mb-2"></i>
                                                                        <h6 class="fw-bold"><?= $isEmri['durum'] ?></h6>
                                                                        <small class="text-muted">Mevcut Durum</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Dosya Sayısı -->
                                                            <div class="col-md-4 mb-3">
                                                                <div class="card bg-light-success">
                                                                    <div class="card-body p-3 text-center">
                                                                        <i class="ki-outline ki-folder fs-2x text-success mb-2"></i>
                                                                        <h6 class="fw-bold"><?= count($dosyalar) ?></h6>
                                                                        <small class="text-muted">Ekli Dosya</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Tarih Bilgileri -->
                                                    <div class="row mb-4">
                                                        <div class="col-6">
                                                            <div class="d-flex align-items-center">
                                                                <i class="ki-outline ki-calendar-add fs-4 text-success me-3"></i>
                                                                <div>
                                                                    <small class="text-muted d-block">Oluşturulma</small>
                                                                    <span class="fw-bold"><?= date('d.m.Y H:i', strtotime($isEmri['tarih'])) ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                <div class="d-flex align-items-center">
                                                                <i class="ki-outline ki-calendar-tick fs-4 text-warning me-3"></i>
                                                                <div>
                                                                    <small class="text-muted d-block">Bitiş Tarihi</small>
                                                                    <span class="fw-bold"><?= $isEmri['bitis_tarihi'] ? date('d.m.Y', strtotime($isEmri['bitis_tarihi'])) : 'Belirlenmemiş' ?></span>
                                                                </div>
                                                            </div>
                                                    </div>
                                                </div>
                                                    
                                                    <!-- Dosyalar -->
                                                    <?php if (!empty($dosyalar)): ?>
                                                        <div class="mb-4">
                                                            <h6 class="fw-bold mb-3">
                                                                <i class="ki-outline ki-folder fs-5 me-2"></i>Ekli Dosyalar (<?= count($dosyalar) ?>)
                                                            </h6>
                                                            <div class="row">
                                                                <?php foreach ($dosyalar as $dosya): ?>
                                                                            <?php 
                                                                            $uzanti = strtolower(pathinfo($dosya["dosya_adi"], PATHINFO_EXTENSION));
                                                                            $resimUzantilari = ["jpg", "jpeg", "png", "gif", "webp"];
                                                                            ?>
                                                                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                                                                        <div class="card shadow-sm">
                                                                            <?php if (in_array($uzanti, $resimUzantilari)): ?>
                                                                                <img src="/public/uploads/isemri/<?= htmlspecialchars($dosya["dosya_yolu"]) ?>" 
                                                                                     class="card-img-top" 
                                                                                     alt="<?= htmlspecialchars($dosya["dosya_adi"]) ?>"
                                                                                     style="height: 100px; object-fit: cover;">
                                                                            <?php else: ?>
                                                                                <div class="text-center p-3" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                                                                                    <i class="ki-outline ki-document fs-2x text-muted"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            <div class="card-body p-2">
                                                                                <small class="text-muted d-block text-truncate" title="<?= htmlspecialchars($dosya["dosya_adi"]) ?>">
                                                                                    <?= htmlspecialchars($dosya["dosya_adi"]) ?>
                                                                                </small>
                                                                                <small class="text-muted">
                                                                                    <?= $dosya['boyut'] ? number_format($dosya['boyut'] / 1024, 1) . ' KB' : '' ?>
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    </div>
                                                
                                                <!-- Sağ Kısım: Aksiyonlar -->
                                                <div class="col-md-4">
                                                    <div class="card bg-light-primary">
                                                        <div class="card-body p-4">
                                                            <h6 class="fw-bold mb-3">
                                                                <i class="ki-outline ki-gear fs-5 me-2"></i>İşlemler
                                                            </h6>
                                                            
                                                            <div class="d-grid gap-2">
                                                                <a href="/isemri/duzenle/<?= $isEmri['id'] ?>" 
                                                                   class="btn btn-primary btn-sm">
                                                                    <i class="ki-outline ki-pencil fs-6 me-2"></i>Düzenle
                                                                </a>
                                                                
                                                           
                                                                
                                                                <!--button class="btn btn-light-danger btn-sm" 
                                                                        onclick="return confirm('Bu iş emrini silmek istediğinizden emin misiniz?') ? (window.location.href='/isemri/sil/<?= $isEmri['id'] ?>') : false;">
                                                                    <i class="ki-outline ki-trash fs-6 me-2"></i>Sil
                                                                </button-->
                                                </div>
                                                            
                                                            <!-- İstatistikler -->
                                                            <div class="mt-4 pt-3 border-top border-white">
                                                                <small class="text-muted d-block mb-1">ID: #<?= $isEmri['id'] ?></small>
                                                                <?php if (isset($isEmri['olusturulma_tarihi'])): ?>
                                                                    <small class="text-muted d-block mb-1">
                                                                        Kayıt: <?= date('d.m.Y H:i', strtotime($isEmri['olusturulma_tarihi'])) ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                                <?php if (isset($isEmri['oncelik_puani'])): ?>
                                                                    <small class="text-muted d-block">
                                                                        Öncelik Puanı: <?= $isEmri['oncelik_puani'] ?>/100
                                                                    </small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                               
                                                                <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Sayfalama Bileşeni -->
                        <?php if (isset($toplamSayfa) && $toplamSayfa > 1): ?>
                            <div class="pagination-wrapper mt-5 mb-3">
                                <nav aria-label="Sayfa navigasyonu">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <!-- Sayfa Bilgisi -->
                                        <div class="pagination-info mb-2 mb-md-0">
                                            <span class="text-muted">
                                                Toplam <strong><?= $toplamKayit ?? 0 ?></strong> kayıttan 
                                                <strong><?= ((($mevcutSayfa ?? 1) - 1) * ($sayfaBasinaKayit ?? 10)) + 1 ?></strong> - 
                                                <strong><?= min(($mevcutSayfa ?? 1) * ($sayfaBasinaKayit ?? 10), $toplamKayit ?? 0) ?></strong> 
                                                arası gösteriliyor
                                            </span>
                                        </div>
                                        
                                        <!-- Sayfa Butonları -->
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php 
                                            // Mevcut URL parametrelerini koru
                                            $currentParams = $_GET;
                                            unset($currentParams['sayfa']);
                                            $baseUrl = '/isemri/listesi?' . http_build_query($currentParams);
                                            $baseUrl .= empty($currentParams) ? 'sayfa=' : '&sayfa=';
                                            ?>
                                            
                                            <!-- İlk Sayfa -->
                                            <?php if (($mevcutSayfa ?? 1) > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= $baseUrl ?>1" title="İlk sayfa">
                                                        <i class="ki-outline ki-double-left fs-6"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= $baseUrl ?><?= ($mevcutSayfa ?? 1) - 1 ?>" title="Önceki sayfa">
                                                        <i class="ki-outline ki-left fs-6"></i>
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="ki-outline ki-double-left fs-6"></i></span>
                                                </li>
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="ki-outline ki-left fs-6"></i></span>
                                                </li>
                                <?php endif; ?>
                                            
                                            <!-- Sayfa Numaraları -->
                                            <?php 
                                            $mevcutSayfa = $mevcutSayfa ?? 1;
                                            $toplamSayfa = $toplamSayfa ?? 1;
                                            $startPage = max(1, $mevcutSayfa - 2);
                                            $endPage = min($toplamSayfa, $mevcutSayfa + 2);
                                            
                                            for ($i = $startPage; $i <= $endPage; $i++): 
                                            ?>
                                                <li class="page-item <?= $i == $mevcutSayfa ? 'active' : '' ?>">
                                                    <?php if ($i == $mevcutSayfa): ?>
                                                        <span class="page-link fw-bold"><?= $i ?></span>
                                                    <?php else: ?>
                                                        <a class="page-link" href="<?= $baseUrl ?><?= $i ?>"><?= $i ?></a>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Son Sayfa -->
                                            <?php if ($mevcutSayfa < $toplamSayfa): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= $baseUrl ?><?= $mevcutSayfa + 1 ?>" title="Sonraki sayfa">
                                                        <i class="ki-outline ki-right fs-6"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="<?= $baseUrl ?><?= $toplamSayfa ?>" title="Son sayfa">
                                                        <i class="ki-outline ki-double-right fs-6"></i>
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="ki-outline ki-right fs-6"></i></span>
                                                </li>
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="ki-outline ki-double-right fs-6"></i></span>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                    </div>
                                </nav>
                                
                                <!-- Sayfa Atlama (Mobil için) -->
                                <div class="d-md-none mt-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Sayfa:</span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="jumpToPage" 
                                               min="1" 
                                               max="<?= $toplamSayfa ?? 1 ?>" 
                                               value="<?= $mevcutSayfa ?? 1 ?>" 
                                               placeholder="Sayfa numarası">
                                        <button class="btn btn-primary" 
                                                type="button" 
                                                onclick="jumpToPage('<?= $baseUrl ?>')">
                                            Git
                                        </button>
                </div>
            </div>
        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
// Accordion Liste UX İyileştirmeleri
document.addEventListener('DOMContentLoaded', function() {
    // Accordion animasyonları
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    // Her accordion item'e fade-in animasyonu ekle
    accordionItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
    
    // Form submit sırasında loading state
    const filterForm = document.querySelector('form[action="/isemri/listesi"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            this.classList.add('loading');
            
            // Loading mesajı göster
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ki-outline ki-loading fs-2 me-2"></i>Yükleniyor...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                this.classList.remove('loading');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    }
    
    // Accordion açılırken scroll optimization
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            setTimeout(() => {
                if (!this.classList.contains('collapsed')) {
                    // Açılan accordion'u view'a scroll et
                    this.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }, 150);
        });
    });
    
    // Mobil cihazlar için touch feedback
    if ('ontouchstart' in window) {
        accordionItems.forEach(item => {
            const button = item.querySelector('.accordion-button');
            
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            button.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    }
    
    // Keyboard navigation (accessibility)
    document.addEventListener('keydown', function(e) {
        const focusedElement = document.activeElement;
        
        // ESC tuşu ile tüm accordion'ları kapat
        if (e.key === 'Escape') {
            accordionButtons.forEach(button => {
                if (!button.classList.contains('collapsed')) {
                    button.click();
                }
            });
        }
        
        // Space veya Enter ile accordion aç/kapat
        if ((e.key === ' ' || e.key === 'Enter') && focusedElement.classList.contains('accordion-button')) {
            e.preventDefault();
            focusedElement.click();
        }
        
        // Arrow keys ile navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            if (focusedElement.classList.contains('accordion-button')) {
                e.preventDefault();
                const currentIndex = Array.from(accordionButtons).indexOf(focusedElement);
                let nextIndex;
                
                if (e.key === 'ArrowDown') {
                    nextIndex = currentIndex + 1 < accordionButtons.length ? currentIndex + 1 : 0;
                } else {
                    nextIndex = currentIndex - 1 >= 0 ? currentIndex - 1 : accordionButtons.length - 1;
                }
                
                accordionButtons[nextIndex].focus();
            }
        }
    });
    
    // Auto-submit on filter change (sadece desktop)
    if (window.innerWidth > 768) {
        const filterSelects = document.querySelectorAll('#filtre-alani select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                clearTimeout(this.autoSubmitTimer);
                this.autoSubmitTimer = setTimeout(() => {
                    filterForm?.submit();
                }, 800); // Biraz daha uzun süre
            });
        });
    }
    
    // Filtre alanı otomatik kapatma (mobil)
    if (window.innerWidth <= 768) {
        const filterArea = document.getElementById('filtre-alani');
        
        if (filterForm && filterArea) {
            filterForm.addEventListener('submit', function() {
                setTimeout(() => {
                    filterArea.classList.remove('show');
                }, 500);
            });
        }
    }
    
    // Resim yükleme optimizasyonu
    const images = document.querySelectorAll('.accordion-body img');
    if ('IntersectionObserver' in window && images.length > 0) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '1';
                    img.style.transform = 'scale(1)';
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => {
            img.style.opacity = '0.3';
            img.style.transform = 'scale(0.95)';
            img.style.transition = 'all 0.3s ease';
            imageObserver.observe(img);
        });
    }
    
    // Dosya modal optimizasyonları
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            // Modal açılırken animasyon
            this.querySelector('.modal-content').style.animation = 'slideIn 0.3s ease-out';
        });
    });
    
    // Sayfa yükleme performansı
    window.addEventListener('load', function() {
        // Sayfa tamamen yüklendiğinde smooth scroll enable et
        document.documentElement.style.scrollBehavior = 'smooth';
        
        // Lazy loading görselleri aktive et
        const lazyImages = document.querySelectorAll('img[data-src]');
        if ('IntersectionObserver' in window && lazyImages.length > 0) {
            const lazyImageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        lazyImageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => lazyImageObserver.observe(img));
        }
    });
    
    // Responsive davranış
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            // Resize sonrası optimizasyonlar
            if (window.innerWidth <= 768) {
                // Mobil mod optimizasyonları
                accordionButtons.forEach(button => {
                    button.style.fontSize = '0.9rem';
                });
            } else {
                // Desktop mod optimizasyonları
                accordionButtons.forEach(button => {
                    button.style.fontSize = '';
                });
            }
        }, 250);
    });
});

// Sayfalama fonksiyonları
function jumpToPage(baseUrl) {
    const pageInput = document.getElementById('jumpToPage');
    const pageNumber = parseInt(pageInput.value);
    
    if (pageNumber && pageNumber > 0) {
        window.location.href = baseUrl + pageNumber;
    }
}

// Enter tuşu ile sayfa atlama
document.addEventListener('DOMContentLoaded', function() {
    const jumpInput = document.getElementById('jumpToPage');
    if (jumpInput) {
        jumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const baseUrl = this.getAttribute('data-base-url') || '/isemri/listesi?sayfa=';
                jumpToPage(baseUrl);
            }
        });
    }
});

// Modal Görsel Galeri Fonksiyonları
let currentImageIndex = {}; // Her modal için ayrı index

function showImage(modalId, index) {
    const imageData = JSON.parse(document.getElementById(`imageData_${modalId}`).textContent);
    const mainImage = document.getElementById(`mainImage_${modalId}`);
    const imageTitle = document.getElementById(`imageTitle_${modalId}`);
    const imageDetails = document.getElementById(`imageDetails_${modalId}`);
    const imageCounter = document.getElementById(`imageCounter_${modalId}`);
    
    // Geçerli index'i güncelle
    currentImageIndex[modalId] = index;
    
    // Görseli değiştir
    const currentImg = imageData[index];
    mainImage.src = `/public/uploads/isemri/${currentImg.dosya_yolu}`;
    mainImage.alt = currentImg.dosya_adi;
    
    // Bilgileri güncelle
    imageTitle.textContent = currentImg.dosya_adi;
    const uzanti = currentImg.dosya_adi.split('.').pop().toUpperCase();
    const boyut = currentImg.boyut ? `${(currentImg.boyut / 1024).toFixed(1)} KB` : 'Bilinmiyor';
    imageDetails.textContent = `${uzanti} • ${boyut}`;
    
    // Sayacı güncelle
    if (imageCounter) {
        imageCounter.textContent = `${index + 1} / ${imageData.length}`;
    }
    
    // Thumbnail'ları güncelle
    const thumbnails = document.querySelectorAll(`#dosya_modal_${modalId} .thumbnail-img`);
    thumbnails.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('active');
            thumb.style.border = '3px solid #007bff';
        } else {
            thumb.classList.remove('active');
            thumb.style.border = '1px solid #dee2e6';
        }
    });
}

function changeImage(modalId, direction) {
    const imageData = JSON.parse(document.getElementById(`imageData_${modalId}`).textContent);
    const currentIndex = currentImageIndex[modalId] || 0;
    let newIndex = currentIndex + direction;
    
    // Sınırları kontrol et
    if (newIndex < 0) {
        newIndex = imageData.length - 1;
    } else if (newIndex >= imageData.length) {
        newIndex = 0;
    }
    
    showImage(modalId, newIndex);
}

// Modal açılırken ilk görseli ayarla
document.addEventListener('DOMContentLoaded', function() {
    // Mobil cihaz algılama
    const isMobile = window.innerWidth <= 768 || 'ontouchstart' in window;
    
    // Tüm modalleri dinle
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const modalId = this.id.replace('dosya_modal_', '');
            const imageDataElement = document.getElementById(`imageData_${modalId}`);
            
            if (imageDataElement) {
                currentImageIndex[modalId] = 0;
                showImage(modalId, 0);
                
                // Mobilde viewport kilitleme
                if (isMobile) {
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${window.scrollY}px`;
                    document.body.style.width = '100%';
                }
            }
        });
        
        // Modal kapanırken temizlik
        modal.addEventListener('hidden.bs.modal', function() {
            const modalId = this.id.replace('dosya_modal_', '');
            delete currentImageIndex[modalId];
            
            // Mobilde scroll geri yükleme
            if (isMobile && document.body.style.position === 'fixed') {
                const scrollY = document.body.style.top;
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        });
        
        // Mobilde backdrop tıklama ile kapatma
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                const closeBtn = this.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                }
            }
        });
    });
    
    // Klavye navigation (sadece desktop)
    if (!isMobile) {
        document.addEventListener('keydown', function(e) {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modalId = openModal.id.replace('dosya_modal_', '');
                const imageData = document.getElementById(`imageData_${modalId}`);
                
                if (imageData) {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        changeImage(modalId, -1);
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        changeImage(modalId, 1);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        const closeBtn = openModal.querySelector('.btn-close');
                        if (closeBtn) closeBtn.click();
                    }
                }
            }
        });
    }
    
    // Mobil swipe desteği
    if (isMobile) {
        let touchStartX = 0;
        let touchEndX = 0;
        
        document.addEventListener('touchstart', function(e) {
            const openModal = document.querySelector('.modal.show');
            if (openModal && e.target.closest('.main-image-container')) {
                touchStartX = e.changedTouches[0].screenX;
            }
        });
        
        document.addEventListener('touchend', function(e) {
            const openModal = document.querySelector('.modal.show');
            if (openModal && e.target.closest('.main-image-container')) {
                touchEndX = e.changedTouches[0].screenX;
                const modalId = openModal.id.replace('dosya_modal_', '');
                const imageData = document.getElementById(`imageData_${modalId}`);
                
                if (imageData) {
                    const swipeThreshold = 50;
                    const swipeDistance = touchEndX - touchStartX;
                    
                    if (Math.abs(swipeDistance) > swipeThreshold) {
                        if (swipeDistance > 0) {
                            // Sağa swipe - önceki resim
                            changeImage(modalId, -1);
                        } else {
                            // Sola swipe - sonraki resim
                            changeImage(modalId, 1);
                        }
                    }
                }
            }
        });
    }
    
    // Resize event handler
    window.addEventListener('resize', function() {
        // Modal açıkken resize edilirse modal'i kapat
        const openModal = document.querySelector('.modal.show');
        if (openModal && window.innerWidth !== window.outerWidth) {
            const closeBtn = openModal.querySelector('.btn-close');
            if (closeBtn) {
                setTimeout(() => closeBtn.click(), 100);
            }
        }
    });
});

// API tabanlı iş emri yönetimi
class IsEmriManager {
    constructor() {
        this.apiService = window.isEmriApiService;
        this.init();
    }
    
    async init() {
        await this.loadIsEmriListesi();
        this.setupEventListeners();
    }
    
    async loadIsEmriListesi() {
        try {
            const response = await this.apiService.getIsEmriListesi();
            
            if (response.success) {
                this.updateIsEmriCount(response.data.length);
            } else {
                console.error('İş emri listesi yüklenemedi:', response.message);
            }
        } catch (error) {
            console.error('İş emri listesi yükleme hatası:', error);
        }
    }
    
    updateIsEmriCount(count) {
        // İş emri sayısını güncelle (eğer varsa)
        const countElement = document.querySelector('.isemri-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }
    
    setupEventListeners() {
        // Refresh butonu ekle
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
        refreshBtn.innerHTML = '<i class="ki-outline ki-refresh fs-7 me-1"></i> Yenile';
        refreshBtn.onclick = () => this.loadIsEmriListesi();
        
        const toolbar = document.querySelector('.card-toolbar');
        if (toolbar) {
            toolbar.appendChild(refreshBtn);
        }
    }
}

// Sayfa yüklendiğinde IsEmriManager'ı başlat
document.addEventListener('DOMContentLoaded', function() {
    window.isEmriManager = new IsEmriManager();
    // Pull-to-refresh entegrasyonu
    window.refreshPageData = async function() {
        if (window.isEmriManager) {
            await window.isEmriManager.loadIsEmriListesi();
        }
    }
});

// Service Worker registration (isteğe bağlı)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => console.log('SW registered:', registration))
            .catch(error => console.log('SW registration failed:', error));
    });
}
</script>

<!-- Mobil sabit aksiyon çubuğu -->
<div class="d-md-none" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1030;">
    <div class="bg-white border-top d-flex justify-content-around align-items-center py-2 shadow-sm">
        <button type="button" class="btn btn-light d-flex flex-column align-items-center" onclick="window.refreshPageData && window.refreshPageData()">
            <i class="ki-outline ki-refresh fs-2"></i>
            <small>Yenile</small>
        </button>
        <a href="/isemri/olustur" class="btn btn-primary d-flex flex-column align-items-center">
            <i class="ki-outline ki-plus fs-2"></i>
            <small>Oluştur</small>
        </a>
        <button type="button" class="btn btn-light d-flex flex-column align-items-center" onclick="(function(){var btn=document.querySelector('[data-bs-target=\'#filtre-alani\']'); if(btn){btn.click();}})()">
            <i class="ki-outline ki-filter fs-2"></i>
            <small>Filtre</small>
        </button>
    </div>
    <!-- Alt çubuk yüksekliği için boşluk -->
    <div style="height: 64px; background: transparent;"></div>
 </div>

<?php
require_once __DIR__ . '/../layouts/layout/footer.php';
?>

