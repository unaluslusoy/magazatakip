<?php
$title = "Gönderilen Bildirimler";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

    <table class="table">
        <thead>
        <tr>
            <th>Başlık</th>
            <th>Mesaj</th>
            <th>Tip</th>
            <th>Alıcı Tipi</th>
            <th>Gönderim Tarihi</th>
            <th>Başarılı</th>
            <th>Hatalı</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bildirimler as $bildirim): ?>
            <tr>
                <td><?php echo htmlspecialchars($bildirim['baslik']); ?></td>
                <td><?php echo htmlspecialchars($bildirim['mesaj']); ?></td>
                <td><?php echo htmlspecialchars($bildirim['bildirim_tipi']); ?></td>
                <td><?php echo htmlspecialchars($bildirim['alici_tipi']); ?></td>
                <td><?php echo htmlspecialchars($bildirim['gonderim_tarihi']); ?></td>
                <td><?php echo htmlspecialchars($bildirim['basarili']); ?></td>
                <td><?php echo htmlspecialchars($bildirim['hatali']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php
require_once 'app/Views/layouts/footer.php';
