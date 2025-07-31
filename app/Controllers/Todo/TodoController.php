<?php

namespace App\Controllers\Todo;

use Core\Controller;

class TodoController extends Controller {
    public function index() {
        $this->view('todo/index');
    }

    public function create() {
        $this->view('todo/ekle');
    }

    public function store() {
        // Yeni görev kaydet
    }

    public function edit($id) {
        $this->view('todo/duzenle');
    }

    public function update($id) {
        // Mevcut görev bilgilerini güncelle
    }

    public function delete($id) {
        // Görevi sil
    }

    public function assignUser($taskId, $userId) {
        // Göreve kullanıcı ata
    }

    public function setDates($taskId, $startDate, $endDate) {
        // Görev başlangıç ve bitiş tarihlerini ayarla
    }

    public function setStatus($taskId, $status) {
        // Görev durumunu güncelle
    }
}
