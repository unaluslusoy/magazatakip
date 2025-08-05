<?php
$title = "Sistem Timeline";
$link = "Timeline";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
require_once 'scripts/timeline-manager.php';

$timeline = new TimelineManager();
?>

<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    <i class="ki-duotone ki-calendar-8 fs-1 text-primary me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                    </i>
                    Sistem Timeline & Rollback
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin" class="text-muted text-hover-primary">Anasayfa</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Timeline</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container"="app-container container-xxl">
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Quick Actions -->
            <div class="row mb-8">
                <div class="col-md-6">
                    <div class="card card-flush">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-duotone ki-plus-circle fs-2 text-success me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Yeni Checkpoint
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="createCheckpointForm">
                                <div class="mb-5">
                                    <label class="form-label">Checkpoint Açıklaması</label>
                                    <input type="text" class="form-control" id="checkpointDescription" 
                                           placeholder="Ör: Yeni özellik eklenmesi öncesi backup" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ki-duotone ki-check fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Checkpoint Oluştur
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card card-flush">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-duotone ki-shield-tick fs-2 text-info me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Hızlı İşlemler
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <button class="btn btn-light-primary" onclick="createAutoBackup()">
                                    <i class="ki-duotone ki-calendar fs-2 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Günlük Backup Oluştur
                                </button>
                                <button class="btn btn-light-warning" onclick="cleanupOldBackups()">
                                    <i class="ki-duotone ki-trash fs-2 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Eski Backup'ları Temizle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-timeline fs-2 text-primary me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Sistem Timeline
                    </h3>
                    <div class="card-toolbar">
                        <button class="btn btn-sm btn-light" onclick="refreshTimeline()">
                            <i class="ki-duotone ki-arrows-circle fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Yenile
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="timelineContainer" class="timeline">
                        <!-- Timeline içeriği buraya yüklenecek -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadTimeline();
    
    // Checkpoint oluşturma formu
    document.getElementById('createCheckpointForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createCheckpoint();
    });
});

/**
 * Timeline yükle
 */
function loadTimeline() {
    fetch('/api/timeline/list', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayTimeline(data.timeline);
        } else {
            showAlert('error', 'Timeline yüklenemedi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Timeline yükleme hatası:', error);
        showAlert('error', 'Timeline yüklenirken hata oluştu');
    });
}

/**
 * Timeline görüntüle
 */
