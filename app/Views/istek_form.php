<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>
    <h2>Yeni İstek Oluştur</h2>
	<form method="post" action="/istek/olustur">
		<div class="mb-3">
			<label for="baslik" class="form-label">Başlık:</label>
			<input type="text" name="baslik" id="baslik" class="form-control" required>
		</div>
		<div class="mb-3">
			<label for="magaza" class="form-label">Mağaza:</label>
			<input type="text" name="magaza" id="magaza" class="form-control" required>
		</div>
		<div class="mb-3">
			<label for="derece" class="form-label">İstek Derecesi:</label>
			<select name="derece" id="derece" class="form-select" required>
				<option value="ACİL">ACİL</option>
				<option value="KRİTİK">KRİTİK</option>
				<option value="YÜKSEK">YÜKSEK</option>
				<option value="ORTA">ORTA</option>
				<option value="DÜŞÜK">DÜŞÜK</option>
				<option value="İNCELENİYOR">İNCELENİYOR</option>
			</select>
		</div>
		<div class="mb-3">
			<label for="aciklama" class="form-label">Açıklama:</label>
			<textarea name="aciklama" id="aciklama" class="form-control" required></textarea>
		</div>


		<button type="submit" class="btn btn-primary">Gönder</button>
	</form>

<?php
require_once 'app/Views/kullanici/layout/footer.php';
