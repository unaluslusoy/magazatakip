<?php

use core\Router;

// API Authentication middleware için  
require_once 'app/Middleware/ApiAuthMiddleware.php';

$apiRouter = new Router();

// API base path: /api/*

// Auth endpoints
$apiRouter->post('api/auth/login', 'Api\AuthController@login');
$apiRouter->post('api/auth/logout', 'Api\AuthController@logout');
$apiRouter->get('api/auth/me', 'Api\AuthController@me');

// User management endpoints (Admin only)
$apiRouter->get('api/kullanicilar', 'Api\KullaniciController@index');
$apiRouter->get('api/kullanici/{id}', 'Api\KullaniciController@show');
$apiRouter->post('api/kullanici', 'Api\KullaniciController@create');
$apiRouter->put('api/kullanici/{id}', 'Api\KullaniciController@update');
$apiRouter->delete('api/kullanici/{id}', 'Api\KullaniciController@delete');

// Store endpoints
$apiRouter->get('api/magazalar', 'Api\MagazaController@index');
$apiRouter->get('api/magaza/{id}', 'Api\MagazaController@show');

// Personnel endpoints
$apiRouter->get('api/personeller', 'Api\PersonelController@index');
$apiRouter->get('api/personel/{id}', 'Api\PersonelController@show');
$apiRouter->put('api/personel/{personelId}/kullanici/{kullaniciId}', 'Api\PersonelController@updateKullaniciAtama');

// Kullanıcı API Rotaları
$apiRouter->get('api/user/profile', 'Api\UserApiController@getUserProfile');
$apiRouter->put('api/user/profile', 'Api\UserApiController@updateUserProfile');
$apiRouter->put('api/user/password', 'Api\UserApiController@changePassword');
$apiRouter->get('api/user/dashboard-stats', 'Api\UserApiController@getDashboardStats');
$apiRouter->get('api/user/system-status', 'Api\UserApiController@getSystemStatus');

// Ciro API Rotaları
$apiRouter->get('api/ciro/liste', 'Api\CiroApiController@getCiroListesi');
$apiRouter->get('api/ciro/{id}', 'Api\CiroApiController@getCiro');
$apiRouter->post('api/ciro/ekle', 'Api\CiroApiController@addCiro');
$apiRouter->put('api/ciro/guncelle/{id}', 'Api\CiroApiController@updateCiro');
$apiRouter->delete('api/ciro/sil/{id}', 'Api\CiroApiController@deleteCiro');

// Gider API Rotaları
$apiRouter->get('api/gider/liste', 'Api\GiderApiController@getGiderListesi');
$apiRouter->get('api/gider/{id}', 'Api\GiderApiController@getGider');
$apiRouter->post('api/gider/ekle', 'Api\GiderApiController@addGider');
$apiRouter->put('api/gider/guncelle/{id}', 'Api\GiderApiController@updateGider');
$apiRouter->delete('api/gider/sil/{id}', 'Api\GiderApiController@deleteGider');
$apiRouter->get('api/gider/stats', 'Api\GiderApiController@getGiderStats');

// İş Emri API Rotaları
$apiRouter->get('api/is-emri/liste', 'Api\IsEmriApiController@getIsEmriListesi');
$apiRouter->get('api/is-emri/{id}', 'Api\IsEmriApiController@getIsEmri');
$apiRouter->post('api/is-emri/olustur', 'Api\IsEmriApiController@createIsEmri');
$apiRouter->put('api/is-emri/guncelle/{id}', 'Api\IsEmriApiController@updateIsEmri');
$apiRouter->delete('api/is-emri/sil/{id}', 'Api\IsEmriApiController@deleteIsEmri');
$apiRouter->put('api/is-emri/durum/{id}', 'Api\IsEmriApiController@updateIsEmriStatus');
$apiRouter->get('api/is-emri/stats', 'Api\IsEmriApiController@getIsEmriStats');

// Bildirim API Rotaları
$apiRouter->get('api/bildirim/liste', 'Api\BildirimApiController@getBildirimListesi');
$apiRouter->get('api/bildirim/{id}', 'Api\BildirimApiController@getBildirim');
$apiRouter->put('api/bildirim/okundu/{id}', 'Api\BildirimApiController@markAsRead');
$apiRouter->put('api/bildirim/tumunu-okundu', 'Api\BildirimApiController@markAllAsRead');
$apiRouter->delete('api/bildirim/sil/{id}', 'Api\BildirimApiController@deleteBildirim');
$apiRouter->get('api/bildirim/okunmamis-sayi', 'Api\BildirimApiController@getUnreadCount');
$apiRouter->get('api/bildirim/stats', 'Api\BildirimApiController@getBildirimStats');

// Cihaz Token API Rotaları
$apiRouter->post('api/device/token/save', 'Api\CihazTokenController@saveDeviceToken');
$apiRouter->delete('api/device/token/remove', 'Api\CihazTokenController@removeDeviceToken');
$apiRouter->get('api/device/info', 'Api\CihazTokenController@getDeviceInfo');
$apiRouter->put('api/device/notification-permission', 'Api\CihazTokenController@updateNotificationPermission');

return $apiRouter;