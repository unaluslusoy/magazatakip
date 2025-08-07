<?php
require_once __DIR__ . '/../layouts/layout/header.php';
require_once __DIR__ . '/../layouts/layout/navbar.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test - Ciro Yönetimi</title>
    <script src="/app/Views/kullanici/ciro/api-service.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1>API Test Sayfası</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>API Testleri</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary mb-2" onclick="testGetCiroListesi()">Ciro Listesi Getir</button>
                        <button class="btn btn-success mb-2" onclick="testGetMagazalar()">Mağaza Listesi Getir</button>
                        <button class="btn btn-warning mb-2" onclick="testAddCiro()">Test Ciro Ekle</button>
                        <button class="btn btn-info mb-2" onclick="clearResults()">Sonuçları Temizle</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>API Sonuçları</h5>
                    </div>
                    <div class="card-body">
                        <pre id="api-results" style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px;"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Gerçek Zamanlı Ciro Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div id="real-time-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const apiService = window.ciroApiService;
        
        function logResult(title, data) {
            const resultsDiv = document.getElementById('api-results');
            const timestamp = new Date().toLocaleTimeString('tr-TR');
            const logEntry = `
=== ${title} (${timestamp}) ===
${JSON.stringify(data, null, 2)}
=====================================

`;
            resultsDiv.textContent += logEntry;
            resultsDiv.scrollTop = resultsDiv.scrollHeight;
        }
        
        async function testGetCiroListesi() {
            try {
                logResult('Ciro Listesi İsteği Başlatıldı', {});
                const response = await apiService.getCiroListesi();
                logResult('Ciro Listesi Başarılı', response);
                updateRealTimeList(response.data);
            } catch (error) {
                logResult('Ciro Listesi Hatası', { error: error.message });
            }
        }
        
        async function testGetMagazalar() {
            try {
                logResult('Mağaza Listesi İsteği Başlatıldı', {});
                const response = await apiService.getMagazalar();
                logResult('Mağaza Listesi Başarılı', response);
            } catch (error) {
                logResult('Mağaza Listesi Hatası', { error: error.message });
            }
        }
        
        async function testAddCiro() {
            try {
                const testData = {
                    magaza_id: 1,
                    gun: '2025-01-15',
                    nakit: '1000.50',
                    kredi_karti: '500.25',
                    carliston: '100.00',
                    getir_carsi: '50.00',
                    trendyolgo: '75.00',
                    multinet: '25.00',
                    sodexo: '30.00',
                    edenred: '20.00',
                    setcard: '15.00',
                    tokenflex: '10.00',
                    iwallet: '5.00',
                    metropol: '8.00',
                    ticket: '12.00',
                    didi: '3.00',
                    toplam: '1853.80',
                    aciklama: 'API Test Kaydı'
                };
                
                logResult('Test Ciro Ekleme İsteği Başlatıldı', testData);
                const response = await apiService.addCiro(testData);
                logResult('Test Ciro Ekleme Başarılı', response);
                
                // Listeyi yenile
                setTimeout(() => testGetCiroListesi(), 1000);
            } catch (error) {
                logResult('Test Ciro Ekleme Hatası', { error: error.message });
            }
        }
        
        function updateRealTimeList(ciroListesi) {
            const listDiv = document.getElementById('real-time-list');
            
            if (!ciroListesi || ciroListesi.length === 0) {
                listDiv.innerHTML = '<p class="text-muted">Henüz ciro kaydı bulunmuyor</p>';
                return;
            }
            
            const table = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mağaza</th>
                            <th>Gün</th>
                            <th>Nakit</th>
                            <th>Kredi Kartı</th>
                            <th>Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${ciroListesi.map(ciro => `
                            <tr>
                                <td>${ciro.id}</td>
                                <td>${ciro.magaza_adi || ciro.magaza_id}</td>
                                <td>${new Date(ciro.gun).toLocaleDateString('tr-TR')}</td>
                                <td>${formatMoney(ciro.nakit)}</td>
                                <td>${formatMoney(ciro.kredi_karti)}</td>
                                <td><strong>${formatMoney(ciro.toplam)}</strong></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            listDiv.innerHTML = table;
        }
        
        function formatMoney(value) {
            if (!value || value == 0) return '0,00 ₺';
            return new Intl.NumberFormat('tr-TR', {
                style: 'currency',
                currency: 'TRY'
            }).format(value);
        }
        
        function clearResults() {
            document.getElementById('api-results').textContent = '';
        }
        
        // Sayfa yüklendiğinde otomatik test
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => testGetCiroListesi(), 1000);
        });
    </script>
</body>
</html> 