<?php


namespace Core;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        require_once '../app/Views/' . $view . '.php';
    }
}
