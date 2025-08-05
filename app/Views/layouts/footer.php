
</div>
<!--end::Content container-->
</div>
<!--end::Content-->
</div>
<!--end::Content wrapper-->
<!--begin::Footer-->

<!--end::Footer-->
</div>
<!--end:::Main-->
</div>
<!--end::Wrapper-->
</div>
<!--end::Page-->
</div>

<footer class="footer mt-auto py-3 bg-light">
	<div class="container">
		<span class="text-muted">© 2024 Mağaza Takip Sistemi</span>
	</div>
</footer>
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
<script src="/public/js/custom/utilities/search/horizontal.js"></script>

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

<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
