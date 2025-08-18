<?php
$title = "Mağaza Listesi" ;
$link = "Mağazalar" ;
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>


	<a href="/admin/magaza/ekle" class="btn btn-primary mb-3">Yeni Mağaza Ekle</a>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>ID</th>
			<th>Şube Adı</th>
			<th>Adres</th>
			<th>Telefon</th>
			<th>Email</th>
			<th>İşlemler</th>
		</tr>
		</thead>
		<tbody>
        <?php foreach($magazalar as $magaza): ?>
			<tr>
				<td><?= $magaza['id']; ?></td>
				<td><?= $magaza['ad']; ?></td>
				<td><?= $magaza['adres']; ?></td>
				<td><?= $magaza['telefon']; ?></td>
				<td><?= $magaza['email']; ?></td>
				<td>
					<a href="/admin/magaza/guncelle/<?= $magaza['id']; ?>" class="btn btn-sm btn-warning">Güncelle</a>
					<a href="/admin/magaza/sil/<?= $magaza['id']; ?>" class="btn btn-sm btn-danger">Sil</a>
				</td>
			</tr>
        <?php endforeach; ?>
		</tbody>
	</table>
<?php
require_once 'app/Views/layouts/footer.php';