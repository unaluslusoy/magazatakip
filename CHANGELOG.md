# Değişiklik Geçmişi (CHANGELOG)

Tüm önemli proje değişiklikleri bu dosyada belgelenecektir.

## [v1.2.0] - 2024-12-21

### ✨ Yeni Özellikler

#### 🔐 Giriş Sistemi Geliştirmeleri
- **Şifre Göster/Gizle** özelliği eklendi
- **Gelişmiş "Beni Hatırla"** sistemi
  - LocalStorage ile otomatik giriş
  - Çıkış yapana kadar sürekli oturum
  - Otomatik form doldurma

#### 👤 Kullanıcı Menüsü Geliştirmeleri
- **Dinamik Avatar Sistemi**
  - Kullanıcı fotoğrafı varsa göster
  - Yoksa ad/soyad baş harfleri
- **Kullanıcı Bilgileri Gösterimi**
  - Tam ad ve soyad
  - E-posta adresi
  - Unvan/Rol bilgisi
- **Çıkış Sistemi** - LocalStorage temizleme

#### 📋 Personel Profil Sistemi
- **Kapsamlı Personel Künyesi**
  - Personel numarası ile kimlik sistemi
  - Çalışan durumu (aktif/pasif/izinli)
  - Otomatik kıdem hesaplama
  
- **Detaylı Bilgi Kartları**
  - 🆔 Kimlik Bilgileri (TC, doğum, kan grubu)
  - 💼 Görev Bilgileri (pozisyon, departman, başlama tarihi)
  - 📄 İstihdam Bilgileri (sözleşme, SGK, vergi no)
  - 📞 İletişim & Konum (telefon, adres, acil durum)
  - 👥 İK & Haklar (izin, maaş, medeni durum)
  - 🏆 Yetkinlikler (eğitim, dil, yetenekler)

- **Form Tabanlı Görünüm**
  - Readonly input formları
  - Disabled select dropdown'lar
  - Profesyonel form tasarımı
  - Bootstrap solid stili

### 🔧 Teknik Geliştirmeler

#### 📊 Veritabanı Şeması Genişletilmesi
- **Personel Tablosu** yeni alanlar:
  - `calisan_no` - Çalışan numarası
  - `kullanici_id` - Kullanıcı bağlantısı
  - `magaza_id` - Mağaza bağlantısı
  - `durum` - Çalışan durumu
  - `kan_grubu` - Kan grubu bilgisi
  - `medeni_durum` - Medeni durum
  - `cocuk_sayisi` - Çocuk sayısı
  - `ehliyet_sinifi` - Ehliyet sınıfı
  - `iban_no` - Banka hesap bilgisi
  - `vergi_no` - Vergi numarası
  - `acil_durum_kisi_adi` - Acil durum kişisi
  - `acil_durum_kisi_telefon` - Acil durum telefonu
  - `is_cikis_tarihi` - İş çıkış tarihi
  - `calisma_saatleri` - Çalışma saatleri
  - `dogum_yeri` - Doğum yeri
  - `uyruk` - Uyruk bilgisi
  - `maas_tipi` - Maaş ödeme türü
  - `vardiya_sistemi` - Vardiya sistemi

#### 🔄 Model Geliştirmeleri
- **Personel Model** (`app/Models/Personel.php`)
  - Dinamik `create()` metodu
  - Esnek `update()` metodu
  - `getByKullaniciId()` metodu eklendi
  - Tüm yeni alanlar için destek

#### 🎛️ Controller Geliştirmeleri
- **ProfilController** (`app/Controllers/Kullanici/ProfilController.php`)
  - Kullanıcı ID ile personel verisi eşleştirme
  - Session kontrolleri
  - Hata yönetimi ve logging

### 🎨 UI/UX İyileştirmeleri

#### 📱 Responsive Tasarım
- Mobil uyumlu kart düzeni
- Bootstrap 5 grid sistemi
- Esnek form yapısı

#### 🎯 Kullanıcı Deneyimi
- Otomatik form validasyonu hazırlığı
- Input maskeleme desteği
- Placeholder metinler
- Form field'ları grouping

#### 🎨 Görsel Geliştirmeler
- Renkli kart sistemi (her kart farklı renk)
- Icon destekli etiketler
- Badge sistemleri
- Hover efektleri

### 🐛 Hata Düzeltmeleri
- **Undefined array key "durum"** hatası düzeltildi
- **Session management** iyileştirmeleri
- **Null pointer** kontrolleri eklendi
- **XSS protection** - `htmlspecialchars()` kullanımı

### 📁 Değiştirilen Dosyalar

```
app/Views/auth/giris.php              ← Giriş form geliştirmeleri
app/Views/kullanici/layout/navbar.php ← Kullanıcı menüsü
app/Views/kullanici/profil.php        ← Personel profil sistemi
app/Controllers/Kullanici/ProfilController.php ← Profil kontrolcüsü
app/Models/Personel.php               ← Personel model geliştirmesi
```

### 🔮 Gelecek Sürüm Planları (v1.3.0)
- [ ] Personel profil düzenleme sistemi
- [ ] Fotoğraf yükleme sistemi  
- [ ] PDF export özelliği
- [ ] E-imza sistemi
- [ ] Çalışan performans modülü
- [ ] İzin talep sistemi

---

## [v1.1.0] - 2024-12-20
### Temel Sistem
- İlk kurulum ve temel modüller

## [v1.0.0] - 2024-12-15  
### İlk Sürüm
- Temel proje kurulumu
- Kullanıcı giriş sistemi
- Temel dashboard