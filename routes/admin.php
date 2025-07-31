<?php
use core\Router;

$router->get('admin', 'Admin\AnasayfaController@index');
$router->get('admin/giris', 'Auth\GirisController@login');
$router->post('admin/giris', 'Auth\GirisController@loginPost');
$router->get('admin/logout', 'Auth\GirisController@logout');



$router->get('admin/magazalar', 'Admin\MagazaController@liste');
$router->get('admin/magaza/ekle', 'Admin\MagazaController@ekle');
$router->post('admin/magaza/ekle', 'Admin\MagazaController@ekle');
$router->get('admin/magaza/guncelle/{id}', 'Admin\MagazaController@guncelle');
$router->post('admin/magaza/guncelle/{id}', 'Admin\MagazaController@guncelle');
$router->get('admin/magaza/sil/{id}', 'Admin\MagazaController@sil');

$router->get('admin/kullanici_ekle', 'Admin\KullaniciController@getMagazalar');

$router->get('admin/kullanicilar', 'Admin\KullaniciController@index');
$router->get('admin/kullanici_ekle', 'Admin\KullaniciController@create');
$router->post('admin/kullanici/store', 'Admin\KullaniciController@store');
$router->get('admin/kullanici/duzenle/{id}', 'Admin\KullaniciController@edit');
$router->post('admin/kullanici/update/{id}', 'Admin\KullaniciController@update');
$router->get('admin/kullanici/sil/{id}', 'Admin\KullaniciController@delete');

$router->get('admin/istekler', 'Admin\IstekController@liste');
$router->get('admin/istek/guncelle/{id}', 'Admin\IstekController@guncelle');
$router->post('admin/istek/guncelle/{id}', 'Admin\IstekController@guncelle');
$router->post('admin/istek/ekle', 'Admin\IstekController@ekle');
$router->get('/admin/istek/sil/{id}', 'Admin\IstekController@sil');
$router->post('/admin/istek/sil/{id}', 'Admin\IstekController@sil');



$router->get('admin/personel/detay/{id}', 'Admin\PersonelController@detay');
$router->get('admin/personeller', 'Admin\PersonelController@liste');
$router->get('admin/personel/ekle', 'Admin\PersonelController@ekle');
$router->post('admin/personel/ekle', 'Admin\PersonelController@ekle');
$router->get('admin/personel/guncelle/{id}', 'Admin\PersonelController@guncelle');
$router->post('admin/personel/guncelle/{id}', 'Admin\PersonelController@guncelle');
$router->get('admin/personel/sil/{id}', 'Admin\PersonelController@sil');

$router->get('admin/geri_bildirimler', 'Admin\GeriBildirimController@index');
$router->get('admin/geri_bildirimler/ekle', 'Admin\GeriBildirimController@ekle');
$router->post('admin/geri_bildirimler/ekle', 'Admin\GeriBildirimController@ekle');
$router->get('admin/geri_bildirimler/detay/{id}', 'Admin\GeriBildirimController@detay');
$router->get('admin/geri_bildirimler/sil/{id}', 'Admin\GeriBildirimController@sil');
$router->post('admin/geri_bildirimler/guncelleDurum', 'Admin\GeriBildirimController@guncelleDurum');

// OneSignal rotaları
$router->get('/admin/onesignal/ayarlar', 'Admin\OneSignalController@ayarlar');
$router->post('/admin/onesignal/kaydet', 'Admin\OneSignalController@kaydet');

$router->post('/token/kaydet', 'TokenController@kaydet');
$router->get('/token/onesignal-config', 'TokenController@getOneSignalConfig');

$router->get('/admin/bildirimler', 'Admin\BildirimController@index');
$router->get('/admin/bildirim_gonder', 'Admin\BildirimController@bildirimiGonderForm');
$router->post('/admin/bildirim_gonder', 'Admin\BildirimController@bildirimiGonder');
$router->get('/admin/bildirimler/sil/(:num)', 'Admin\BildirimController@delete');
$router->get('/admin/bildirimler/okundu/(:num)', 'Admin\BildirimController@markAsRead');

$router->get('admin/fatura_talep/listesi', 'Admin\FaturaTalepController@listesi');
$router->get('admin/fatura_talep/duzenle/{id}', 'Admin\FaturaTalepController@duzenle');
$router->post('admin/fatura_talep/duzenle/{id}', 'Admin\FaturaTalepController@duzenle');
$router->get('admin/fatura_talep/sil/{id}', 'Admin\FaturaTalepController@sil');
$router->post('admin/fatura_talep/sil/{id}', 'Admin\FaturaTalepController@sil');


$router->get('admin/fatura_talep/getFaturaDetails/{id}', 'Admin\FaturaTalepController@getFaturaDetails');
$router->post('admin/fatura_talep/sendWhatsapp', 'Admin\FaturaTalepController@sendWhatsapp');
