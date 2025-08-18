<?php
use core\Router;

// Router instance'ını kullan
global $router;

$router->get('admin', 'Admin\AnasayfaController@index');
$router->get('admin/giris', 'Auth\GirisController@login');
$router->post('admin/giris', 'Auth\GirisController@loginPost');
$router->get('admin/logout', 'Auth\GirisController@logout');
$router->post('admin/logout', 'Auth\GirisController@logout');



$router->get('admin/magazalar', 'Admin\MagazaController@liste');
$router->get('admin/magaza/ekle', 'Admin\MagazaController@ekle');
$router->post('admin/magaza/ekle', 'Admin\MagazaController@ekle');
$router->get('admin/magaza/guncelle/{id}', 'Admin\MagazaController@guncelle');
$router->post('admin/magaza/guncelle/{id}', 'Admin\MagazaController@guncelle');
$router->get('admin/magaza/sil/{id}', 'Admin\MagazaController@sil');

$router->get('admin/kullanici_ekle', 'Admin\KullaniciController@getMagazalar');

$router->get('admin/kullanicilar', 'Admin\KullaniciController@index');
$router->get('admin/kullanici_ekle', function() {
    require_once 'app/Views/admin/kullanici_ekle.php';
});
$router->post('admin/kullanici/store', 'Admin\KullaniciController@store');
$router->get('admin/kullanici/duzenle/{id}', function() {
    require_once 'app/Views/admin/kullanici_duzenle.php';
});
$router->post('admin/kullanici/update/{id}', 'Admin\KullaniciController@update');
$router->get('admin/kullanici/sil/{id}', 'Admin\KullaniciController@delete');

// API Routes for AJAX calls
$router->get('admin/kullanici/api-get/{id}', 'Admin\KullaniciController@apiGet');
$router->post('admin/kullanici/api-update/{id}', 'Admin\KullaniciController@apiUpdate');
$router->post('admin/kullanici/api-create', 'Admin\KullaniciController@apiCreate');
$router->get('admin/kullanicilar/api-list', 'Admin\KullaniciController@apiList');
$router->delete('admin/kullanici/api-delete/{id}', 'Admin\KullaniciController@apiDelete');

$router->get('admin/istekler', 'Admin\IstekController@liste');
$router->get('admin/istek/guncelle/{id}', 'Admin\IstekController@guncelle');
$router->post('admin/istek/guncelle/{id}', 'Admin\IstekController@guncelle');
$router->post('admin/istek/ekle', 'Admin\IstekController@ekle');
$router->get('/admin/istek/sil/{id}', 'Admin\IstekController@sil');
$router->post('/admin/istek/sil/{id}', 'Admin\IstekController@sil');



$router->get('admin/personel/detay/{id}', 'Admin\PersonelController@detay');
$router->get('admin/personeller', function() {
    require_once 'app/Views/admin/personeller.php';
});
$router->get('admin/personel_ekle', function() {
    require_once 'app/Views/admin/personel_ekle.php';
});
$router->get('admin/personel/guncelle/{id}', function() {
    require_once 'app/Views/admin/personel_guncelle.php';
});
$router->post('admin/personel/guncelle/{id}', 'Admin\PersonelController@guncelle');
$router->post('admin/personel/ekle', 'Admin\PersonelController@ekle');
$router->get('admin/personel/sil/{id}', 'Admin\PersonelController@sil');

// Personel API Routes
$router->get('admin/personel/liste-json', 'Admin\PersonelController@listeJson');
$router->get('admin/personel/api-get/{id}', 'Admin\PersonelController@apiGet');
$router->post('admin/personel/api-update/{id}', 'Admin\PersonelController@apiUpdate');
$router->post('admin/personel/api-create', 'Admin\PersonelController@apiCreate');
$router->get('admin/personeller/api-list', 'Admin\PersonelController@apiList');
$router->delete('admin/personel/api-delete/{id}', 'Admin\PersonelController@apiDelete');

// Timeline Routes
$router->get('admin/timeline', 'Admin\TimelineController@index');
$router->get('admin/timeline/emergency', 'Admin\TimelineController@emergency');
$router->get('api/timeline/list', 'Admin\TimelineController@apiList');
$router->post('api/timeline/create', 'Admin\TimelineController@apiCreate');
$router->post('api/timeline/rollback', 'Admin\TimelineController@apiRollback');
$router->post('api/timeline/auto-backup', 'Admin\TimelineController@apiAutoBackup');

$router->get('admin/geri_bildirimler', 'Admin\GeriBildirimController@index');
$router->get('admin/geri_bildirimler/ekle', 'Admin\GeriBildirimController@ekle');
$router->post('admin/geri_bildirimler/ekle', 'Admin\GeriBildirimController@ekle');
$router->get('admin/geri_bildirimler/detay/{id}', 'Admin\GeriBildirimController@detay');
$router->get('admin/geri_bildirimler/sil/{id}', 'Admin\GeriBildirimController@sil');
$router->post('admin/geri_bildirimler/guncelleDurum', 'Admin\GeriBildirimController@guncelleDurum');

