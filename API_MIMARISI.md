# Magaza Takip Sistemi - API Mimarisi

## Genel Bakış

Bu sistem, PHP tabanlı bir web uygulaması olup, hem web arayüzü hem de RESTful API sunmaktadır. Sistem MVC (Model-View-Controller) mimarisi kullanmaktadır.

## Sistem Mimarisi

### 1. Ana Dizin Yapısı
```
/
├── index.php              # Ana giriş noktası
├── .htaccess              # URL rewriting kuralları
├── api/                   # API endpoint'leri
├── app/                   # Uygulama kodları
│   ├── Controllers/       # Controller'lar
│   ├── Models/           # Veri modelleri
│   ├── Views/            # Görünüm dosyaları
│   ├── Middleware/       # Ara katman işlemleri
│   └── Services/         # İş mantığı servisleri
├── core/                 # Çekirdek sistem dosyaları
├── config/               # Konfigürasyon dosyaları
├── routes/               # Rota tanımlamaları
└── vendor/               # Composer bağımlılıkları
```

### 2. Routing Sistemi

#### Web Routes (`routes/web.php`)
- **Ana Sayfa**: `/` → `HomeController@index`
- **Giriş**: `/auth/giris` → `Auth\GirisController@index`
- **Kayıt**: `/auth/kayit` → `Auth\KayitController@index`
- **Anasayfa**: `/anasayfa` → `AnasayfaController@index`

#### API Routes (`routes/api.php`)
- **Auth**: `/api/auth/*` → `Api\AuthController@*`
- **Kullanıcı**: `/api/user/*` → `Api\UserApiController@*`
- **Ciro**: `/api/ciro/*` → `Api\CiroApiController@*`
- **Gider**: `/api/gider/*` → `Api\GiderApiController@*`
- **İş Emri**: `/api/is-emri/*` → `Api\IsEmriApiController@*`
- **Bildirim**: `/api/bildirim/*` → `Api\BildirimApiController@*`

### 3. API Endpoint'leri

#### Authentication API
```
POST /api/auth/login     # Kullanıcı girişi
POST /api/auth/logout    # Kullanıcı çıkışı
GET  /api/auth/me        # Mevcut kullanıcı bilgileri
```

#### User API
```
GET  /api/user/profile           # Kullanıcı profili
PUT  /api/user/profile           # Profil güncelleme
PUT  /api/user/password          # Şifre değiştirme
GET  /api/user/dashboard-stats   # Dashboard istatistikleri
GET  /api/user/system-status     # Sistem durumu
```

#### Ciro API
```
GET    /api/ciro/liste           # Ciro listesi
GET    /api/ciro/{id}            # Tek ciro detayı
POST   /api/ciro/ekle            # Yeni ciro ekleme
PUT    /api/ciro/guncelle/{id}   # Ciro güncelleme
DELETE /api/ciro/sil/{id}        # Ciro silme
```

#### Gider API
```
GET    /api/gider/liste          # Gider listesi
GET    /api/gider/{id}           # Tek gider detayı
POST   /api/gider/ekle           # Yeni gider ekleme
PUT    /api/gider/guncelle/{id}  # Gider güncelleme
DELETE /api/gider/sil/{id}       # Gider silme
GET    /api/gider/stats          # Gider istatistikleri
```

#### İş Emri API
```
GET    /api/is-emri/liste        # İş emri listesi
GET    /api/is-emri/{id}         # Tek iş emri detayı
POST   /api/is-emri/olustur      # Yeni iş emri oluşturma
PUT    /api/is-emri/guncelle/{id} # İş emri güncelleme
DELETE /api/is-emri/sil/{id}     # İş emri silme
PUT    /api/is-emri/durum/{id}   # İş emri durumu güncelleme
GET    /api/is-emri/stats        # İş emri istatistikleri
```

