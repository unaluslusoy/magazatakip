<?php
$title="<h2>Kullanıcılar</h2>";
$link = "Kullanıcı Listesi" ;
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
    <button class="btn btn-primary er fs-6 px-8 py-4" data-bs-toggle="modal" data-bs-target="#kt_modal_new_target">Yeni Kullanıcı Ekle</button>
	<!--<a href="/admin/kullanici/ekle" class="btn btn-primary mb-3">Yeni Kullanıcı Ekle</a>-->
	<table class="table table-striped">
		<thead>
		<tr>
			<th>ID</th>
			<th>Ad</th>
			<th>Email</th>
            <th>Tanımlı Mağaza</th>
			<th>Rol</th>
			<th>İşlemler</th>
		</tr>
		</thead>
		<tbody>
        <?php foreach ($kullanicilar as $kullanici): ?>
			<tr>
				<td><?php echo $kullanici['id']; ?></td>
				<td><?php echo $kullanici['ad']; ?></td>
				<td><?php echo $kullanici['email']; ?></td>
                <td><?= $kullanici['magaza_isim']; ?></td>
				<td><?php echo $kullanici['yonetici'] ? 'Yönetici' : 'Magaza Kullanıcısı'; ?></td>
				<td>
					<a href="/admin/kullanici/duzenle/<?php echo $kullanici['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
					<a href="/admin/kullanici/sil/<?php echo $kullanici['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">Sil</a>
				</td>
			</tr>
        <?php endforeach; ?>
		</tbody>
	</table>
    <!--begin::Modal - New Target-->
    <div class="modal fade" id="kt_modal_new_target" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-750px">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--begin::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                    <!--begin:Form-->
                    <?php
                    include 'app/Views/admin/kullanici_ekle.php';
                    ?>
                    <!--end:Form-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal - New Target-->

<?php
require_once 'app/Views/layouts/footer.php';