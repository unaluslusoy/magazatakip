<?php
use core\Router;

$router->get('modul', 'Modul\ModulController@index');
$router->get('modul/ekle', 'Modul\ModulController@create');
$router->post('modul/store', 'Modul\ModulController@store');
$router->get('modul/duzenle/{id}', 'Modul\ModulController@edit');
$router->post('modul/update/{id}', 'Modul\ModulController@update');
$router->get('modul/sil/{id}', 'Modul\ModulController@delete');
$router->get('modul/aktifPasif/{id}', 'Modul\ModulController@aktifPasif');