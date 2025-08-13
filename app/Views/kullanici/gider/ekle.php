<?php require_once __DIR__ . '/../layouts/layout/header.php'; ?>

<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        <?php require_once __DIR__ . '/../layouts/layout/navbar.php'; ?>
        
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <div class="d-flex flex-column flex-column-fluid">
                    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                    Yeni Gider Ekle
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
                                    <li class="breadcrumb-item text-muted">Yeni Gider</li>
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
                                        <h3>Yeni Gider Ekle</h3>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <form method="post" action="/gider/ekle" enctype="multipart/form-data" id="giderForm">
                                        <?= csrf_field(); ?>
                                        <!-- Gider Başlığı -->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Gider Başlığı</label>
                                            <div class="col-lg-8">
                                                <input type="text" name="baslik" class="form-control form-control-lg form-control-solid" placeholder="Gider başlığını giriniz" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Miktar -->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Miktar</label>
                                            <div class="col-lg-8">
                                                <div class="input-group input-group-lg">
                                                    <input type="text" name="miktar" id="miktar" class="form-control form-control-solid money-input" placeholder="0,00" value="0,00" required>
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                                <div class="form-text">Türk Lirası formatında giriniz (örn: 1.234,56)</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tarih -->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tarih</label>
                                            <div class="col-lg-8">
                                                <input type="date" name="tarih" class="form-control form-control-lg form-control-solid" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Kategori -->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Kategori</label>
                                            <div class="col-lg-8">
                                                <select name="kategori" class="form-select form-select-lg form-select-solid">
                                                    <option value="Genel">Genel</option>
                                                    <option value="Personel">Personel</option>
                                                    <option value="Kira">Kira</option>
                                                    <option value="Elektrik">Elektrik</option>
                                                    <option value="Su">Su</option>
                                                    <option value="Doğalgaz">Doğalgaz</option>
                                                    <option value="İnternet">İnternet</option>
                                                    <option value="Temizlik">Temizlik</option>
                                                    <option value="Bakım">Bakım</option>
                                                    <option value="Diğer">Diğer</option>
                                                </select>
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
                                                                <label for="giderGorsel" class="btn btn-primary btn-lg">
                                                                    <i class="ki-outline ki-folder fs-2 me-2"></i>
                                                                    Dosya Seç
                                                                </label>
                                                                <input type="file" id="giderGorsel" name="gider_gorsel" accept="image/*" style="display: none;" onchange="handleFileSelect(this)">
                                                            </div>
                                                            
                                                            <!-- Kamera Butonu -->
                                                            <div>
                                                                <label for="giderGorselKamera" class="btn btn-success btn-lg">
                                                                    <i class="ki-outline ki-camera fs-2 me-2"></i>
                                                                    Kamera ile Çek
                                                                </label>
                                                                <input type="file" id="giderGorselKamera" name="gider_gorsel" accept="image/*" capture="environment" style="display: none;" onchange="handleFileSelect(this)">
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
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Açıklama</label>
                                            <div class="col-lg-8">
                                                <textarea name="aciklama" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Gider açıklaması (opsiyonel)"></textarea>
                                            </div>
                                        </div>
                                        
                                        <!-- Form Butonları -->
                                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                                            <a href="/gider/listesi" class="btn btn-light me-3">
                                                <i class="ki-outline ki-arrow-left fs-2"></i>
                                                İptal
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <span class="indicator-label">
                                                    <i class="ki-outline ki-check fs-2"></i>
                                                    Gider Ekle
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
        const otherInput = input.id === 'giderGorsel' ? 'giderGorselKamera' : 'giderGorsel';
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
    document.getElementById('giderGorsel').value = '';
    document.getElementById('giderGorselKamera').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadArea').style.display = 'block';
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Para birimi inputlarına event listener ekle
    document.querySelectorAll('.money-input').forEach(input => {
        // Focus olduğunda tüm içeriği seç
        input.addEventListener('focus', function() {
            this.select();
        });
        
        // Input değiştiğinde sadece temizlik yap, formatlamaz
        input.addEventListener('input', function() {
            handleMoneyInput(this);
        });
        
        // Blur olduğunda formatı düzelt
        input.addEventListener('blur', function() {
            if (this.value === '' || this.value === '0') {
                this.value = '0,00';
            }
            // Son formatlamayı yap
            formatMoneyForDisplay(this);
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
        });
    });
    
    // Form gönderimi öncesi para birimi formatını düzelt
    document.getElementById('giderForm').addEventListener('submit', function(e) {
        const miktarInput = document.getElementById('miktar');
        // Türkçe para formatını temizle ve nokta formatına çevir
        const value = parseTurkishMoney(miktarInput.value);
        miktarInput.value = value.toString();
        
        // Görsel yüklenmediğinde boş değer gönder
        const giderGorsel = document.getElementById('giderGorsel');
        const giderGorselKamera = document.getElementById('giderGorselKamera');
        
        // Eğer hiçbir dosya seçilmemişse, gizli input ekle
        if (!giderGorsel.files.length && !giderGorselKamera.files.length) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'gider_gorsel';
            hiddenInput.value = '';
            this.appendChild(hiddenInput);
        }
    });
});
</script>

<style>
.upload-area {
    transition: all 0.3s ease;
}

.upload-area:hover {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}

.money-input:focus {
    border-color: #009ef7;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.25);
}

#imagePreview img {
    box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
    border: 2px solid #e1e3ea;
}

.card-flush.border-dashed {
    transition: all 0.3s ease;
}

.card-flush.border-dashed:hover {
    border-color: #009ef7 !important;
    background-color: #f8f9fa;
}
</style>

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?> 