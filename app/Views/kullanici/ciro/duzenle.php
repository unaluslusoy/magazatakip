<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ciro Düzenle</title>
</head>
<body>
<h1>Ciro Düzenle</h1>

<?php if (isset($mesaj)) { echo "<p>$mesaj</p>"; } ?>

<form action="" method="post">
    Şube:
    <select name="magaza_id">
        <?php
        // Şubeleri veritabanından çekme
        $conn = (new Database())->getConnection();
        $result = $conn->query("SELECT id, ad FROM magazalar");
        while ($row = $result->fetch_assoc()) {
            $selected = ($row['id'] == $ciro['magaza_id']) ? 'selected' : '';
            echo "<option value='{$row['id']}' $selected>{$row['ad']}</option>";
        }
        ?>
    </select><br><br>

    Ekleme Tarihi: <input type="date" name="ekleme_tarihi" value="<?= $ciro['ekleme_tarihi'] ?>"><br><br>
    Gün: <input type="date" name="gun" value="<?= $ciro['gun'] ?>"><br><br>
    Nakit: <input type="text" name="nakit" value="<?= $ciro['nakit'] ?>"><br><br>
    Kredi Kartı: <input type="text" name="kredi_karti" value="<?= $ciro['kredi_karti'] ?>"><br><br>
    Carliston: <input type="text" name="carliston" value="<?= $ciro['carliston'] ?>"><br><br>
    Getir Çarşı: <input type="text" name="getir_carsi" value="<?= $ciro['getir_carsi'] ?>"><br><br>
    TrendyolGO: <input type="text" name="trendyolgo" value="<?= $ciro['trendyolgo'] ?>"><br><br>
    Multinet: <input type="text" name="multinet" value="<?= $ciro['multinet'] ?>"><br><br>
    Sodexo: <input type="text" name="sodexo" value="<?= $ciro['sodexo'] ?>"><br><br>
    Ticket: <input type="text" name="ticket" value="<?= $ciro['ticket'] ?>"><br><br>
    Edenred: <input type="text" name="edenred" value="<?= $ciro['edenred'] ?>"><br><br>
    Setcard: <input type="text" name="setcard" value="<?= $ciro['setcard'] ?>"><br><br>
    Didi: <input type="text" name="didi" value="<?= $ciro['didi'] ?>"><br><br>
    Gider: <input type="text" name="gider" value="<?= $ciro['gider'] ?>"><br><br>
    Açıklama: <textarea name="aciklama"><?= $ciro['aciklama'] ?></textarea><br><br>
    Durum: <input type="text" name="durum" value="<?= $ciro['durum'] ?>"><br><br>

    <input type="submit" value="Güncelle">
</form>
</body>
</html>
