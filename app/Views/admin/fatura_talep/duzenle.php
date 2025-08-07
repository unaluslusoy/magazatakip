<?php
$title  = "<h2>Fatura Talebini Düzenle</h2>";
$link   = "Fatura Talebi Düzenle";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

<div class="container">
    <h2>Fatura Talebini Düzenle</h2>
    <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/fatura_talep/duzenle/<?php echo htmlspecialchars($faturaTalep['id']); ?>" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="magaza_id" class="form-label">Mağaza:</label>
            <select name="magaza_id" id="magaza_id" class="form-select" required>
                <option value="">Seçim Yapınız</option>
                <?php foreach ($magazalar as $magaza): ?>
                    <option value="<?= htmlspecialchars($magaza['id']); ?>" <?= ($magaza['id'] == $faturaTalep['magaza_id']) ? 'selected' : ''; ?>><?= htmlspecialchars($magaza['ad']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="musteri_ad" class="form-label">Müşteri Adı:</label>
            <input type="text" name="musteri_ad" id="musteri_ad" class="form-control" value="<?= htmlspecialchars($faturaTalep['musteri_ad']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="musteri_adres" class="form-label">Müşteri Adresi:</label>
            <textarea name="musteri_adres" id="musteri_adres" class="form-control" required><?= htmlspecialchars($faturaTalep['musteri_adres'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="musteri_vergi_no" class="form-label">Vergi Numarası:</label>
            <input type="text" name="musteri_vergi_no" id="musteri_vergi_no" class="form-control" value="<?= htmlspecialchars($faturaTalep['musteri_vergi_no'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="musteri_vergi_dairesi" class="form-label">Vergi Dairesi:</label>
            <input type="text" name="musteri_vergi_dairesi" id="musteri_vergi_dairesi" class="form-control" value="<?= htmlspecialchars($faturaTalep['musteri_vergi_dairesi'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="musteri_telefon" class="form-label">Telefon:</label>
            <input type="text" name="musteri_telefon" id="musteri_telefon" class="form-control" value="<?= htmlspecialchars($faturaTalep['musteri_telefon'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="musteri_email" class="form-label">E-posta:</label>
            <input type="email" name="musteri_email" id="musteri_email" class="form-control" value="<?= htmlspecialchars($faturaTalep['musteri_email'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="aciklama" class="form-label">Açıklama:</label>
            <textarea name="aciklama" id="aciklama" class="form-control"><?= htmlspecialchars($faturaTalep['aciklama']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="durum" class="form-label">Durum:</label>
            <select name="durum" id="durum" class="form-select" required>
                <?php
                $durum = $faturaTalep['durum'] ?? 'Yeni';
                ?>
                <option value="Yeni" <?= ($durum == 'Yeni') ? 'selected' : ''; ?>>Yeni</option>
                <option value="Beklemede" <?= ($durum == 'Beklemede') ? 'selected' : ''; ?>>Beklemede</option>
                <option value="Onaylandi" <?= ($durum == 'Onaylandi') ? 'selected' : ''; ?>>Onaylandı</option>
                <option value="Gonderildi" <?= ($durum == 'Gonderildi') ? 'selected' : ''; ?>>Gönderildi</option>
                <option value="Tamamlandi" <?= ($durum == 'Tamamlandi') ? 'selected' : ''; ?>>Tamamlandı</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="fatura_pdf" class="form-label">Fatura PDF:</label>
            <input type="file" name="fatura_pdf" id="fatura_pdf" class="form-control">
            <?php if (!empty($faturaTalep['fatura_pdf_path'])): ?>
                <a href="/public/uploads/<?= htmlspecialchars($faturaTalep['fatura_pdf_path']); ?>" target="_blank">Mevcut PDF</a>
            <?php endif; ?>
        </div>

        <div class="modal-footer flex-lg-end">
            <a href="/admin/fatura_talep/listesi" id="kt_modal_new_address_cancel" class="btn btn-light me-3">
                Geri
            </a>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">
                    Güncelle
                </span>
                <span class="indicator-progress">
                    Lütfen bekleyin...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>
    </form>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
