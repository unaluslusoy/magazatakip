<?php
$title = "Gösterge Paneli";
$link = "Gösterge";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

	<div class="row g-5 g-xl-8">
		<div class="col-xl-3">
			<!--begin::Statistics Widget 5-->
			<a href="/admin/istekler" class="card bg-body hoverable card-xl-stretch mb-xl-8">
				<!--begin::Body-->
				<div class="card-body">
					<i class="ki-outline ki-chart-simple text-primary fs-2x ms-n1"></i>
					<div class="text-gray-900 fw-bold fs-2 mb-2 mt-5"><?= $IsTasks ?></div>
					<div class="fw-semibold text-gray-400">Bekleyen İş Emirleri</div>
				</div>
				<!--end::Body-->
			</a>
			<!--end::Statistics Widget 5-->
		</div>
		<div class="col-xl-3">
			<!--begin::Statistics Widget 5-->
			<a href="admin/personeller" class="card bg-dark hoverable card-xl-stretch mb-xl-8">
				<!--begin::Body-->
				<div class="card-body">
					<i class="ki-outline ki-cheque text-gray-100 fs-2x ms-n1"></i>
					<div class="text-gray-100 fw-bold fs-2 mb-2 mt-5">+<?= $totalEmployees ?></div>
					<div class="fw-semibold text-gray-100">Personel</div>
				</div>
				<!--end::Body-->
			</a>
			<!--end::Statistics Widget 5-->
		</div>
		<div class="col-xl-3">
			<!--begin::Statistics Widget 5-->
			<a href="/admin/magazalar" class="card bg-warning hoverable card-xl-stretch mb-xl-8">
				<!--begin::Body-->
				<div class="card-body">
					<i class="ki-outline ki-briefcase text-white fs-2x ms-n1"></i>
					<div class="text-white fw-bold fs-2 mb-2 mt-5">+<?=$totalStores?></div>
					<div class="fw-semibold text-white">Mağaza</div>
				</div>
				<!--end::Body-->
			</a>
			<!--end::Statistics Widget 5-->
		</div>
		<div class="col-xl-3">
			<!--begin::Statistics Widget 5-->
			<a href="/admin/kullanicilar" class="card bg-info hoverable card-xl-stretch mb-5 mb-xl-8">
				<!--begin::Body-->
				<div class="card-body">
					<i class="ki-outline ki-chart-pie-simple text-white fs-2x ms-n1"></i>
					<div class="text-white fw-bold fs-2 mb-2 mt-5">+<?= $totalUsers ?></div>
					<div class="fw-semibold text-white">Kullanıcı</div>
				</div>
				<!--end::Body-->
			</a>
			<!--end::Statistics Widget 5-->
		</div>
	</div>
	<?php
require_once 'app/Views/layouts/footer.php';

