
</div>
<!--end::Content container-->
</div>
<!--end::Content-->
</div>
<!--end::Content wrapper-->
<!--begin::Footer-->
<div id="kt_app_footer" class="app-footer">
	<div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
		<div class="text-dark order-2 order-md-1">
			<span class="text-muted fw-semibold me-1">© 2024</span>
			<a href="#" class="text-gray-800 text-hover-primary">Mağaza Takip Sistemi</a>
		</div>
	</div>
</div>
<!--end::Footer-->
</div>
<!--end:::Main-->
</div>
<!--end::Wrapper-->
</div>
<!--end::Page-->
</div>
<!--end::Root-->
<script>var hostUrl = "public/";</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="/public/plugins/global/plugins.bundle.js?v=<?php echo time(); ?>"></script>
<script src="/public/js/scripts.bundle.js?v=<?php echo time(); ?>"></script>
<!--end::Global Javascript Bundle-->
<!--begin::Vendors Javascript(used for this page only)-->
<script src="/public/plugins/custom/datatables/datatables.bundle.js?v=<?php echo time(); ?>"></script>
<script src="/public/plugins/custom/vis-timeline/vis-timeline.bundle.js?v=<?php echo time(); ?>"></script>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<!--end::Vendors Javascript-->
<script>
try { window.KTComponents = window.KTComponents || {}; } catch(e) {}
</script>
<script src="/public/js/custom/utilities/search/horizontal.js" defer></script>

<!--begin::Auth Guard for Admin Panel-->
<script>
	// Cache buster for auth-guard.js
	const cacheVersion = '?v=' + new Date().getTime();
	const authScript = document.createElement('script');
	authScript.src = '/auth-guard.js' + cacheVersion;
	authScript.onload = function() {
		console.log('✅ Auth Guard loaded for admin panel');
	};
	document.head.appendChild(authScript);
</script>
<!--end::Auth Guard-->

<!--begin::Page Navigation Loader-->
<script src="/public/js/page-transitions.js?v=<?php echo time(); ?>" defer></script>
<!--end::Page Navigation Loader-->

<!-- Global Preview Modal (image) -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Ön İzleme</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-center">
				<img id="previewImage" src="#" alt="Ön İzleme" class="img-fluid rounded" />
			</div>
		</div>
	</div>
</div>

<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
