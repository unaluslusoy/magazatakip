<?php
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
<div class="container mt-5">
    <?php require_once $view; ?>
</div>
<?php
require_once 'app/Views/layouts/footer.php';