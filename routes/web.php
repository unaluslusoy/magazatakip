<?php
use core\Router;

// Router instance'ını kullan
global $router;

$router->get('', 'HomeController@index');
$router->get('auth/giris', 'Auth\GirisController@index');
$router->post('auth/giris', 'Auth\GirisController@login');
$router->get('auth/logout', 'Auth\GirisController@logout');
$router->post('auth/logout', 'Auth\GirisController@logout');
$router->get('auth/kayit', 'Auth\KayitController@index');
$router->post('auth/kayit', 'Auth\KayitController@register');
$router->get('anasayfa', 'AnasayfaController@index');
$router->get('istek/olustur', 'IstekController@olustur');
$router->post('istek/olustur', 'IstekController@olustur');
$router->get('istekler', 'IstekController@liste');

// Kullanıcı İş Emri Rotaları
$router->get('isemri/olustur', 'Kullanici\IsEmri\IsEmriController@olustur');
$router->post('isemri/olustur', 'Kullanici\IsEmri\IsEmriController@olustur');
$router->get('isemri/listesi', 'Kullanici\IsEmri\IsEmriController@listesi');
$router->get('isemri/duzenle/{id}', 'Kullanici\IsEmri\IsEmriController@duzenle');
$router->post('isemri/duzenle/{id}', 'Kullanici\IsEmri\IsEmriController@duzenle');
$router->get('isemri/sil/{id}', 'Kullanici\IsEmri\IsEmriController@sil');

// Kullanıcı Ciro Rotaları
$router->get('ciro/ekle', 'Kullanici\Ciro\CiroController@ekle');
$router->post('ciro/ekle', 'Kullanici\Ciro\CiroController@ekle');
$router->get('ciro/listele', 'Kullanici\Ciro\CiroController@listele');
$router->get('ciro/listele/refresh', 'Kullanici\Ciro\CiroController@listeleRefresh');
$router->get('ciro/duzenle/{id}', 'Kullanici\Ciro\CiroController@duzenle');
$router->post('ciro/duzenle/{id}', 'Kullanici\Ciro\CiroController@duzenle');
$router->get('ciro/sil/{id}', 'Kullanici\Ciro\CiroController@sil');

// API Test ve Dokümantasyon Rotaları
$router->get('ciro/api-test', 'Kullanici\Ciro\CiroController@apiTest');
$router->get('api-docs', 'Kullanici\Ciro\CiroController@apiDocs');

// Kullanıcı Profil Rotaları
$router->get('profil', 'Kullanici\ProfilController@index');
$router->get('kullanici/profil', 'Kullanici\ProfilController@index');
$router->post('kullanici/profil/guncelle', 'Kullanici\ProfilController@guncelle');
$router->post('kullanici/profil/sifre-degistir', 'Kullanici\ProfilController@sifreDegistir');

// Kullanıcı Bildirim Routes
$router->get('kullanici/bildirimler', 'Kullanici\BildirimController@index');
$router->get('kullanici/bildirimler/detay/(:num)', 'Kullanici\BildirimController@detay');
$router->post('kullanici/bildirimler/okundu/(:num)', 'Kullanici\BildirimController@markAsReadAjax');
$router->get('kullanici/bildirimler/unread-count', 'Kullanici\BildirimController@getUnreadCount');

// Token Rotaları
$router->post('/token/kaydet', 'Kullanici\TokenController@kaydet');
$router->get('/token/onesignal-config', 'Kullanici\TokenController@getOneSignalConfig');

// Fatura Talep Rotaları
$router->get('fatura_talep/olustur', 'Kullanici\FaturaTalep\FaturaTalepController@olustur');
$router->post('fatura_talep/olustur', 'Kullanici\FaturaTalep\FaturaTalepController@olustur');
$router->get('fatura_talep/listesi', 'Kullanici\FaturaTalep\FaturaTalepController@listesi');
$router->get('fatura_talep/duzenle/{id}', 'Kullanici\FaturaTalep\FaturaTalepController@duzenle');
$router->post('fatura_talep/duzenle/{id}', 'Kullanici\FaturaTalep\FaturaTalepController@duzenle');
$router->post('fatura_talep/sil/{id}', 'Kullanici\FaturaTalep\FaturaTalepController@sil');

// Gider Rotaları
$router->get('gider/listesi', 'Kullanici\GiderController@listesi');
$router->get('gider/ekle', 'Kullanici\GiderController@ekle');
$router->post('gider/ekle', 'Kullanici\GiderController@ekle');
$router->get('gider/duzenle/{id}', 'Kullanici\GiderController@duzenle');
$router->post('gider/duzenle/{id}', 'Kullanici\GiderController@duzenle');
$router->get('gider/sil/{id}', 'Kullanici\GiderController@sil');

// Diğer kullanıcı rotaları devam eder