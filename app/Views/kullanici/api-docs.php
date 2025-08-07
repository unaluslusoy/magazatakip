<?php
require_once __DIR__ . '/layouts/layout/header.php';
require_once __DIR__ . '/layouts/layout/navbar.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Dokümantasyonu - Magaza Takip Sistemi</title>
    <script src="/app/Views/kullanici/api-service.js"></script>
    <style>
        .endpoint-card {
            border-left: 4px solid #007bff;
            margin-bottom: 1rem;
        }
        .endpoint-card.get { border-left-color: #28a745; }
        .endpoint-card.post { border-left-color: #ffc107; }
        .endpoint-card.put { border-left-color: #17a2b8; }
        .endpoint-card.delete { border-left-color: #dc3545; }
        
        .method-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            color: white;
            font-weight: bold;
        }
        .method-get { background-color: #28a745; }
        .method-post { background-color: #ffc107; color: #212529; }
        .method-put { background-color: #17a2b8; }
        .method-delete { background-color: #dc3545; }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        
        .test-section {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="ki-outline ki-code fs-2 me-2"></i>
                    API Dokümantasyonu
                </h1>
                
                <div class="alert alert-info">
                    <i class="ki-outline ki-information fs-2 me-2"></i>
                    Bu sayfa, Magaza Takip Sistemi'nin tüm API endpoint'lerini ve kullanım örneklerini içerir.
                </div>
            </div>
        </div>

        <!-- Genel Bilgiler -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Genel Bilgiler</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Base URL</h6>
                                <div class="code-block">https://magazatakip.com.tr/api</div>
                                
                                <h6 class="mt-3">Content Type</h6>
                                <div class="code-block">application/json</div>
                                
                                <h6 class="mt-3">Authentication</h6>
                                <p>Session tabanlı kimlik doğrulama kullanılır. Kullanıcı girişi yapılmış olmalıdır.</p>
                            </div>
                            <div class="col-md-6">
                                <h6>HTTP Status Codes</h6>
                                <ul>
                                    <li><strong>200</strong> - Başarılı</li>
                                    <li><strong>400</strong> - Hatalı istek</li>
                                    <li><strong>401</strong> - Kimlik doğrulama gerekli</li>
                                    <li><strong>404</strong> - Bulunamadı</li>
                                    <li><strong>500</strong> - Sunucu hatası</li>
                                </ul>
                                
                                <h6 class="mt-3">Response Format</h6>
                                <div class="code-block">
{
  "success": true,
  "data": {...},
  "message": "İşlem başarılı",
  "timestamp": 1640995200
}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kullanıcı API'leri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="ki-outline ki-profile-user fs-2 me-2"></i>Kullanıcı API'leri</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Profil Getir -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Kullanıcı Profili Getir</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/user/profile</div>
                            <p class="text-muted">Kullanıcının profil bilgilerini getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testUserProfile()">
                                    Test Et
                                </button>
                                <div id="user-profile-result" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Dashboard Stats -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Dashboard İstatistikleri</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/user/dashboard-stats</div>
                            <p class="text-muted">Dashboard için gerekli tüm istatistikleri getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testDashboardStats()">
                                    Test Et
                                </button>
                                <div id="dashboard-stats-result" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Sistem Durumu</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/user/system-status</div>
                            <p class="text-muted">Sistem durumu ve performans bilgilerini getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testSystemStatus()">
                                    Test Et
                                </button>
                                <div id="system-status-result" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ciro API'leri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="ki-outline ki-chart-line-up fs-2 me-2"></i>Ciro API'leri</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Ciro Listesi -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Ciro Listesi</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/ciro/liste</div>
                            <p class="text-muted">Tüm ciro kayıtlarını getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testCiroList()">
                                    Test Et
                                </button>
                                <div id="ciro-list-result" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Mağaza Listesi -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Mağaza Listesi</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/magazalar</div>
                            <p class="text-muted">Tüm mağazaları getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testMagazalar()">
                                    Test Et
                                </button>
                                <div id="magazalar-result" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gider API'leri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="ki-outline ki-chart-line-down fs-2 me-2"></i>Gider API'leri</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Gider Listesi -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Gider Listesi</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/gider/liste</div>
                            <p class="text-muted">Tüm gider kayıtlarını getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testGiderList()">
                                    Test Et
                                </button>
                                <div id="gider-list-result" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Gider İstatistikleri -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Gider İstatistikleri</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/gider/stats</div>
                            <p class="text-muted">Gider istatistiklerini getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testGiderStats()">
                                    Test Et
                                </button>
                                <div id="gider-stats-result" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- İş Emri API'leri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="ki-outline ki-notepad-edit fs-2 me-2"></i>İş Emri API'leri</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- İş Emri Listesi -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">İş Emri Listesi</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/is-emri/liste</div>
                            <p class="text-muted">Tüm iş emirlerini getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testIsEmriList()">
                                    Test Et
                                </button>
                                <div id="is-emri-list-result" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- İş Emri İstatistikleri -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">İş Emri İstatistikleri</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/is-emri/stats</div>
                            <p class="text-muted">İş emri istatistiklerini getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testIsEmriStats()">
                                    Test Et
                                </button>
                                <div id="is-emri-stats-result" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bildirim API'leri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="ki-outline ki-notification fs-2 me-2"></i>Bildirim API'leri</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Bildirim Listesi -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Bildirim Listesi</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/bildirim/liste</div>
                            <p class="text-muted">Tüm bildirimleri getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testBildirimList()">
                                    Test Et
                                </button>
                                <div id="bildirim-list-result" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Okunmamış Bildirim Sayısı -->
                        <div class="endpoint-card get p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Okunmamış Bildirim Sayısı</h6>
                                <span class="method-badge method-get">GET</span>
                            </div>
                            <div class="code-block mb-2">/api/bildirim/okunmamis-sayi</div>
                            <p class="text-muted">Okunmamış bildirim sayısını getirir.</p>
                            
                            <div class="test-section">
                                <button class="btn btn-sm btn-outline-primary" onclick="testUnreadCount()">
                                    Test Et
                                </button>
                                <div id="unread-count-result" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test fonksiyonları
        async function testUserProfile() {
            try {
                apiHelpers.showLoading();
                const result = await userApiService.getProfile();
                document.getElementById('user-profile-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Profil bilgileri başarıyla getirildi');
            } catch (error) {
                document.getElementById('user-profile-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Profil getirme hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testDashboardStats() {
            try {
                apiHelpers.showLoading();
                const result = await userApiService.getDashboardStats();
                document.getElementById('dashboard-stats-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Dashboard istatistikleri başarıyla getirildi');
            } catch (error) {
                document.getElementById('dashboard-stats-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('İstatistik getirme hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testSystemStatus() {
            try {
                apiHelpers.showLoading();
                const result = await userApiService.getSystemStatus();
                document.getElementById('system-status-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Sistem durumu başarıyla getirildi');
            } catch (error) {
                document.getElementById('system-status-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Sistem durumu hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testCiroList() {
            try {
                apiHelpers.showLoading();
                const result = await ciroApiService.getCiroListesi();
                document.getElementById('ciro-list-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Ciro listesi başarıyla getirildi');
            } catch (error) {
                document.getElementById('ciro-list-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Ciro listesi hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testMagazalar() {
            try {
                apiHelpers.showLoading();
                const result = await ciroApiService.getMagazalar();
                document.getElementById('magazalar-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Mağaza listesi başarıyla getirildi');
            } catch (error) {
                document.getElementById('magazalar-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Mağaza listesi hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testGiderList() {
            try {
                apiHelpers.showLoading();
                const result = await giderApiService.getGiderListesi();
                document.getElementById('gider-list-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Gider listesi başarıyla getirildi');
            } catch (error) {
                document.getElementById('gider-list-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Gider listesi hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testGiderStats() {
            try {
                apiHelpers.showLoading();
                const result = await giderApiService.getGiderStats();
                document.getElementById('gider-stats-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Gider istatistikleri başarıyla getirildi');
            } catch (error) {
                document.getElementById('gider-stats-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Gider istatistikleri hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testIsEmriList() {
            try {
                apiHelpers.showLoading();
                const result = await isEmriApiService.getIsEmriListesi();
                document.getElementById('is-emri-list-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('İş emri listesi başarıyla getirildi');
            } catch (error) {
                document.getElementById('is-emri-list-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('İş emri listesi hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testIsEmriStats() {
            try {
                apiHelpers.showLoading();
                const result = await isEmriApiService.getIsEmriStats();
                document.getElementById('is-emri-stats-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('İş emri istatistikleri başarıyla getirildi');
            } catch (error) {
                document.getElementById('is-emri-stats-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('İş emri istatistikleri hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testBildirimList() {
            try {
                apiHelpers.showLoading();
                const result = await bildirimApiService.getBildirimListesi();
                document.getElementById('bildirim-list-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Bildirim listesi başarıyla getirildi');
            } catch (error) {
                document.getElementById('bildirim-list-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Bildirim listesi hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }

        async function testUnreadCount() {
            try {
                apiHelpers.showLoading();
                const result = await bildirimApiService.getUnreadCount();
                document.getElementById('unread-count-result').innerHTML = 
                    `<pre class="code-block">${JSON.stringify(result, null, 2)}</pre>`;
                apiHelpers.showSuccess('Okunmamış bildirim sayısı başarıyla getirildi');
            } catch (error) {
                document.getElementById('unread-count-result').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
                apiHelpers.showError('Okunmamış bildirim sayısı hatası: ' + error.message);
            } finally {
                apiHelpers.hideLoading();
            }
        }
    </script>
</body>
</html> 