// Site ayarları rotaları
$router->get('/admin/site-ayarlar', 'Admin\SiteAyarlarController@index');
$router->post('/admin/site-ayarlar/kaydet', 'Admin\SiteAyarlarController@siteAyarlarKaydet');
$router->post('/admin/site-ayarlar/genel-kaydet', 'Admin\SiteAyarlarController@siteAyarlarKaydet');
$router->post('/admin/site-ayarlar/iletisim-kaydet', 'Admin\SiteAyarlarController@siteAyarlarKaydet');
$router->post('/admin/site-ayarlar/sosyal-kaydet', 'Admin\SiteAyarlarController@siteAyarlarKaydet');
$router->post('/admin/site-ayarlar/onesignal-kaydet', 'Admin\SiteAyarlarController@oneSignalKaydet');
$router->post('/admin/site-ayarlar/mail-kaydet', 'Admin\SiteAyarlarController@mailKaydet');
$router->post('/admin/site-ayarlar/mail-test', 'Admin\SiteAyarlarController@mailTestGonder');
// Log ayarları
$router->post('/admin/site-ayarlar/log-kaydet', 'Admin\SiteAyarlarController@logAyarKaydet');
// Rate limit ayarları
$router->post('/admin/site-ayarlar/rate-limit-kaydet', 'Admin\SiteAyarlarController@rateLimitKaydet');

// Cloudflare paneli
$router->get('/admin/cloudflare', 'Admin\\CloudflareController@index');
$router->post('/admin/cloudflare/save', 'Admin\\CloudflareController@save');
$router->post('/admin/cloudflare/devmode', 'Admin\\CloudflareController@devModeToggle');
$router->post('/admin/cloudflare/purge', 'Admin\\CloudflareController@purge');
$router->post('/admin/cloudflare/dns-create', 'Admin\\CloudflareController@dnsCreate');
$router->post('/admin/cloudflare/dns-delete', 'Admin\\CloudflareController@dnsDelete');
$router->post('/admin/cloudflare/ssl-set', 'Admin\\CloudflareController@sslSet');

// Bildirim rotaları
$router->get('/admin/bildirimler', 'Admin\BildirimController@index');
$router->get('/admin/bildirimler/ekle', 'Admin\BildirimController@ekle');
$router->post('/admin/bildirimler/ekle', 'Admin\BildirimController@eklePost');
$router->get('/admin/bildirimler/duzenle/{id}', 'Admin\BildirimController@duzenle');
$router->post('/admin/bildirimler/duzenle/{id}', 'Admin\BildirimController@duzenlePost');
$router->delete('/admin/bildirimler/sil/{id}', 'Admin\BildirimController@sil');
$router->post('/admin/bildirimler/toplu-gonder', 'Admin\BildirimController@topluGonder');

$router->post('/token/kaydet', 'TokenController@kaydet');
$router->get('/token/onesignal-config', 'TokenController@getOneSignalConfig');

$router->get('/admin/bildirimler', 'Admin\BildirimController@index');
$router->get('/admin/bildirim_gonder', 'Admin\BildirimController@bildirimiGonderForm');
$router->post('/admin/bildirim_gonder', 'Admin\BildirimController@bildirimiGonder');
$router->get('/admin/bildirimler/sil/(:num)', 'Admin\BildirimController@delete');
$router->get('/admin/bildirimler/okundu/(:num)', 'Admin\BildirimController@markAsRead');
$router->get('/admin/bildirimler/detay/(:num)', 'Admin\BildirimController@detay');

// Tamsoft modülü kaldırıldı

