<h1>Geri Bildirimler</h1>
<a href="/admin/geri_bildirimler/ekle">Yeni Geri Bildirim Ekle</a>
<table>
    <thead>
    <tr>
        <th>Başlık</th>
        <th>Durum</th>
        <th>İşlemler</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($geriBildiriler as $geriBildirim): ?>
        <tr>
            <td><?= $geriBildirim['baslik'] ?></td>
            <td><?= $geriBildirim['durum'] ?></td>
            <td>
                <a href="/admin/geri_bildirimler/detay/<?= $geriBildirim['id'] ?>">Detay</a>
                <a href="/admin/geri_bildirimler/sil/<?= $geriBildirim['id'] ?>">Sil</a>
                <form action="/admin/geri_bildirimler/guncelleDurum" method="post">
                    <input type="hidden" name="id" value="<?= $geriBildirim['id'] ?>">
                    <select name="durum">
                        <option value="yeni" <?= $geriBildirim['durum'] == 'yeni' ? 'selected' : '' ?>>Yeni</option>
                        <option value="okundu" <?= $geriBildirim['durum'] == 'okundu' ? 'selected' : '' ?>>Okundu</option>
                        <option value="arsiv" <?= $geriBildirim['durum'] == 'arsiv' ? 'selected' : '' ?>>Arşiv</option>
                    </select>
                    <button type="submit">Güncelle</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>


<!-- Durum Güncelleme Modali -->
<div class="modal fade" id="durumGuncelleModal" tabindex="-1" aria-labelledby="durumGuncelleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="durumGuncelleModalLabel">Durum Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="durumGuncelleForm">
                    <input type="hidden" id="geriBildirimId" name="id">
                    <div class="mb-3">
                        <label for="durum" class="form-label">Durum</label>
                        <select class="form-control" id="durum" name="durum" required>
                            <option value="okundu">Okundu</option>
                            <option value="okunmadı">Okunmadı</option>
                            <option value="arşivlendi">Arşivlendi</option>
                            <option value="silindi">Silindi</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.durumGuncelleBtn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const currentStatus = this.dataset.status;

            // Modal içindeki formu güncelle
            document.getElementById('geriBildirimId').value = id;
            document.getElementById('durum').value = currentStatus;

            // Modalı göster
            const myModal = new bootstrap.Modal(document.getElementById('durumGuncelleModal'), {
                keyboard: false
            });
            myModal.show();
        });
    });

    document.getElementById('durumGuncelleForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('geriBildirimId').value;
        const durum = document.getElementById('durum').value;

        fetch('/admin/geri_bildirimler/guncelleDurum', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id, durum })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Durum güncellenirken hata oluştu.');
                }
            });
    });
</script>
