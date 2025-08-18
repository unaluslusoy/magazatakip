<?php
$title = "Bildirim Gönder";
$link = "Bildirim" ;

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

<?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
        <?php
        echo $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        ?>
    </div>
<?php endif; ?>

<form action="/admin/bildirim_gonder" method="POST">
    <div>
        <label for="bildirim_tipi">Bildirim Tipi:</label>
        <select id="bildirim_tipi" name="bildirim_tipi" required>
            <option value="push">Push Bildirimi</option>
            <option value="sms">SMS</option>
            <option value="email">E-posta</option>
        </select>
    </div>
    <div id="alici_secenekleri">
        <label>Alıcılar:</label>
        <div>
            <input type="radio" id="tum_kullanicilar" name="alici_tipi" value="tum" checked>
            <label for="tum_kullanicilar">Tüm Kullanıcılar</label>
        </div>
        <div>
            <input type="radio" id="belirli_kullanicilar" name="alici_tipi" value="belirli">
            <label for="belirli_kullanicilar">Belirli Kullanıcılar</label>
        </div>
    </div>

    <div id="kullanici_listesi" style="display: none;">
        <label for="kullanicilar">Kullanıcıları Seçin:</label>
        <select id="kullanicilar" name="kullanicilar[]" multiple>
            <?php foreach ($kullanicilar as $kullanici): ?>
                <option value="<?php echo $kullanici['id']; ?>"><?php echo $kullanici['ad']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="baslik">Başlık:</label>
        <input type="text" id="baslik" name="baslik" required>
    </div>

    <div>
        <label for="mesaj">Mesaj:</label>
        <textarea id="mesaj" name="mesaj" required></textarea>
    </div>
    <div>
        <label for="url">Yönlendirme URL'si:</label>
        <input type="text" id="url" name="url" value="/admin/bildirimler">
    </div>
    <div>
        <label for="icon">Icon URL:</label>
        <input type="text" id="icon" name="icon">
    </div>
    <div>
        <label for="oncelik">Öncelik:</label>
        <select id="oncelik" name="oncelik">
            <option value="dusuk">Düşük</option>
            <option value="normal" selected>Normal</option>
            <option value="yuksek">Yüksek</option>
        </select>
    </div>

    <button type="submit">Bildirimi Gönder</button>
</form>

<script>
    document.getElementById('bildirim_tipi').addEventListener('change', function() {
        var baslikField = document.getElementById('baslik');
        if (this.value === 'sms') {
            baslikField.disabled = true;
            baslikField.required = false;
        } else {
            baslikField.disabled = false;
            baslikField.required = true;
        }
    });

    document.getElementById('belirli_kullanicilar').addEventListener('change', function() {
        document.getElementById('kullanici_listesi').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('tum_kullanicilar').addEventListener('change', function() {
        document.getElementById('kullanici_listesi').style.display = this.checked ? 'none' : 'block';
    });
</script>
<?php
require_once 'app/Views/layouts/footer.php';
?>
