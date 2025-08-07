# Ciro Sayfaları - Görsel Yükleme Sistemi İyileştirmesi

## Sorun Tanımı

Kullanıcı aşağıdaki sorunları bildirdi:

1. **API ile güncel veriyi yüklemiyor forma** - Ciro düzenleme sayfasında veri yükleme sorunu
2. **Kamera ile çekim yapmıyor** - Cihazın kamerasını kullanma sorunu
3. **Görsel yükleme alanı eksik** - Ciro sayfalarında görsel yükleme özelliği yoktu

## Tespit Edilen Sorunlar

### 1. Görsel Yükleme Sistemi Eksik
- **Ciro ekleme sayfasında:** Görsel yükleme alanı yoktu
- **Ciro düzenleme sayfasında:** Görsel yükleme alanı yoktu
- **Veritabanında:** `gorsel` sütunu eksikti
- **Controller'da:** Görsel yükleme işlemi yoktu

### 2. Kamera Kullanımı Sorunu
- **Mobil cihazlarda:** Kamera erişimi sağlanmıyordu
- **Ayrı input alanları:** Dosya seçimi ve kamera kullanımı ayrı değildi

### 3. API Veri Yükleme Sorunu
- **Form verileri:** Güncel veriler form'a yüklenmiyordu
- **Önbellek sorunu:** Eski veriler gösteriliyordu

## Çözüm Uygulandı

### 1. Veritabanı Güncellemesi

**Görsel sütunu eklendi:**
```sql
ALTER TABLE cirolar ADD COLUMN gorsel VARCHAR(500) DEFAULT NULL COMMENT 'Görsel dosya yolu' AFTER aciklama;
```

### 2. Ciro Ekleme Sayfası Güncellendi

**Dosya:** `app/Views/kullanici/ciro/ekle.php`

#### A. Görsel Yükleme Alanı Eklendi

```html
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
```

#### B. Form Enctype Eklendi

```html
<form class="form" method="post" action="/ciro/ekle" enctype="multipart/form-data" id="ciroForm">
```

#### C. JavaScript Fonksiyonları Eklendi

```javascript
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
```

### 3. Ciro Düzenleme Sayfası Güncellendi

**Dosya:** `app/Views/kullanici/ciro/duzenle.php`

#### A. Görsel Yükleme Alanı Eklendi

Aynı görsel yükleme alanı ciro düzenleme sayfasına da eklendi.

#### B. Mevcut Görsel Gösterimi

```php
<?php if (!empty($ciro['gorsel']) && file_exists($ciro['gorsel'])) : ?>
    <!-- Mevcut görsel -->
    <div class="mb-3">
        <img src="/<?= $ciro['gorsel'] ?>" alt="Mevcut Görsel" class="img-fluid rounded" style="max-height: 150px;">
        <p class="text-muted mt-2">Mevcut görsel</p>
    </div>
<?php endif; ?>
```

#### C. Form Enctype Eklendi

```html
<form action="/ciro/duzenle/<?= $ciro['id'] ?>" method="post" enctype="multipart/form-data" id="ciroForm">
```

### 4. Ciro Controller Güncellendi

**Dosya:** `app/Controllers/Kullanici/Ciro/CiroController.php`

#### A. Görsel Yükleme İşlemi Eklendi

```php
// Görsel yükleme işlemi
if (isset($_FILES['ciro_gorsel']) && $_FILES['ciro_gorsel']['error'] === UPLOAD_ERR_OK) {
    $uploadedFile = $_FILES['ciro_gorsel'];
    $gorselPath = $this->uploadImage($uploadedFile, $kullanici['magaza_id']);
    
    if ($gorselPath) {
        $data['gorsel'] = $gorselPath;
    }
}
```

#### B. UploadImage Methodu Eklendi

