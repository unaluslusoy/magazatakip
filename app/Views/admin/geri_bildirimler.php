<?php
$title = "Geri Bildirimler";
$link = "Geri Bildirimler" ;

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#geriBildirimEkleModal">Yeni Geri Bildirim Ekle</button>
<table class="table">
    <thead>
    <tr>
        <th>Başlık</th>
        <th>Durum</th>
        <th>İşlemler</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($geriBildiriler as $geriBildirim): ?>
        <tr>
            <td><?= $geriBildirim['baslik'] ?></td>
            <td><?= $geriBildirim['durum'] ?></td>
            <td>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#geriBildirimDetayModal<?= $geriBildirim['id'] ?>">Detay</button>
                <a href="/admin/geri_bildirimler/sil/<?= $geriBildirim['id'] ?>" class="btn btn-danger">Sil</a>
                <form action="/admin/geri_bildirimler/guncelleDurum" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $geriBildirim['id'] ?>">
                    <select name="durum" onchange="this.form.submit()">
                        <option value="yeni" <?= $geriBildirim['durum'] == 'yeni' ? 'selected' : '' ?>>Yeni</option>
                        <option value="okundu" <?= $geriBildirim['durum'] == 'okundu' ? 'selected' : '' ?>>Okundu</option>
                        <option value="arsiv" <?= $geriBildirim['durum'] == 'arsiv' ? 'selected' : '' ?>>Arşiv</option>
                    </select>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Geri Bildirim Ekle Modal -->
<div class="modal fade" id="geriBildirimEkleModal" tabindex="-1" aria-labelledby="geriBildirimEkleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="geriBildirimEkleModalLabel">Yeni Geri Bildirim Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/admin/geri_bildirimler/ekle" method="post">
                    <div class="mb-3">
                        <label for="baslik" class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="baslik" name="baslik" required>
                    </div>
                    <div class="mb-3">
                        <label for="icerik" class="form-label">İçerik</label>
                        <textarea class="form-control" id="icerik" name="icerik" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="kategori" name="kategori" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php foreach ($geriBildiriler as $geriBildirim): ?>
    <!-- Geri Bildirim Detay Modal -->
    <div class="modal fade" id="geriBildirimDetayModal<?= $geriBildirim['id'] ?>" tabindex="-1" aria-labelledby="geriBildirimDetayModalLabel<?= $geriBildirim['id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="geriBildirimDetayModalLabel<?= $geriBildirim['id'] ?>">Geri Bildirim Detayı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Başlık:</strong> <?= $geriBildirim['baslik'] ?></p>
                    <p><strong>İçerik:</strong> <?= $geriBildirim['icerik'] ?></p>
                    <p><strong>Kategori:</strong> <?= $geriBildirim['kategori'] ?></p>
                    <p><strong>Durum:</strong> <?= $geriBildirim['durum'] ?></p>
                    <p><strong>Oluşturma Tarihi:</strong> <?= $geriBildirim['olusturma_tarihi'] ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php
require_once 'app/Views/layouts/footer.php';