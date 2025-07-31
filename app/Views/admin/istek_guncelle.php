<?php
$id = $_GET['id'] ?? null;
$istek = getIstekById($id);

function getIstekById($id) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM istekler WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<form method="post" action="/admin/istek/guncelle/<?= $istek['id']; ?>">
	<div class="mb-3">
		<label for="baslik" class="form-label">Başlık:</label>
		<input type="text" name="baslik" id="baslik" class="form-control" value="<?= $istek['baslik']; ?>" disabled>
	</div>
	<div class="mb-3">
		<label for="aciklama" class="form-label">Açıklama:</label>
		<textarea name="aciklama" id="aciklama" class="form-control" disabled><?= $istek['aciklama']; ?></textarea>
	</div>
	<div class="mb-3">
		<label for="magaza" class="form-label">Mağaza:</label>
		<input type="text" name="magaza" id="magaza" class="form-control" value="<?= $istek['magaza']; ?>" disabled>
	</div>
	<div class="mb-3">
		<label for="derece" class="form-label">İstek Derecesi:</label>
		<input type="text" name="derece" id="derece" class="form-control" value="<?= $istek['derece']; ?>" disabled>
	</div>
	<div class="mb-3">
		<label for="durum" class="form-label">Durum:</label>
		<select name="durum" id="durum" class="form-select" required>
			<option value="bekliyor" <?= $istek['durum'] == 'bekliyor' ? 'selected' : ''; ?>>Bekliyor</option>
			<option value="tamamlandi" <?= $istek['durum'] == 'tamamlandi' ? 'selected' : ''; ?>>Tamamlandı</option>
		</select>
	</div>
	<div class="mb-3">
		<label for="personel_id" class="form-label">Personel:</label>
		<select name="personel_id" id="personel_id" class="form-select">
			<option value="0">Seçim Yapın</option>
            <?php foreach ($personeller as $personel): ?>
				<option value="<?= $personel['id']; ?>" <?= $personel['id'] == $istek['personel_id'] ? 'selected' : ''; ?>><?= $personel['ad']; ?></option>
            <?php endforeach; ?>
		</select>
	</div>
	<div class="mb-3">
		<label for="is_aciklamasi" class="form-label">İş Açıklaması:</label>
		<textarea name="is_aciklamasi" id="is_aciklamasi" class="form-control"><?= $istek['is_aciklamasi']; ?></textarea>
	</div>
	<div class="mb-3">
		<label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi:</label>
		<input type="date" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control" value="<?= $istek['baslangic_tarihi']; ?>">
	</div>
	<div class="mb-3">
		<label for="bitis_tarihi" class="form-label">Bitiş Tarihi:</label>
		<input type="date" name="bitis_tarihi" id="bitis_tarihi" class="form-control" value="<?= $istek['bitis_tarihi']; ?>">
	</div>
	<button type="submit" class="btn btn-primary">Güncelle</button>
</form>


