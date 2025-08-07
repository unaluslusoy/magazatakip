<?php
namespace core;

class Controller {
    public function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../app/Views/' . $view . '.php';
    }

    public function model($model) {
        require_once 'app/Models/' . $model . '.php';
        return new $model();
    }
}
