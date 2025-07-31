
<form class="form fv-plugins-bootstrap5 fv-plugins-framework" method="post" action="/admin/personel/ekle">
	<div class="modal-header" id="kt_modal_add_customer_header">
		<h2 class="fw-bold">Yeni Personel Ekle</h2>
		<div id="kt_modal_add_customer_close" class="btn btn-icon btn-sm btn-active-icon-primary">
			<i class="ki-outline ki-cross fs-1"></i>
		</div>
	</div>

	<div class="modal-body py-10 px-lg-17">
		<div class="scroll-y me-n7 pe-7" id="kt_modal_add_customer_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_customer_header" data-kt-scroll-wrappers="#kt_modal_add_customer_scroll" data-kt-scroll-offset="300px" style="max-height: 984px;">
			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="ad" class="form-label">Adı Soyadı:</label>
				<input type="text" name="ad" id="ad" class="form-control" required>
			</div>

			<div class="fv-row mb-7 fv-plugins-icon-container">
				<label for="email" class="fs-6 fw-semibold mb-2">
					<span class="required">Email</span>
					<span class="ms-1" data-bs-toggle="tooltip" aria-label="Email address must be active" data-bs-original-title="Email address must be active" data-kt-initialized="1">
                            <i class="ki-outline ki-information fs-7"></i>
                        </span>
				</label>
				<input type="email" name="email" id="email" class="form-control" required>
			</div>

			<div class="fv-row mb-15">
				<label for="telefon" class="fs-6 fw-semibold mb-2">Telefon:</label>
				<input type="text" name="telefon" id="telefon" class="form-control form-control-solid">
			</div>

			<div class="mb-3">
				<label for="pozisyon" class="form-label">Pozisyon:</label>
				<input type="text" name="pozisyon" id="pozisyon" class="form-control">
			</div>

			<div class="mb-3">
				<label for="tarih" class="form-label">Tarih:</label>
				<input type="date" name="tarih" id="tarih" class="form-control">
			</div>
		</div>
	</div>

	<div class="modal-footer flex-center">
		<button type="submit" id="kt_modal_add_customer_submit" class="btn btn-primary">
			<span class="indicator-label">Ekle</span>
			<span class="indicator-progress">Lütfen bekleyin...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
		</button>
	</div>
</form>