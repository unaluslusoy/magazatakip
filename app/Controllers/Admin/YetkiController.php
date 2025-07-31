<?php
namespace app\Controllers\Admin;

use core\Controller;

class YetkiController extends Controller {
    public function atama($personelId) {
        $this->view('admin/yetki_atama');
        // Personel yetki atama işlemleri
    }

    public function unvanAtama($personelId) {
        $this->view('admin/unvan_atama');
        // Personel ünvan atama işlemleri
    }
}
