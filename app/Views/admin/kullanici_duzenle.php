<?php
$title = "<h2>Kullanıcı Güncelleme</h2>";
$link = "Kullanıcı Düzenle";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
    
    <form method="post" action="/admin/kullanici/update/<?php echo $kullanici['id']; ?>">
        <div class="form-group">
            <label for="ad">Ad:</label>
            <input type="text" name="ad" id="ad" class="form-control" value="<?php echo $kullanici['ad']; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo $kullanici['email']; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Şifre (Boş bırakılırsa değişmez):</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="form-check">
            <input type="checkbox" name="yonetici" id="yonetici" class="form-check-input" <?php echo $kullanici['yonetici'] ? 'checked' : ''; ?>>
            <label for="yonetici" class="form-check-label">Yönetici</label>
        </div>
        <button type="submit" class="btn btn-primary">Güncelle</button>
    </form>

<?php
require_once 'app/Views/layouts/footer.php';