#### Bildirim API
```
GET    /api/bildirim/liste           # Bildirim listesi
GET    /api/bildirim/{id}            # Tek bildirim detayı
PUT    /api/bildirim/okundu/{id}     # Bildirimi okundu işaretleme
PUT    /api/bildirim/tumunu-okundu   # Tüm bildirimleri okundu işaretleme
DELETE /api/bildirim/sil/{id}        # Bildirim silme
GET    /api/bildirim/okunmamis-sayi  # Okunmamış bildirim sayısı
GET    /api/bildirim/stats           # Bildirim istatistikleri
```

### 4. Veri Tabanı Yapısı

#### Ana Tablolar
- **kullanicilar**: Kullanıcı bilgileri
- **magazalar**: Mağaza bilgileri
- **cirolar**: Ciro kayıtları
- **giderler**: Gider kayıtları
- **gorevler**: İş emirleri
- **bildirimler**: Bildirimler
- **personeller**: Personel bilgileri

### 5. Güvenlik Katmanları

#### Authentication
- Session tabanlı kimlik doğrulama
- Remember token desteği
- Session timeout kontrolü
- IP bazlı güvenlik

#### Authorization
- Role-based access control (RBAC)
- Route bazlı erişim kontrolü
- Admin/User ayrımı

#### Middleware
- **AuthMiddleware**: Kimlik doğrulama
- **AdminMiddleware**: Admin erişim kontrolü
- **ApiAuthMiddleware**: API kimlik doğrulama

### 6. Cache Sistemi

#### Redis Cache
- Kullanıcı verileri cache'leme
- Session cache'leme
- Query cache'leme
- File cache fallback

#### Cache Manager
- Otomatik cache temizleme
- TTL (Time To Live) yönetimi
- Cache istatistikleri

### 7. Hata Yönetimi

#### HTTP Status Codes
- **200**: Başarılı
- **201**: Oluşturuldu
- **400**: Hatalı istek
- **401**: Yetkisiz erişim
- **404**: Bulunamadı
- **500**: Sunucu hatası

#### Error Response Format
```json
{
    "success": false,
    "message": "Hata mesajı",
    "timestamp": 1640995200,
    "error_code": "ERROR_CODE"
}
```

### 8. Response Format

#### Success Response
```json
{
    "success": true,
    "data": {...},
    "message": "İşlem başarılı",
    "timestamp": 1640995200
}
```

#### List Response
```json
{
    "success": true,
    "data": [...],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "total_pages": 5
    },
    "message": "Veriler başarıyla getirildi",
    "timestamp": 1640995200
}
```

### 9. Performans Optimizasyonları

#### Database
- Connection pooling
- Query cache
- Slow query logging
- Index optimizasyonu

#### Cache
- Redis cache
- File cache fallback
- Cache warming
- Cache invalidation

#### File System
- Gzip compression
- Image optimization
- CSS/JS minification
- CDN desteği

### 10. Monitoring ve Logging

#### Logging
- Error logging
- Access logging
- Performance logging
- Security logging

#### Monitoring
- System health checks
- Database connection monitoring
- Cache performance monitoring
- API response time monitoring

## Sorun Tespiti ve Çözümler

### Tespit Edilen Sorunlar

1. **Phalcon5.so Hatası**: PHP 8.4 ile uyumsuzluk
2. **Headers Already Sent**: CacheManager'da output
3. **REQUEST_URI Undefined**: CLI'da çalıştırma sorunu

### Uygulanan Çözümler

1. **CacheManager Düzeltmesi**: Output temizlendi
2. **Index.php Güvenliği**: REQUEST_URI kontrolü eklendi
3. **Router CLI Desteği**: CLI için varsayılan değerler
4. **View Güvenliği**: Headers sent kontrolü

### Sistem Durumu

✅ **Sistem Yükleme**: Başarılı  
✅ **Veritabanı Bağlantısı**: Aktif  
✅ **Redis Cache**: Aktif  
✅ **Routing Sistemi**: Çalışıyor  
✅ **API Endpoint'leri**: Hazır  

## Sonuç

Sistem başarıyla çalışır durumda. API mimarisi RESTful standartlara uygun olarak tasarlanmış ve güvenlik katmanları ile korunmaktadır. Performans optimizasyonları ve cache sistemi ile hızlı yanıt süreleri sağlanmaktadır.
