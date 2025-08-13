<h1>Görev Düzenle</h1>
<form method="post" action="/todo/update">
    <?= csrf_field(); ?>
    <label for="task">Görev:</label>
    <input type="text" name="task" id="task">
    <label for="user">Kullanıcı Ata:</label>
    <select name="user" id="user">
        <!-- Kullanıcı Seçenekleri -->
    </select>
    <label for="start_date">Başlangıç Tarihi:</label>
    <input type="date" name="start_date" id="start_date">
    <label for="end_date">Bitiş Tarihi:</label>
    <input type="date" name="end_date" id="end_date">
    <label for="status">Durum:</label>
    <select name="status" id="status">
        <option value="beklemede">Beklemede</option>
        <option value="devam_ediyor">Devam Ediyor</option>
        <option value="tamamlandi">Tamamlandı</option>
    </select>
    <button type="submit">Güncelle</button>
</form>
