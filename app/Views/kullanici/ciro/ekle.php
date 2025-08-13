<?php
require_once __DIR__ . '/../layouts/layout/header.php';
require_once __DIR__ . '/../layouts/layout/navbar.php';
?>

<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Yeni Ciro Ekle
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/anasayfa" class="text-muted text-hover-primary">Anasayfa</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="/ciro/listele" class="text-muted text-hover-primary">Ciro İşlemleri</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Yeni Ciro</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['message'];
                            unset($_SESSION['message'], $_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h3>Ciro Ekleme Formu</h3>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <form class="form" method="post" action="/ciro/ekle" enctype="multipart/form-data" id="ciroForm">
                                <?= csrf_field(); ?>
                                <!-- Mağaza Bilgisi -->
                                <div class="row mb-6">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label fw-semibold fs-6">Mağaza</label>
                                        <div class="form-control form-control-lg form-control-solid bg-light-primary">
                                            <strong><?= htmlspecialchars($kullanici['magaza_isim'] ?? 'Mağaza bilgisi bulunamadı') ?></strong>
                                        </div>
                                        <input type="hidden" name="magaza_id" value="<?= $kullanici['magaza_id'] ?? '' ?>">
                                        <input type="hidden" name="magaza_ad" value="<?= htmlspecialchars($kullanici['magaza_isim'] ?? '') ?>">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label for="ekleme_tarihi" class="form-label fw-semibold fs-6">Ekleme Tarihi</label>
                                        <input type="text" name="ekleme_tarihi" id="ekleme_tarihi" class="form-control form-control-lg form-control-solid" readonly>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label for="gun" class="form-label required fw-semibold fs-6">İşlem Günü</label>
                                        <input type="date" name="gun" id="gun" class="form-control form-control-lg form-control-solid" required>
                                    </div>
                                </div>

                                <!-- Ana Ödeme Yöntemleri -->
                                <div class="card card-flush mb-6">
                                    <div class="card-header">
                                        <h4 class="card-title fw-bold text-primary">
                                            <i class="ki-outline ki-dollar fs-2 me-2"></i>
                                            Ana Ödeme Yöntemleri
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="nakit" class="form-label required fw-semibold fs-6">Nakit</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="nakit" id="nakit" class="form-control form-control-solid money-input" value="0,00" required>
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="kredi_karti" class="form-label required fw-semibold fs-6">Kredi Kartı</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="kredi_karti" id="kredi_karti" class="form-control form-control-solid money-input" value="0,00" required>
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Online Platformlar -->
                                <div class="card card-flush mb-6">
                                    <div class="card-header">
                                        <h4 class="card-title fw-bold text-success">
                                            <i class="ki-outline ki-globe fs-2 me-2"></i>
                                            Online Platformlar
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="carliston" class="form-label fw-semibold fs-6">YemekSepeti</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="carliston" id="carliston" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="getir_carsi" class="form-label fw-semibold fs-6">Getir Çarşı</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="getir_carsi" id="getir_carsi" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="trendyolgo" class="form-label fw-semibold fs-6">TrendyolGO</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="trendyolgo" id="trendyolgo" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Yemek Kartları -->
                                <div class="card card-flush mb-6">
                                    <div class="card-header">
                                        <h4 class="card-title fw-bold text-warning">
                                            <i class="ki-outline ki-credit-cart fs-2 me-2"></i>
                                            Yemek Kartları
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="multinet" class="form-label fw-semibold fs-6">MultiNet</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="multinet" id="multinet" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="sodexo" class="form-label fw-semibold fs-6">Sodexo</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="sodexo" id="sodexo" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="edenred" class="form-label fw-semibold fs-6">Edenred</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="edenred" id="edenred" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="setcard" class="form-label fw-semibold fs-6">Setcard</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="setcard" id="setcard" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="tokenflex" class="form-label fw-semibold fs-6">Token Flex</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="tokenflex" id="tokenflex" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="iwallet" class="form-label fw-semibold fs-6">iWallet</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="iwallet" id="iwallet" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="metropol" class="form-label fw-semibold fs-6">Metropol</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="metropol" id="metropol" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="ticket" class="form-label fw-semibold fs-6">Ticket</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="ticket" id="ticket" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="didi" class="form-label fw-semibold fs-6">Didi</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="didi" id="didi" class="form-control form-control-solid money-input" value="0,00">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Toplam Ciro -->
                                <div class="card card-flush mb-6">
                                    <div class="card-header">
                                        <h4 class="card-title fw-bold text-success">
                                            <i class="ki-outline ki-calculator fs-2 me-2"></i>
                                            Toplam Ciro
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <label for="toplam" class="form-label fw-bold fs-5 text-success">Toplam Ciro</label>
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="toplam" id="toplam" class="form-control form-control-solid bg-light-success" readonly>
                                                    <span class="input-group-text bg-success text-white fw-bold">₺</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Görsel Yükleme -->
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Görsel Ekle</label>
                                    <div class="col-lg-8">
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

                                <!-- Açıklama -->
                                <div class="row mb-6">
                                    <div class="col-12">
                                        <label for="aciklama" class="form-label fw-semibold fs-6">Açıklama</label>
                                        <textarea name="aciklama" id="aciklama" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Varsa ek açıklama ekleyiniz..."></textarea>
                                    </div>
                                </div>

                                <!-- Form Butonları -->
                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="/ciro/listele" class="btn btn-light me-3">
                                        <i class="ki-outline ki-arrow-left fs-2"></i>
                                        Geri Dön
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">
                                            <i class="ki-outline ki-check fs-2"></i>
                                            Ciro Kaydet
                                        </span>
                                        <span class="indicator-progress">
                                            Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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

// Toplam hesaplama
function calculateTotal() {
    const fields = [
        'nakit', 'kredi_karti', 'carliston', 'getir_carsi', 'trendyolgo',
        'multinet', 'sodexo', 'edenred', 'setcard', 'tokenflex', 
        'iwallet', 'metropol', 'ticket', 'didi'
    ];
    
    let total = 0;
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            total += parseTurkishMoney(field.value);
        }
    });
    
    // Toplamı formatla
    const toplamField = document.getElementById('toplam');
    if (toplamField) {
        toplamField.value = formatTurkishMoney(total);
    }
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Tarih alanını doldur
    const dateInput = document.getElementById('ekleme_tarihi');
    if (dateInput) {
        const today = new Date();
        const formattedDate = today.toLocaleDateString('tr-TR');
        dateInput.value = formattedDate;
    }
    
    // Gün alanını bugün yap
    const gunInput = document.getElementById('gun');
    if (gunInput) {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        gunInput.value = formattedDate;
    }
    
    // İlk toplam hesaplama
    calculateTotal();
    
    // Para birimi inputlarına event listener ekle
    document.querySelectorAll('.money-input').forEach(input => {
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

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?>
