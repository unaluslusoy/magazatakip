<?php require_once 'app/Views/layouts/Header.php'; ?>
<?php require_once 'app/Views/layouts/Navbar.php'; ?>

<div class="container">
    <h2>Fatura Talebini Düzenle</h2>
    <form method="post" action="/admin/fatura_talep/duzenle/<?php echo htmlspecialchars($faturaTalep['id']); ?>">
        <div class="mb-3">
            <label for="magaza_id" class="form-label">Mağaza:</label>
            <select name="magaza_id" id="magaza_id" class="form-select" required>
                <option value="">Seçim Yapınız</option>
                <?php foreach ($magazalar as $magaza): ?>
                    <option value="<?php echo htmlspecialchars($magaza['id']); ?>" <?php echo ($magaza['id'] == $faturaTalep['magaza_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($magaza['ad']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="musteri_ad" class="form-label">Müşteri Adı:</label>
            <input type="text" name="musteri_ad" id="musteri_ad" class="form-control" value="<?php echo htmlspecialchars($faturaTalep['musteri_ad']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="musteri_adres" class="form-label">Müşteri Adresi:</label>
            <textarea name="musteri_adres" id="musteri_adres" class="form-control" required><?php echo htmlspecialchars($faturaTalep['musteri_adres']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="musteri_vergi_no" class="form-label">Vergi Numarası:</label>
            <input type="text" name="musteri_vergi_no" id="musteri_vergi_no" class="form-control" value="<?php echo htmlspecialchars($faturaTalep['musteri_vergi_no']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="musteri_vergi_dairesi" class="form-label">Vergi Dairesi:</label>
            <input type="text" name="musteri_vergi_dairesi" id="musteri_vergi_dairesi" class="form-control" value="<?php echo htmlspecialchars($faturaTalep['musteri_vergi_dairesi']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="aciklama" class="form-label">Açıklama:</label>
            <textarea name="aciklama" id="aciklama" class="form-control"><?php echo htmlspecialchars($faturaTalep['aciklama']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Güncelle</button>
    </form>
</div>

<?php require_once 'app/Views/layouts/Footer.php'; ?>
