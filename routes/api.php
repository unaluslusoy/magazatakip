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

return $apiRouter;