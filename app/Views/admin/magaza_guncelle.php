<?php
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
<h2>Mağaza Güncelle</h2>
<form method="post" action="/admin/magaza/guncelle/<?= $magaza['id']; ?>">
    <?= csrf_field(); ?>
	<div class="mb-3">
		<label for="ad" class="form-label">Mağaza:</label>
		<input type="text" name="ad" id="ad" class="form-control" value="<?= $magaza['ad']; ?>" required>
	</div>
	<div class="mb-3">
		<label for="adres" class="form-label">Adres:</label>
		<input type="text" name="adres" id="adres" class="form-control" value="<?= $magaza['adres']; ?>" required>
	</div>
	<div class="mb-3">
		<label for="telefon" class="form-label">Telefon:</label>
		<input type="text" name="telefon" id="telefon" class="form-control" value="<?= $magaza['telefon']; ?>" required>
	</div>
	<div class="mb-3">
		<label for="email" class="form-label">Email:</label>
		<input type="email" name="email" id="email" class="form-control" value="<?= $magaza['email']; ?>" required>
	</div>
	<button type="submit" class="btn btn-primary">Güncelle</button>
</form>
<?php
require_once 'app/Views/layouts/footer.php';