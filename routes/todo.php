<?php
use core\Router;

// Router instance'ını kullan
global $router;

$router->get('todo', 'Todo\TodoController@index');
$router->get('todo/ekle', 'Todo\TodoController@create');
$router->post('todo/store', 'Todo\TodoController@store');
$router->get('todo/duzenle/{id}', 'Todo\TodoController@edit');
$router->post('todo/update/{id}', 'Todo\TodoController@update');
$router->get('todo/sil/{id}', 'Todo\TodoController@delete');
$router->post('todo/assignUser/{taskId}', 'Todo\TodoController@assignUser');
$router->post('todo/setDates/{taskId}', 'Todo\TodoController@setDates');
$router->post('todo/setStatus/{taskId}', 'Todo\TodoController@setStatus');

