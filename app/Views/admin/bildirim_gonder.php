
<!DOCTYPE html>
<html>
<head>
    <title>Bildirim Gönder</title>
</head>
<body>
    <h1>Bildirim Gönder</h1>
    <form action="/admin/bildirim_gonder" method="POST">
        <label for="kullanici_id">Kullanıcı ID:</label>
        <input type="text" id="kullanici_id" name="kullanici_id"><br><br>
        <label for="baslik">Başlık:</label>
        <input type="text" id="baslik" name="baslik"><br><br>
        <label for="mesaj">Mesaj:</label>
        <textarea id="mesaj" name="mesaj"></textarea><br><br>
        <button type="submit">Gönder</button>
    </form>
</body>
</html>
