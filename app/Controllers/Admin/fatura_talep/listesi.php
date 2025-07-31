<?php require_once 'app/Views/layouts/Header.php'; ?>
<?php require_once 'app/Views/layouts/Navbar.php'; ?>

<div class="container">
    <h2>Fatura Talepleri Listesi</h2>
    <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Mağaza Adı</th>
            <th>Müşteri Adı</th>
            <th>Vergi Numarası</th>
            <th>Oluşturulma Tarihi</th>
            <th>İşlemler</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($faturaTalepleri as $talep): ?>
            <tr>
                <td><?php echo htmlspecialchars($talep['id']); ?></td>
                <td><?php echo htmlspecialchars($talep['magaza_ad']); ?></td>
                <td><?php echo htmlspecialchars($talep['musteri_ad']); ?></td>
                <td><?php echo htmlspecialchars($talep['musteri_vergi_no']); ?></td>
                <td><?php echo htmlspecialchars($talep['olusturulma_tarihi']); ?></td>
                <td>
                    <a href="/admin/fatura_talep/duzenle/<?php echo $talep['id']; ?>" class="btn btn-primary">Düzenle</a>
                    <a href="/admin/fatura_talep/sil/<?php echo $talep['id']; ?>" class="btn btn-danger" onclick="return confirm('Bu talebi silmek istediğinize emin misiniz?');">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'app/Views/layouts/Footer.php'; ?>
