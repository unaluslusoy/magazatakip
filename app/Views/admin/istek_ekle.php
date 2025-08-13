<!-- app/Views/admin/istek_ekle.php -->
<form method="post" action="/admin/istek/ekle" class="form fv-plugins-bootstrap5 fv-plugins-framework" id="kt_modal_add_istek_form">
    <?= csrf_field(); ?>
	<div class="modal-header">
		<h2 class="fw-bold">İş Emri Ekle</h2>
		<div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
			<i class="ki-outline ki-cross fs-1"></i>
		</div>
	</div>
	<div class="modal-body py-10 px-lg-17">
		<div class="scroll-y me-n7 pe-7" id="kt_modal_add_istek_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_istek_header" data-kt-scroll-wrappers="#kt_modal_add_istek_scroll" data-kt-scroll-offset="300px" style="max-height: 984px;">
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="kullanici_id" class="form-label">Kullanıcı ID:</label>
				<input type="text" name="kullanici_id" id="kullanici_id" class="form-control form-control-solid" required>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="baslik" class="form-label">Başlık:</label>
				<input type="text" name="baslik" id="baslik" class="form-control form-control-solid" required>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="aciklama" class="form-label">Açıklama:</label>
				<textarea name="aciklama" id="aciklama" class="form-control form-control-solid" required></textarea>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="magaza" class="form-label">Mağaza:</label>
				<input type="text" name="magaza" id="magaza" class="form-control form-control-solid" required>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="derece" class="form-label">İstek Derecesi:</label>
				<input type="text" name="derece" id="derece" class="form-control form-control-solid" required>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="personel_id" class="form-label">Personel:</label>
				<select name="personel_id" id="personel_id" class="form-select form-select-solid">
					<option value="">Görevli Seçin</option>
                    <?php foreach ($personeller as $personel): ?>

						<option value="<?= $personel['id']; ?>"><?= $personel['ad']; ?> <?= $personel['soyad']; ?></option>
                    <?php endforeach; ?>
				</select>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="is_aciklamasi" class="form-label">İş Açıklaması:</label>
				<textarea name="is_aciklamasi" id="is_aciklamasi" class="form-control form-control-solid"></textarea>
				<div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
			</div>
            <div class="fv-row mb-7 fv-plugins-icon-container">
                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi:</label>
                <input type="date" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control form-control-solid" >
                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
            </div>
            <div class="fv-row mb-7 fv-plugins-icon-container">
                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi:</label>
                <input type="date" name="bitis_tarihi" id="bitis_tarihi" class="form-control form-control-solid" >
                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
            </div>

        </div>
	</div>
	<div class="modal-footer flex-center">
		<button type="submit" id="kt_modal_add_istek_submit" class="btn btn-primary">
			<span class="indicator-label">Ekle</span>
			<span class="indicator-progress">Lütfen bekleyin...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
            </span>
		</button>
	</div>
</form>
