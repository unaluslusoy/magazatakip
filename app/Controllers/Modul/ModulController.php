<?php

namespace App\Controllers\Modul;

use Core\Controller;

class ModulController extends Controller {
    public function index() {
        $this->view('modul/index');
    }

    public function create() {
        $this->view('modul/ekle');
    }

    public function store() {
        // Yeni modül kaydet
    }

    public function edit($id) {
        $this->view('modul/duzenle');
    }

    public function update($id) {
        // Mevcut modül bilgilerini güncelle
    }

    public function delete($id) {
        // Modülü sil
    }

    public function aktifPasif($id) {
        // Modülü aktif/pasif yap
    }
}
