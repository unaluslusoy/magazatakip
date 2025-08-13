<h1>Modül Düzenle</h1>
<form method="post" action="/modul/update">
    <?= csrf_field(); ?>
    <label for="name">Modül Adı:</label>
    <input type="text" name="name" id="name">
    <button type="submit">Güncelle</button>
</form>
