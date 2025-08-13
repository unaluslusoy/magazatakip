<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneSignal, SMS ve E-posta Yönetimi</title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<h1>OneSignal, SMS ve E-posta Yönetimi</h1>

<?php if (isset($_GET['success'])): ?>
    <p class="success">Ayarlar başarıyla güncellendi.</p>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <p class="error">Ayarlar güncellenirken bir hata oluştu.</p>
<?php endif; ?>

<?php if (isset($_GET['notification_success'])): ?>
    <p class="success">Bildirim başarıyla gönderildi.</p>
<?php endif; ?>

<?php if (isset($_GET['notification_error'])): ?>
    <p class="error">Bildirim gönderilirken bir hata oluştu.</p>
<?php endif; ?>

<form action="/admin/onesignal/kaydet" method="POST">
    <?= csrf_field(); ?>
    <h2>OneSignal Ayarları</h2>
    <label for="app_id">OneSignal App ID:</label>
    <input type="text" id="app_id" name="app_id" value="<?php echo $ayarlar['app_id'] ?? ''; ?>" required>

    <label for="api_key">OneSignal API Key:</label>
    <input type="text" id="api_key" name="api_key" value="<?php echo $ayarlar['api_key'] ?? ''; ?>" required>

    <h2>Twilio (SMS) Ayarları</h2>
    <label for="twilio_sid">Twilio SID:</label>
    <input type="text" id="twilio_sid" name="twilio_sid" value="<?php echo $ayarlar['twilio_sid'] ?? ''; ?>" required>

    <label for="twilio_token">Twilio Token:</label>
    <input type="text" id="twilio_token" name="twilio_token" value="<?php echo $ayarlar['twilio_token'] ?? ''; ?>" required>

    <label for="twilio_phone">Twilio Telefon Numarası:</label>
    <input type="text" id="twilio_phone" name="twilio_phone" value="<?php echo $ayarlar['twilio_phone'] ?? ''; ?>" required>

    <h2>SendGrid (E-posta) Ayarları</h2>
    <label for="sendgrid_api_key">SendGrid API Key:</label>
    <input type="text" id="sendgrid_api_key" name="sendgrid_api_key" value="<?php echo $ayarlar['sendgrid_api_key'] ?? ''; ?>" required>

    <label for="sendgrid_from_email">SendGrid Gönderen E-posta:</label>
    <input type="email" id="sendgrid_from_email" name="sendgrid_from_email" value="<?php echo $ayarlar['sendgrid_from_email'] ?? ''; ?>" required>

    <button type="submit">Ayarları Kaydet</button>
</form>

<h2>Bildirim Gönder</h2>
<form action="/admin/onesignal/bildirim-gonder" method="POST">
    <?= csrf_field(); ?>
    <label for="tip">Bildirim Tipi:</label>
    <select id="tip" name="tip" required>
        <option value="push">Push Bildirim</option>
        <option value="sms">SMS</option>
        <option value="email">E-posta</option>
    </select>

    <div id="sms-field" style="display: none;">
        <label for="telefon">Telefon Numarası:</label>
        <input type="tel" id="telefon" name="telefon">
    </div>

    <div id="email-field" style="display: none;">
        <label for="email">E-posta Adresi:</label>
        <input type="email" id="email" name="email">
    </div>

    <label for="baslik">Bildirim Başlığı:</label>
    <input type="text" id="baslik" name="baslik" required>

    <label for="mesaj">Bildirim Mesajı:</label>
    <textarea id="mesaj" name="mesaj" required></textarea>

    <button type="submit">Bildirimi Gönder</button>
</form>

<script>
    document.getElementById('tip').addEventListener('change', function() {
        var smsField = document.getElementById('sms-field');
        var emailField = document.getElementById('email-field');

        smsField.style.display = this.value === 'sms' ? 'block' : 'none';
        emailField.style.display = this.value === 'email' ? 'block' : 'none';
    });
</script>
</body>
</html>