// Sadece stok entegrasyonu
$router->get('/admin/tamsoft-stok', 'Admin\\TamsoftStockController@index');
$router->get('/admin/tamsoft-stok/summary', 'Admin\\TamsoftStockController@dashboardSummary');
$router->get('/admin/tamsoft-stok/ayarlar', 'Admin\\TamsoftStockController@ayarlar');
$router->post('/admin/tamsoft-stok/ayarlar', 'Admin\\TamsoftStockController@ayarlarPost');
$router->get('/admin/tamsoft-stok/envanter', 'Admin\\TamsoftStockController@inventory');
$router->get('/admin/tamsoft-stok/envanter/data', 'Admin\\TamsoftStockController@inventoryData');
$router->get('/admin/tamsoft-stok/envanter/export', 'Admin\\TamsoftStockController@inventoryExport');
$router->post('/admin/tamsoft-stok/envanter/map-save', 'Admin\\TamsoftStockController@mapSave');
$router->post('/admin/tamsoft-stok/refresh', 'Admin\\TamsoftStockController@refresh');
$router->post('/admin/tamsoft-stok/token-test', 'Admin\\TamsoftStockController@tokenTest');
$router->post('/admin/tamsoft-stok/depolar/sync', 'Admin\\TamsoftStockController@depolarSync');
$router->post('/admin/tamsoft-stok/depolar/refresh-parallel', 'Admin\\TamsoftStockController@depolarRefreshParallel');
$router->get('/admin/tamsoft-stok/depolar/preview', 'Admin\\TamsoftStockController@depolarPreview');
$router->post('/admin/tamsoft-stok/stok/preview', 'Admin\\TamsoftStockController@stokPreview');
$router->get('/admin/tamsoft-stok/ecommerce/preview', 'Admin\\TamsoftStockController@ecommercePreview');
$router->post('/admin/tamsoft-stok/price-refresh', 'Admin\\TamsoftStockController@priceRefresh');
$router->post('/admin/tamsoft-stok/cron/stock-sync', 'Admin\\TamsoftStockController@cronStockSync');
$router->post('/admin/tamsoft-stok/cron/monthly-master', 'Admin\\TamsoftStockController@cronMonthlyMaster');
$router->post('/admin/tamsoft-stok/cron/ecommerce-stock', 'Admin\\TamsoftStockController@cronEcommerceStock');
$router->get('/admin/tamsoft-stok/depolar', 'Admin\\TamsoftStockController@depolarPage');
$router->get('/admin/tamsoft-stok/depolar/data', 'Admin\\TamsoftStockController@depolarData');
$router->post('/admin/tamsoft-stok/depolar/set-active', 'Admin\\TamsoftStockController@depolarSetActive');

// Import (manuel)
$router->get('/admin/tamsoft-stok/import', 'Admin\\TamsoftStockController@import');
$router->post('/admin/tamsoft-stok/import', 'Admin\\TamsoftStockController@importRun');

// Trendyol Go

// Job Manager (Tamsoft)
$router->get('/admin/tamsoft-stok/jobs', 'Admin\\TamsoftStockController@jobsPage');
$router->get('/admin/tamsoft-stok/jobs/list', 'Admin\\TamsoftStockController@jobsList');
$router->get('/admin/tamsoft-stok/jobs/runs', 'Admin\\TamsoftStockController@jobsRuns');
$router->get('/admin/tamsoft-stok/cron/list', 'Admin\\TamsoftStockController@cronList');
$router->post('/admin/tamsoft-stok/jobs/run', 'Admin\\TamsoftStockController@jobsRun');
$router->post('/admin/tamsoft-stok/jobs/toggle', 'Admin\\TamsoftStockController@jobsToggle');
$router->post('/admin/tamsoft-stok/jobs/lock/release', 'Admin\\TamsoftStockController@jobsLockRelease');
$router->post('/admin/tamsoft-stok/jobs/create', 'Admin\\TamsoftStockController@jobsCreate');
$router->post('/admin/tamsoft-stok/jobs/delete', 'Admin\\TamsoftStockController@jobsDelete');
$router->get('/admin/tamsoft-stok/queue/summary', 'Admin\\TamsoftStockController@queueSummary');
$router->post('/admin/tamsoft-stok/jobs/schedule-now', 'Admin\\TamsoftStockController@jobsScheduleNow');

