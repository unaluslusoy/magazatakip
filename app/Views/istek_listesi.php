<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>
	<style>
        .derece-acil { color: white !important; background-color: red !important; }
        .derece-kritik { color: white !important; background-color: darkred !important; }
        .derece-yuksek { color: white !important; background-color: orange !important; }
        .derece-orta { color: white !important; background-color: yellow !important; }
        .derece-dusuk { color: white !important; background-color: green !important; }
        .derece-inceleniyor { color: white !important; background-color: blue !important; }
	</style>
    <h2>İstek Listesi</h2>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>ID</th>
			<th>Kullanıcı ID</th>
			<th>Başlık</th>
			<th>Açıklama</th>
			<th>Mağaza</th>
			<th>İstek Derecesi</th>
			<th>Tarih</th>
			<th>Durum</th>
		</tr>
		</thead>
        <?php
        function turkish_strtolower($string) {
            $upper = ['I', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
            $lower = ['ı', 'i', 'g', 'u', 's', 'o', 'c'];

            $string = str_replace($upper, $lower, $string);
            return strtolower($string);
        }
        ?>

		<tbody>
        <?php foreach($istekler as $istek): ?>
			<tr>
				<td><?= $istek['id']; ?></td>
				<td><?= $istek['kullanici_id']; ?></td>
				<td><?= $istek['baslik']; ?></td>
				<td><?= $istek['aciklama']; ?></td>
				<td><?= $istek['magaza']; ?></td>
				<td class="derece-<?= turkish_strtolower($istek['derece']); ?>"><?= ucfirst($istek['derece']); ?></td>
				<td><?= $istek['tarih']; ?></td>
				<td><?= ucfirst($istek['durum']); ?></td>
			</tr>
        <?php endforeach; ?>
		</tbody>

	</table>
<?php
require_once 'app/Views/kullanici/layout/footer.php';

