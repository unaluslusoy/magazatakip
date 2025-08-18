# Tamsoft Stok Modülü - Kullanım ve Geliştirici Dokümanı

Bu doküman Tamsoft stok modülünün kullanımı, ayarları, API uçları ve geliştiriciye yönelik teknik bilgileri içerir.

## 1) Kullanıcı Arayüzü

- Yönetim menüsü: `Yönetim > Tamsoft Stok`
- Üst sekmeler: `Pano`, `Envanter`, `Depolar`, `Ayarlar`, `İşler`

### Pano
- Ürün/Depo sayıları, son senkron bilgileri, kuyruk ve son işlem özetleri.
- Manuel fiyat güncelleme butonu.
- Debug butonu: Son sistem yanıtlarını modalde gösterir.

### Envanter
- Ürün listesi, depo bazlı miktarlar, filtreleme ve CSV dışa aktarma.

### Depolar
- Depo listesi, aktif/pasif işaretleme.
- Senkronizasyon sonrası önizleme.

### Ayarlar
- API adresi ve kimlik bilgileri.
- Parametreler: tarih, depo, “sadece stoklu”, “son barkod”, “sadece e-ticaret”.
- Gelişmiş (katlanır alan): toplu/performans parametreleri ve zamanlama.
- Debug butonu ile işlem sonuçları modalde görüntülenir.

### İşler (Job Manager)
- Cron ifadeleriyle işler, durum ve geçmiş.
- “Due” olan işleri kuyruğa al butonu.
- Debug butonu: Son iş çıktıları modali.

## 2) Temel İş Akışları

- Stok Senkronu: Ayarlara göre Tamsoft’tan sayfalı okuma ve veritabanına yazma.
- Fiyat Güncelleme: Yalnız fiyat alanlarını günceller.
- Depo Senkronu: Depo listesini günceller, depo bazlı miktarları eşitler.

## 3) Ayarlar (Alanlar ve Anlamları)

- API URL: Tamsoft servis tabanı (ör: `http://...`).
- Kullanıcı / Şifre: Token alma için kimlik bilgileri.
- Varsayılan Tarih: Listeleme için başlangıç tarihi.
- Varsayılan Depo ID: Boş ise tüm depolar veya varsayılan strateji.
- Sadece stoklu ürünleri getir: Miktarı 0 üstü olanları getir.
- Sadece son barkodu getir: Ürünün son barkodunu dikkate al.
- Sadece e-ticaret ürünlerini getir: Yalnız e-ticaret işaretli ürünler.
- Senkron aktif: Otomatik akışlar açıksa kullanılır.
- Depo bazlı çekim: Depo-depo gezinerek çek (zaman kutusu ile).
- İstek aralığı (sn): Otomatik çağrılar için önerilen bekleme süresi.
- Gelişmiş: Toplu işleme boyutları, sayfa/ süre sınırları.

## 4) Panel Uçları (HTTP)

- Ayar kaydet: `POST /admin/tamsoft-stok/ayarlar`
- Stok senkron: `POST /admin/tamsoft-stok/refresh`
- Fiyat güncelle: `POST /admin/tamsoft-stok/price-refresh`
- Depo senkron: `POST /admin/tamsoft-stok/depolar/sync`
- Depo önizleme: `GET /admin/tamsoft-stok/depolar/preview`
- Stok önizleme: `POST /admin/tamsoft-stok/stok/preview`
- Pano özet: `GET /admin/tamsoft-stok/summary`
- Envanter verisi: `GET /admin/tamsoft-stok/envanter/data`
- Envanter dışa aktar: `GET /admin/tamsoft-stok/envanter/export`
- İşler: `GET/POST /admin/tamsoft-stok/jobs/*`

Tüm POST isteklerinde CSRF başlığı: `X-CSRF-Token` zorunludur.

## 5) Geliştirici Notları

- Servisler: `app/Services/Tamsoft/*Service.php`
- HTTP İstemci: `app/Http/TamsoftHttpClient.php`
- Hız Sınırlayıcı: `app/Utils/RateLimiter.php`
- Depo/Ürün/Özet veri erişimi: `app/Models/TamsoftStockRepo.php`
- Konfigürasyon: `app/Models/TamsoftStockConfig.php` (tablo: `tamsoft_stok_ayarlar`)
- Kuyruk ve zamanlayıcı:
  - Zamanlayıcı: `scripts/job_scheduler.php` (cron ile dakikada 1 önerilir)
  - İşçi: `scripts/queue_worker.php` (daemon olarak çalışır)
  - İş çizelgesi/çalışmaları: tablolar `job_schedule`, `job_runs`, `job_lock`

### Veritabanı Şeması (özet)

- `tamsoft_urunler`: Ürün ana tablo (fiyat, miktar toplamı, aktif)
- `tamsoft_urun_barkodlar`: Barkod ve birim/fiyat bilgileri
- `tamsoft_depolar`: Depo kimlik ve adları, aktiflik
- `tamsoft_depo_stok_ozet`: Ürün x Depo stok ve fiyat
- `tamsoft_stok_log`: Sistem logları
- `tamsoft_urunler_stage`, `tamsoft_urun_barkodlar_stage`: Geçici tablolara toplu aktarma

### Komutlar

- Migrasyon (keşif): `php scripts/tamsoft_migrate.php`
- Migrasyon (uygula): `php scripts/tamsoft_migrate.php --execute --skip-drops`
- Zamanlayıcı: `php scripts/job_scheduler.php`
- İşçi: `php scripts/queue_worker.php`

## 6) Sorun Giderme

- Token hatası: Ayarlar bölümünden kullanıcı/şifre ve API URL’yi doğrulayın, “Token Test” ile deneyin.
- Yavaşlık: Gelişmiş bölümde batch/limitleri düşürün; loglarda “page_*” girdilerine bakın.
- Çakışmalar: İşler sayfasından kilit kaldırabilirsiniz.

---
Bu doküman `docs/TAMSOFT_MODUL_KULLANIMI.md` konumunda tutulur. Güncel değişiklikleri buraya ekleyiniz.


