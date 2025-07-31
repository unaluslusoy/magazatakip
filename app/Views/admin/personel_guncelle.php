<?php
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>


    <div class="card">
        <div class="card-header">
            <h3><?= $personel['ad'] . ' ' . $personel['soyad']; ?></h3>
        </div>
        <div class="card-body">
            <form id="kt_modal_add_customer_form" class="form" method="post" action="/admin/personel/guncelle/<?= $personel['id']; ?>" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="ad" class="form-label">İsim:</label>
                        <input type="text" name="ad" id="ad" class="form-control" value="<?= $personel['ad']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= $personel['Email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefon" class="form-label">Telefon:</label>
                        <input type="text" name="telefon" id="telefon" class="form-control" value="<?= $personel['telefon']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="pozisyon" class="form-label">Pozisyon:</label>
                        <input type="text" name="pozisyon" id="pozisyon" class="form-control" value="<?= $personel['pozisyon']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="tarih" class="form-label">Tarih:</label>
                        <input type="date" name="tarih" id="tarih" class="form-control" value="<?= $personel['tarih']; ?>">
                    </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
<?php
require_once 'app/Views/layouts/footer.php';