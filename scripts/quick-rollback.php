<?php
/**
 * Quick Rollback Script
 * Acil durumlar için hızlı geri alma
 */

require_once 'timeline-manager.php';

echo "🚨 ACIL ROLLBACK SİSTEMİ\n";
echo str_repeat("=", 50) . "\n";

$timeline = new TimelineManager();

// Son 5 checkpoint'i göster
echo "📋 Son 5 Checkpoint:\n\n";
$timelineData = json_decode(file_get_contents('scripts/timeline.json'), true) ?: [];
$recentCheckpoints = array_slice($timelineData, 0, 5);

if (empty($recentCheckpoints)) {
    echo "❌ Hiç checkpoint bulunamadı!\n";
    echo "💡 Önce bir checkpoint oluşturun: php scripts/timeline-manager.php create\n";
    exit;
}

foreach ($recentCheckpoints as $index => $checkpoint) {
    $number = $index + 1;
    echo "{$number}. 📅 {$checkpoint['timestamp']} - {$checkpoint['description']}\n";
    echo "   🆔 ID: {$checkpoint['id']}\n\n";
}

echo "⚡ Hangi checkpoint'e geri dönmek istiyorsunuz? (1-5): ";
$choice = trim(fgets(STDIN));

if (!is_numeric($choice) || $choice < 1 || $choice > 5) {
    echo "❌ Geçersiz seçim!\n";
    exit;
}

$selectedCheckpoint = $recentCheckpoints[$choice - 1];

echo "\n🎯 Seçilen checkpoint:\n";
echo "📅 {$selectedCheckpoint['timestamp']}\n";
echo "📝 {$selectedCheckpoint['description']}\n";
echo "🆔 {$selectedCheckpoint['id']}\n\n";

echo "⚠️  Bu işlem geri alınamaz! Devam etmek istiyor musunuz? (evet/hayır): ";
$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 'evet') {
    echo "❌ İşlem iptal edildi.\n";
    exit;
}

echo "\n🔄 Rollback işlemi başlatılıyor...\n";

try {
    $timeline->rollbackToCheckpoint($selectedCheckpoint['id']);
    echo "\n🎉 Rollback başarıyla tamamlandı!\n";
    echo "🔄 Sistem {$selectedCheckpoint['timestamp']} tarihli duruma geri döndü.\n";
} catch (Exception $e) {
    echo "\n❌ Rollback hatası: " . $e->getMessage() . "\n";
}
?>