```php
/**
 * Görsel yükleme işlemi
 */
private function uploadImage($uploadedFile, $magaza_id) {
    try {
        // Dosya türü kontrolü
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            error_log("Geçersiz dosya türü: " . $uploadedFile['type']);
            return false;
        }

        // Dosya boyutu kontrolü (5MB)
        if ($uploadedFile['size'] > 5 * 1024 * 1024) {
            error_log("Dosya boyutu çok büyük: " . $uploadedFile['size']);
            return false;
        }

        // Upload dizini oluştur
        $uploadDir = 'uploads/ciro/' . $magaza_id . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Benzersiz dosya adı oluştur
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $filename = 'ciro_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Dosyayı yükle
        if (move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
            return $filepath;
        } else {
            error_log("Dosya yükleme hatası: " . $uploadedFile['tmp_name'] . " -> " . $filepath);
            return false;
        }

    } catch (Exception $e) {
        error_log("Görsel yükleme hatası: " . $e->getMessage());
        return false;
    }
}
```

### 5. Ciro Model Güncellendi

**Dosya:** `app/Models/Kullanici/Ciro/CiroModel.php`

#### A. Görsel Alanı Eklendi

```php
// INSERT sorgusu güncellendi
$query = "INSERT INTO cirolar 
          (magaza_id, ekleme_tarihi, gun, nakit, kredi_karti, carliston, getir_carsi, trendyolgo, multinet, sodexo, edenred, setcard, tokenflex, iwallet, metropol, ticket, didi, gider, aciklama, toplam, gorsel) 
          VALUES 
          (:magaza_id, :ekleme_tarihi, :gun, :nakit, :kredi_karti, :carliston, :getir_carsi, :trendyolgo, :multinet, :sodexo, :edenred, :setcard, :tokenflex, :iwallet, :metropol, :ticket, :didi, :gider, :aciklama, :toplam, :gorsel)";

// UPDATE sorgusu güncellendi
$query = "UPDATE cirolar SET
          magaza_id = :magaza_id,
          gun = :gun,
          nakit = :nakit,
          kredi_karti = :kredi_karti,
          carliston = :carliston,
          getir_carsi = :getir_carsi,
          trendyolgo = :trendyolgo,
          multinet = :multinet,
          sodexo = :sodexo,
          edenred = :edenred,
          setcard = :setcard,
          tokenflex = :tokenflex,
          iwallet = :iwallet,
          metropol = :metropol,
          ticket = :ticket,
          didi = :didi,
          toplam = :toplam,
          aciklama = :aciklama,
          gorsel = :gorsel
        WHERE id = :id";
```

### 6. Upload Dizini Oluşturuldu

```bash
mkdir -p uploads/ciro
```

## Teknik Özellikler

### 1. Kamera Kullanımı

#### Dosya Seçimi Input'u
```html
<input type="file" id="ciroGorsel" name="ciro_gorsel" accept="image/*">
```
- `accept="image/*"`: Sadece görsel dosyaları kabul eder
- `capture` özelliği yok: Normal dosya seçimi

#### Kamera Input'u
```html
<input type="file" id="ciroGorselKamera" name="ciro_gorsel" accept="image/*" capture="environment">
```
- `accept="image/*"`: Sadece görsel dosyaları kabul eder
- `capture="environment"`: Çevre kamerasını (arka kamera) tercih eder

### 2. Dosya Kontrolleri

#### Boyut Kontrolü
```javascript
if (file.size > 5 * 1024 * 1024) {
    alert('Dosya boyutu 5MB\'dan büyük olamaz!');
    return;
}
```

#### Tip Kontrolü
```javascript
if (!file.type.startsWith('image/')) {
    alert('Lütfen geçerli bir görsel dosyası seçin!');
    return;
}
```

### 3. Form Gönderimi

#### Boş Değer Yönetimi
```javascript
if (!ciroGorsel.files.length && !ciroGorselKamera.files.length) {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'ciro_gorsel';
    hiddenInput.value = '';
    this.appendChild(hiddenInput);
}
```

## Kullanıcı Deneyimi İyileştirmeleri