// Ürün Eşleştirme (Faz 1 - kural+bulanık)
$router->get('/admin/match', 'Admin\\ProductMatchController@page');
$router->get('/admin/match/stores', 'Admin\\ProductMatchController@storesPage');
$router->get('/admin/match/stores/list', 'Admin\\ProductMatchController@storesList');
$router->post('/admin/match/stores/save', 'Admin\\ProductMatchController@storesSave');
$router->post('/admin/match/trendyolgo/run', 'Admin\\ProductMatchController@runTrendyolGo');
$router->get('/admin/match/trendyolgo/suggestions', 'Admin\\ProductMatchController@suggestionsTrendyolGo');
$router->post('/admin/match/approve', 'Admin\\ProductMatchController@approve');
$router->post('/admin/match/push-single', 'Admin\\ProductMatchController@pushSingle');
$router->get('/admin/match/tamsoft/list', 'Admin\\ProductMatchController@tamsoftList');
$router->get('/admin/match/trendyol/list', 'Admin\\ProductMatchController@trendyolList');
// Analiz ve toplu onay
$router->get('/admin/match/trendyol/analyze', 'Admin\\ProductMatchController@analyzeTrendyolGo');
$router->get('/admin/match/trendyol/preview-single', 'Admin\\ProductMatchController@previewSingle');
$router->post('/admin/match/approve-batch', 'Admin\\ProductMatchController@approveBatch');
$router->get('/admin/trendyolgo', 'Admin\\TrendyolGoController@index');
$router->get('/admin/trendyolgo/ayarlar', 'Admin\\TrendyolGoController@ayarlar');
$router->post('/admin/trendyolgo/ayarlar', 'Admin\\TrendyolGoController@ayarlar');
$router->get('/admin/trendyolgo/urunler', 'Admin\\TrendyolGoController@urunler');
$router->get('/admin/trendyolgo/urunler/data', 'Admin\\TrendyolGoController@urunlerData');
$router->get('/admin/trendyolgo/eslesmeler', 'Admin\\TrendyolGoController@eslesmeler');
$router->get('/admin/trendyolgo/eslesmeler/data', 'Admin\\TrendyolGoController@eslesmelerData');
$router->post('/admin/trendyolgo/eslesmeler/update', 'Admin\\TrendyolGoController@eslesmelerUpdate');
$router->post('/admin/trendyolgo/eslesmeler/delete', 'Admin\\TrendyolGoController@eslesmelerDelete');
$router->get('/admin/trendyolgo/magazalar', 'Admin\\TrendyolGoController@magazalar');
$router->post('/admin/trendyolgo/magazalar', 'Admin\\TrendyolGoController@magazalar');
$router->get('/admin/trendyolgo/magaza/sil/{id}', 'Admin\\TrendyolGoController@magazaSil');
// Şube bazlı stok sayfası
$router->get('/admin/trendyolgo/stoklar', 'Admin\\TrendyolGoController@stoklar');
$router->get('/admin/trendyolgo/stoklar/data', 'Admin\\TrendyolGoController@stoklarData');
// Ürün içe aktarma job tetikleme ve durum sorgu
$router->post('/admin/trendyolgo/urunler/import-trigger', 'Admin\\TrendyolGoController@urunImportTrigger');
$router->get('/admin/trendyolgo/urunler/import-status/{id}', 'Admin\\TrendyolGoController@urunImportStatus');
// Sağlık testi ve yeni sayfalar
$router->get('/admin/trendyolgo/health', 'Admin\\TrendyolGoController@healthCheck');
$router->get('/admin/trendyolgo/siparisler', 'Admin\\TrendyolGoController@siparisler');
$router->get('/admin/trendyolgo/iptaller', 'Admin\\TrendyolGoController@iptaller');
$router->get('/admin/trendyolgo/loglar', 'Admin\\TrendyolGoController@loglar');
$router->post('/admin/trendyolgo/loglar/temizle', 'Admin\\TrendyolGoController@logTemizle');
$router->post('/admin/trendyolgo/siparis/durum', 'Admin\\TrendyolGoController@siparisDurumGuncelle');
$router->get('/admin/trendyolgo/diagnostic', 'Admin\\TrendyolGoController@diagnostic');
$router->get('/admin/trendyolgo/diagnostic-stores', 'Admin\\TrendyolGoController@diagnosticStores');
$router->post('/admin/trendyolgo/urunler/import', 'Admin\\TrendyolGoController@urunleriIceriAl');
$router->post('/admin/trendyolgo/cron/import-all', 'Admin\\TrendyolGoController@cronImportAll');

// GetirÇarşı
$router->get('/admin/getir', 'Admin\\GetirController@index');
$router->get('/admin/getir/ayarlar', 'Admin\\GetirController@ayarlar');
$router->post('/admin/getir/ayarlar', 'Admin\\GetirController@ayarlar');
$router->post('/admin/getir/ayarlar/generate-webhook-key', 'Admin\\GetirController@generateWebhookKey');
$router->get('/admin/getir/loglar', 'Admin\\GetirController@loglar');
$router->post('/admin/getir/loglar/temizle', 'Admin\\GetirController@logTemizle');

// Activity Logs
$router->get('admin/activity-logs', 'Admin\\ActivityLogController@index');

// OneSignal Test Routes
$router->get('/admin/onesignal-test', 'Admin\OneSignalTestController@index');
$router->post('/admin/onesignal-test/gonder', 'Admin\OneSignalTestController@testGonder');

$router->get('admin/fatura_talep/listesi', 'Admin\FaturaTalepController@listesi');
$router->get('admin/fatura_talep/duzenle/{id}', 'Admin\FaturaTalepController@duzenle');
$router->post('admin/fatura_talep/duzenle/{id}', 'Admin\FaturaTalepController@duzenle');
$router->get('admin/fatura_talep/sil/{id}', 'Admin\FaturaTalepController@sil');
$router->post('admin/fatura_talep/sil/{id}', 'Admin\FaturaTalepController@sil');


$router->get('admin/fatura_talep/getFaturaDetails/{id}', 'Admin\FaturaTalepController@getFaturaDetails');
$router->post('admin/fatura_talep/sendWhatsapp', 'Admin\FaturaTalepController@sendWhatsapp');
