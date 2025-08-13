<?php
require_once __DIR__ . '/../layouts/layout/header.php';
require_once __DIR__ . '/../layouts/layout/navbar.php';

// Mağaza listesini al
$ciroModel = new \app\Models\Kullanici\Ciro\CiroModel();
$magazalar = $ciroModel->getMagazalar();

// Önbellek sorununu önlemek için unique timestamp
$timestamp = time();
?>

<!-- Önbellek önleme meta tag'leri -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="container-fluid mt-3 mt-lg-5">
            
            <!-- Başlık -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">
                                <i class="ki-outline ki-pencil fs-3 me-2 text-primary"></i>
                                Ciro Düzenle
                            </h4>
                            <p class="mb-0 text-muted">Ciro kaydını düzenleyin</p>
                        </div>
                        <div>
                            <a href="/ciro/listele" class="btn btn-outline-secondary">
                                <i class="ki-outline ki-arrow-left fs-2 me-2"></i>
                                Geri Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mesaj -->
            <?php if (isset($_SESSION['message'])) : ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show">
                            <i class="ki-outline ki-<?= $_SESSION['message_type'] == 'success' ? 'check-circle' : 'cross-circle' ?> fs-2 me-2"></i>
                            <?= htmlspecialchars($_SESSION['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Form -->
            <?php if (isset($ciro) && is_array($ciro)) : ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light border-bottom p-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="ki-outline ki-dollar text-success me-2"></i>
                                Ciro Bilgileri
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="/ciro/duzenle/<?= $ciro['id'] ?>" method="post" enctype="multipart/form-data" id="ciroForm">
                                <?= csrf_field(); ?>
                                <!-- Önbellek önleme hidden input -->
                                <input type="hidden" name="timestamp" value="<?= $timestamp ?>">
                                
                                <!-- Temel Bilgiler -->
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Şube</label>
                                        <select name="magaza_id" class="form-select" required>
                                            <?php foreach ($magazalar as $magaza) : ?>
                                                <option value="<?= $magaza['id'] ?>" <?= ($magaza['id'] == $ciro['magaza_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($magaza['ad']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Gün</label>
                                        <input type="date" name="gun" class="form-control" value="<?= $ciro['gun'] ?>" required>
                                    </div>
                                </div>

                                <!-- Ana Ödeme Yöntemleri -->
                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="ki-outline ki-dollar me-2"></i>
                                            Ana Ödeme Yöntemleri
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nakit</label>
                                        <div class="input-group">
                                            <input type="text" name="nakit" class="form-control money-input" value="<?= number_format($ciro['nakit'] ?? 0, 2, ',', '.') ?>" required>
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Kredi Kartı</label>
                                        <div class="input-group">
                                            <input type="text" name="kredi_karti" class="form-control money-input" value="<?= number_format($ciro['kredi_karti'] ?? 0, 2, ',', '.') ?>" required>
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Online Platformlar -->
                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-success mb-3">
                                            <i class="ki-outline ki-globe me-2"></i>
                                            Online Platformlar
                                        </h6>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Carliston</label>
                                        <div class="input-group">
                                            <input type="text" name="carliston" class="form-control money-input" value="<?= number_format($ciro['carliston'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Getir Çarşı</label>
                                        <div class="input-group">
                                            <input type="text" name="getir_carsi" class="form-control money-input" value="<?= number_format($ciro['getir_carsi'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">TrendyolGO</label>
                                        <div class="input-group">
                                            <input type="text" name="trendyolgo" class="form-control money-input" value="<?= number_format($ciro['trendyolgo'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Yemek Kartları -->
                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-warning mb-3">
                                            <i class="ki-outline ki-credit-cart me-2"></i>
                                            Yemek Kartları
                                        </h6>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Multinet</label>
                                        <div class="input-group">
                                            <input type="text" name="multinet" class="form-control money-input" value="<?= number_format($ciro['multinet'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Sodexo</label>
                                        <div class="input-group">
                                            <input type="text" name="sodexo" class="form-control money-input" value="<?= number_format($ciro['sodexo'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Edenred</label>
                                        <div class="input-group">
                                            <input type="text" name="edenred" class="form-control money-input" value="<?= number_format($ciro['edenred'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Setcard</label>
                                        <div class="input-group">
                                            <input type="text" name="setcard" class="form-control money-input" value="<?= number_format($ciro['setcard'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Tokenflex</label>
                                        <div class="input-group">
                                            <input type="text" name="tokenflex" class="form-control money-input" value="<?= number_format($ciro['tokenflex'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">iWallet</label>
                                        <div class="input-group">
                                            <input type="text" name="iwallet" class="form-control money-input" value="<?= number_format($ciro['iwallet'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Metropol</label>
                                        <div class="input-group">
                                            <input type="text" name="metropol" class="form-control money-input" value="<?= number_format($ciro['metropol'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Ticket</label>
                                        <div class="input-group">
                                            <input type="text" name="ticket" class="form-control money-input" value="<?= number_format($ciro['ticket'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Didi</label>
                                        <div class="input-group">
                                            <input type="text" name="didi" class="form-control money-input" value="<?= number_format($ciro['didi'] ?? 0, 2, ',', '.') ?>">
                                            <span class="input-group-text">₺</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Görsel Yükleme -->
                                <div class="row g-4 mb-4">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Görsel Ekle</label>
                                        <div class="card card-flush border-dashed border-2 border-gray-300">
                                            <div class="card-body text-center py-10">
                                                <!-- Görsel Önizleme -->
                                                <div id="imagePreview" class="mb-5" style="display: none;">
                                                    <img id="previewImg" src="" alt="Önizleme" class="img-fluid rounded" style="max-height: 200px;">
                                                    <button type="button" class="btn btn-sm btn-light-danger mt-2" onclick="removeImage()">
                                                        <i class="ki-outline ki-cross fs-2"></i> Görseli Kaldır
                                                    </button>
                                                </div>
                                                
                                                <!-- Yükleme Alanı -->
                                                <div id="uploadArea" class="upload-area">
                                                    <?php if (!empty($ciro['gorsel']) && file_exists($ciro['gorsel'])) : ?>
                                                        <!-- Mevcut görsel -->
                                                        <div class="mb-3">
                                                            <img src="/<?= $ciro['gorsel'] ?>" alt="Mevcut Görsel" class="img-fluid rounded" style="max-height: 150px;">
                                                            <p class="text-muted mt-2">Mevcut görsel</p>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <i class="ki-outline ki-image fs-3x text-muted mb-5"></i>
                                                    <h3 class="fs-5 fw-bold text-gray-900 mb-2">Görsel Yükle</h3>
                                                    <p class="text-muted mb-5">Dosya seçin veya kamera ile fotoğraf çekin</p>
                                                    
                                                    <!-- Dosya Seçimi -->
                                                    <div class="mb-3">
                                                        <label for="ciroGorsel" class="btn btn-primary btn-lg">
                                                            <i class="ki-outline ki-folder fs-2 me-2"></i>
                                                            Dosya Seç
                                                        </label>
                                                        <input type="file" id="ciroGorsel" name="ciro_gorsel" accept="image/*" style="display: none;" onchange="handleFileSelect(this)">
                                                    </div>
                                                    
                                                    <!-- Kamera Butonu -->
                                                    <div>
                                                        <label for="ciroGorselKamera" class="btn btn-success btn-lg">
                                                            <i class="ki-outline ki-camera fs-2 me-2"></i>
                                                            Kamera ile Çek
                                                        </label>
                                                        <input type="file" id="ciroGorselKamera" name="ciro_gorsel" accept="image/*" capture="environment" style="display: none;" onchange="handleFileSelect(this)">
                                                    </div>
                                                    
                                                    <div class="form-text mt-3">
                                                        Desteklenen formatlar: JPG, PNG, GIF (Maksimum 5MB)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Toplam ve Açıklama -->
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Toplam</label>
                                        <div class="input-group">
                                            <input type="text" name="toplam" id="toplam" class="form-control fw-bold text-success" value="<?= number_format($ciro['toplam'] ?? 0, 2, ',', '.') ?>" readonly>
                                            <span class="input-group-text fw-bold">₺</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Açıklama</label>
                                        <textarea name="aciklama" class="form-control" rows="3" placeholder="Varsa açıklama ekleyin..."><?= htmlspecialchars($ciro['aciklama'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Butonlar -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex gap-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ki-outline ki-check fs-2 me-2"></i>
                                                Güncelle
                                            </button>
                                            <a href="/ciro/listele" class="btn btn-outline-secondary">
                                                <i class="ki-outline ki-cross fs-2 me-2"></i>
                                                İptal
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Para birimi maskeleme için yardımcı fonksiyonlar
function parseTurkishMoney(value) {
    if (!value) return 0;
    
    // Türkçe para formatını temizle (1.234,56 -> 1234.56)
    let cleanValue = value.toString();
    cleanValue = cleanValue.replace(/\./g, ''); // Binlik ayırıcıları kaldır
    cleanValue = cleanValue.replace(',', '.'); // Virgülü noktaya çevir
    
    return parseFloat(cleanValue) || 0;
}

function formatTurkishMoney(amount) {
    return amount.toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Para birimi input işleme - sadece temizlik yapar, formatlamaz
function handleMoneyInput(input) {
    let value = input.value;
    
    // Sadece rakam, virgül ve nokta bırak
    value = value.replace(/[^\d,.]/g, '');
    
    // Eğer boşsa
    if (value === '') {
        input.value = '0,00';
        return;
    }
    
    // Birden fazla virgül varsa sadece ilkini tut
    const commaCount = (value.match(/,/g) || []).length;
    if (commaCount > 1) {
        const parts = value.split(',');
        value = parts[0] + ',' + parts.slice(1).join('');
    }
    
    // Eğer sadece virgül varsa
    if (value === ',') {
        input.value = '0,00';
        return;
    }
    
    // Eğer 0,00 ise ve kullanıcı rakam yazıyorsa, 0,00'ı temizle
    if (input.value === '0,00' && /^\d/.test(value)) {
        value = value.replace('0,00', '');
    }
    
    // Değeri güncelle (formatlamadan)
    input.value = value;
}

// Geliştirilmiş para birimi formatlaması - sadece görüntüleme için
function formatMoneyForDisplay(input) {
    let value = input.value;
    
    // Sadece rakam, virgül ve nokta bırak
    value = value.replace(/[^\d,.]/g, '');
    
    // Eğer boşsa
    if (value === '') {
        input.value = '0,00';
        return;
    }
    
    // Birden fazla virgül varsa sadece ilkini tut
    const commaCount = (value.match(/,/g) || []).length;
    if (commaCount > 1) {
        const parts = value.split(',');
        value = parts[0] + ',' + parts.slice(1).join('');
    }
    
    // Eğer sadece virgül varsa
    if (value === ',') {
        input.value = '0,00';
        return;
    }
    
    // Sayıyı parse et
    let num = parseTurkishMoney(value);
    
    // Türkçe formatında göster
    input.value = formatTurkishMoney(num);
}

// Toplam hesaplama fonksiyonu
function calculateTotal() {
    const inputs = document.querySelectorAll('.money-input');
    let total = 0;
    
    inputs.forEach(input => {
        total += parseTurkishMoney(input.value);
    });
    
    const toplamInput = document.getElementById('toplam');
    if (toplamInput) {
        toplamInput.value = formatTurkishMoney(total);
    }
}

// Sayfa yüklendiğinde toplam hesapla
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    
    // Para birimi inputlarına event'ler ekle
    const moneyInputs = document.querySelectorAll('.money-input');
    moneyInputs.forEach(input => {
        // Focus olduğunda tüm içeriği seç
        input.addEventListener('focus', function() {
            this.select();
        });
        
        // Input değiştiğinde sadece temizlik yap, formatlamaz
        input.addEventListener('input', function() {
            handleMoneyInput(this);
            calculateTotal();
        });
        
        // Blur olduğunda formatı düzelt
        input.addEventListener('blur', function() {
            if (this.value === '' || this.value === '0') {
                this.value = '0,00';
            }
            // Son formatlamayı yap
            formatMoneyForDisplay(this);
            calculateTotal();
        });
        
        // Keydown event - sadece rakam, virgül, nokta ve kontrol tuşlarına izin ver
        input.addEventListener('keydown', function(e) {
            const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
            const allowedChars = /[\d,.]/;
            
            if (!allowedKeys.includes(e.key) && !allowedChars.test(e.key)) {
                e.preventDefault();
            }
        });
        
        // Paste event - sadece rakam, virgül ve nokta bırak
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const cleanText = pastedText.replace(/[^\d,.]/g, '');
            this.value = cleanText;
            formatMoneyForDisplay(this);
            calculateTotal();
        });
    });
});

// Dosya seçimi işleme
function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        // Dosya boyutu kontrolü (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Dosya boyutu 5MB\'dan büyük olamaz!');
            input.value = '';
            return;
        }
        
        // Dosya tipi kontrolü
        if (!file.type.startsWith('image/')) {
            alert('Lütfen geçerli bir görsel dosyası seçin!');
            input.value = '';
            return;
        }
        
        // Diğer input'u temizle
        const otherInput = input.id === 'ciroGorsel' ? 'ciroGorselKamera' : 'ciroGorsel';
        document.getElementById(otherInput).value = '';
        
        // Önizleme göster
        showImagePreview(file);
    }
}

// Görsel önizleme gösterme
function showImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('imagePreview').style.display = 'block';
        document.getElementById('uploadArea').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// Görsel kaldırma
function removeImage() {
    document.getElementById('ciroGorsel').value = '';
    document.getElementById('ciroGorselKamera').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadArea').style.display = 'block';
}

// Form gönderilmeden önce para birimi formatını düzelt
document.getElementById('ciroForm').addEventListener('submit', function(e) {
    const moneyInputs = document.querySelectorAll('.money-input');
    moneyInputs.forEach(input => {
        // Türkçe para formatını temizle ve nokta formatına çevir
        const value = parseTurkishMoney(input.value);
        input.value = value.toString();
    });
    
    // Görsel yüklenmediğinde boş değer gönder
    const ciroGorsel = document.getElementById('ciroGorsel');
    const ciroGorselKamera = document.getElementById('ciroGorselKamera');
    
    // Eğer hiçbir dosya seçilmemişse, gizli input ekle
    if (!ciroGorsel.files.length && !ciroGorselKamera.files.length) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'ciro_gorsel';
        hiddenInput.value = '';
        this.appendChild(hiddenInput);
    }
});
</script>

<?php
require_once __DIR__ . '/../layouts/layout/footer.php';
?>