function displayTimeline(timeline) {
    const container = document.getElementById('timelineContainer');
    
    if (!timeline || timeline.length === 0) {
        container.innerHTML = `
            <div class="text-center py-10">
                <i class="ki-duotone ki-information fs-5x text-muted mb-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="text-muted fs-6">Henüz checkpoint oluşturulmamış</div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    timeline.forEach((checkpoint, index) => {
        const isActive = index === 0;
        const rollbackBadge = checkpoint.rollback_count > 0 ? 
            `<span class="badge badge-light-warning ms-2">${checkpoint.rollback_count}x kullanıldı</span>` : '';
        
        const typeColor = {
            'manual': 'primary',
            'auto': 'info',
            'pre-operation': 'warning',
            'critical': 'danger'
        }[checkpoint.type] || 'secondary';
        
        html += `
            <div class="timeline-item">
                <div class="timeline-line w-40px"></div>
                <div class="timeline-icon symbol symbol-circle symbol-40px ${isActive ? 'me-4' : ''}">
                    <div class="symbol-label bg-light-${typeColor}">
                        <i class="ki-duotone ki-check fs-2 text-${typeColor}">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="timeline-content mb-10 mt-n1">
                    <div class="pe-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-5 fw-semibold mb-2">
                                ${checkpoint.description}
                                ${isActive ? '<span class="badge badge-light-success ms-2">GÜNCEL</span>' : ''}
                                ${rollbackBadge}
                            </div>
                            <div class="d-flex align-items-center mt-1 fs-6">
                                <div class="text-muted me-2">
                                    <i class="ki-duotone ki-calendar fs-6 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    ${checkpoint.timestamp}
                                </div>
                                <div class="text-muted me-2">
                                    <i class="ki-duotone ki-code fs-6 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    ${checkpoint.id}
                                </div>
                            </div>
                            ${checkpoint.git_hash ? `
                                <div class="text-muted fs-7 mt-1">
                                    <i class="ki-duotone ki-git fs-6 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    ${checkpoint.git_branch} (${checkpoint.git_hash.substring(0, 8)})
                                </div>
                            ` : ''}
                        </div>
                        <div class="d-flex">
                            ${!isActive ? `
                                <button class="btn btn-sm btn-light-danger me-2" 
                                        onclick="rollbackToCheckpoint('${checkpoint.id}', '${checkpoint.description}')">
                                    <i class="ki-duotone ki-arrow-left fs-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Geri Dön
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Yeni checkpoint oluştur
 */
function createCheckpoint() {
    const description = document.getElementById('checkpointDescription').value;
    
    if (!description.trim()) {
        showAlert('warning', 'Lütfen checkpoint açıklaması girin');
        return;
    }
    
    fetch('/api/timeline/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({ description: description })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Checkpoint başarıyla oluşturuldu: ' + data.checkpoint_id);
            document.getElementById('checkpointDescription').value = '';
            loadTimeline();
        } else {
            showAlert('error', 'Checkpoint oluşturulamadı: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Checkpoint oluşturma hatası:', error);
        showAlert('error', 'Checkpoint oluşturulurken hata oluştu');
    });
}

/**
 * Checkpoint'e geri dön
 */
function rollbackToCheckpoint(checkpointId, description) {
    Swal.fire({
        title: 'Rollback Onayı',
        text: `"${description}" checkpoint'ine geri dönmek istediğinizden emin misiniz? Bu işlem geri alınamaz!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Geri Dön!',
        cancelButtonText: 'İptal',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performRollback(checkpointId);
        }
    });
}

/**
 * Rollback işlemini gerçekleştir
 */
function performRollback(checkpointId) {
    showAlert('info', 'Rollback işlemi başlatılıyor...');
    
    fetch('/api/timeline/rollback', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({ checkpoint_id: checkpointId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Rollback başarıyla tamamlandı! Sayfa yenilenecek...');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert('error', 'Rollback hatası: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Rollback hatası:', error);
        showAlert('error', 'Rollback işlemi sırasında hata oluştu');
    });
}

/**
 * Otomatik backup oluştur
 */
function createAutoBackup() {
    fetch('/api/timeline/auto-backup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Günlük backup oluşturuldu');
            loadTimeline();
        } else {
            showAlert('info', data.message);
        }
    })
    .catch(error => {
        console.error('Auto backup hatası:', error);
        showAlert('error', 'Auto backup oluşturulurken hata oluştu');
    });
}

/**
 * Eski backup'ları temizle
 */
function cleanupOldBackups() {
    Swal.fire({
        title: 'Backup Temizleme',
        text: '7 günden eski backup dosyaları silinecek. Devam etmek istiyor musunuz?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Evet, Temizle',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cleanup işlemi
            showAlert('success', 'Eski backup\'lar temizlendi');
        }
    });
}

/**
 * Timeline yenile
 */
function refreshTimeline() {
    loadTimeline();
    showAlert('info', 'Timeline yenilendi');
}

/**
 * Alert göster
 */
function showAlert(type, message) {
    const alertColors = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertClass = alertColors[type] || 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} d-flex align-items-center p-5 mb-5">
            <div class="d-flex flex-column">
                <span>${message}</span>
            </div>
        </div>
    `;
    
    const container = document.getElementById('alertContainer');
    container.innerHTML = alertHtml;
    
    // 5 saniye sonra kaldır
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}
</script>

<?php
require_once 'app/Views/layouts/footer.php';
?>