<h1>Yeni Modül Ekle</h1>
<form method="post" action="/modul/store">
    <?= csrf_field(); ?>
    <label for="name">Modül Adı:</label>
    <input type="text" name="name" id="name">
    <button type="submit">Kaydet</button>
</form>
