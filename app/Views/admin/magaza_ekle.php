<?php
$title = "Yeni Mağaza Ekle";
$link = "Mağaza Tanımlama" ;
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

<form method="post" action="/admin/magaza/ekle">
    <?= csrf_field(); ?>
	<div class="mb-3">
		<label for="ad" class="form-label">Magaza:</label>
		<input type="text" name="ad" id="ad" class="form-control" required>
	</div>
	<div class="mb-3">
		<label for="adres" class="form-label">Adres:</label>
		<input type="text" name="adres" id="adres" class="form-control" required>
	</div>
	<div class="mb-3">
		<label for="telefon" class="form-label">Telefon:</label>
		<input type="text" name="telefon" id="telefon" class="form-control" required>
	</div>
	<div class="mb-3">
		<label for="email" class="form-label">Email:</label>
		<input type="email" name="email" id="email" class="form-control" required>
	</div>
	<button type="submit" class="btn btn-primary">Ekle</button>
</form>
<?php
require_once 'app/Views/layouts/footer.php';