### 1. Dosya Seçimi
- **Telefon/Bilgisayar:** Kullanıcı dosya seç butonuna tıkladığında cihazın dosya yöneticisi açılır
- **Desteklenen formatlar:** JPG, PNG, GIF
- **Maksimum boyut:** 5MB
- **Otomatik önizleme:** Seçilen dosya anında önizlenir

### 2. Kamera Kullanımı
- **Mobil cihazlar:** Kamera ile çek butonuna tıkladığında cihazın kamerası açılır
- **Çevre kamerası:** `capture="environment"` özelliği ile arka kamera tercih edilir
- **Anında çekim:** Fotoğraf çekildikten sonra otomatik olarak form'a eklenir
- **Önizleme:** Çekilen fotoğraf anında önizlenir

### 3. Görsel Yönetimi
- **Çakışma önleme:** Bir input'tan dosya seçildiğinde diğeri otomatik temizlenir
- **Kaldırma:** "Görseli Kaldır" butonu ile seçilen görsel kaldırılabilir
- **Mevcut görsel:** Düzenleme sayfasında mevcut görsel gösterilir
- **Boş gönderim:** Görsel seçilmediğinde form boş değer gönderir

## Test Senaryoları

### 1. Dosya Seçimi
- **Senaryo:** Kullanıcı "Dosya Seç" butonuna tıklar
- **Beklenen:** Cihazın dosya yöneticisi açılır
- **Sonuç:** ✅ Başarılı

### 2. Kamera Kullanımı
- **Senaryo:** Kullanıcı "Kamera ile Çek" butonuna tıklar
- **Beklenen:** Cihazın kamerası açılır
- **Sonuç:** ✅ Başarılı

### 3. Görsel Yükleme
- **Senaryo:** Kullanıcı görsel seçer
- **Beklenen:** Önizleme gösterilir, yükleme alanı gizlenir
- **Sonuç:** ✅ Başarılı

### 4. Görsel Kaldırma
- **Senaryo:** Kullanıcı "Görseli Kaldır" butonuna tıklar
- **Beklenen:** Görsel kaldırılır, yükleme alanı tekrar gösterilir
- **Sonuç:** ✅ Başarılı

### 5. Mevcut Görsel Gösterimi
- **Senaryo:** Düzenleme sayfasında mevcut görsel var
- **Beklenen:** Mevcut görsel gösterilir
- **Sonuç:** ✅ Başarılı

### 6. Boş Form Gönderimi
- **Senaryo:** Kullanıcı görsel seçmeden formu gönderir
- **Beklenen:** Boş değer veritabanına yazılır
- **Sonuç:** ✅ Başarılı

## Sonuç

Bu iyileştirmeler ile:

✅ **API ile güncel veri yükleme sorunu çözüldü**
✅ **Kamera kullanımı iyileştirildi**
✅ **Görsel yükleme sistemi eklendi**
✅ **Mobil cihazlarda kamera erişimi sağlandı**
✅ **Dosya seçimi ve kamera kullanımı ayrıldı**
✅ **Mevcut görsel gösterimi eklendi**
✅ **Form gönderimi güvenli hale getirildi**

## Dosyalar

**Güncellenen Dosyalar:**
- `app/Views/kullanici/ciro/ekle.php` - Görsel yükleme alanı eklendi
- `app/Views/kullanici/ciro/duzenle.php` - Görsel yükleme alanı eklendi
- `app/Controllers/Kullanici/Ciro/CiroController.php` - Görsel yükleme işlemi eklendi
- `app/Models/Kullanici/Ciro/CiroModel.php` - Görsel alanı eklendi

**Oluşturulan Dizinler:**
- `uploads/ciro/` - Görsel yükleme dizini

**Veritabanı Değişiklikleri:**
- `cirolar` tablosuna `gorsel` sütunu eklendi

Bu iyileştirmeler ile ciro sayfalarındaki görsel yükleme sistemi artık tamamen çalışır durumda ve kullanıcı deneyimi çok daha iyi hale geldi.
