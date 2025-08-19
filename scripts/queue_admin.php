<?php
declare(strict_types=1);

// Lightweight CLI admin tool for job_queue
// Usage examples:
//   php scripts/queue_admin.php stats
//   php scripts/queue_admin.php reap            # release reserved older than 15 minutes
//   php scripts/queue_admin.php unlock-all      # force release all reserved

use PDO;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

function getDb(): PDO {
    return (new \app\Models\TamsoftStockRepo())->getDb();
}

function stats(PDO $db): void {
    $byStatus = $db->query("SELECT status, COUNT(*) AS c FROM job_queue GROUP BY status ORDER BY status")
        ->fetchAll(PDO::FETCH_ASSOC);
    $byTypeStatus = $db->query("SELECT job_type, status, COUNT(*) AS c FROM job_queue GROUP BY job_type, status ORDER BY job_type, status")
        ->fetchAll(PDO::FETCH_ASSOC);
    $reservedOldest = $db->query("SELECT id, job_type, status, reserved_at, worker_id FROM job_queue WHERE status='reserved' ORDER BY reserved_at ASC LIMIT 10")
        ->fetchAll(PDO::FETCH_ASSOC);

    $out = [
        'by_status' => $byStatus,
        'by_type_status' => $byTypeStatus,
        'reserved_oldest' => $reservedOldest,
    ];
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
}

function reap(PDO $db): void {
    $sql = "UPDATE job_queue SET status='pending', reserved_at=NULL, worker_id=NULL
            WHERE status='reserved' AND (reserved_at IS NULL OR reserved_at < NOW() - INTERVAL 15 MINUTE)";
    $released = $db->exec($sql);
    echo "released=" . (int)$released . "\n";
}

function unlockAll(PDO $db): void {
    $sql = "UPDATE job_queue SET status='pending', reserved_at=NULL, worker_id=NULL WHERE status='reserved'";
    $released = $db->exec($sql);
    echo "released_all=" . (int)$released . "\n";
}

function purge(PDO $db): void {
    // Truncate queue and related run/lock tables
    $db->exec("TRUNCATE TABLE job_queue");
    $db->exec("TRUNCATE TABLE job_runs");
    $db->exec("TRUNCATE TABLE job_lock");
    echo "purged=job_queue,job_runs,job_lock\n";
}

function metrics(PDO $db): void {
    $pending = $db->query("SELECT COUNT(*) FROM job_queue WHERE status='pending'")->fetchColumn();
    $reserved = $db->query("SELECT COUNT(*) FROM job_queue WHERE status='reserved'")->fetchColumn();
    $failed = $db->query("SELECT COUNT(*) FROM job_queue WHERE status='failed'")->fetchColumn();
    $oldestPending = $db->query("SELECT MIN(created_at) FROM job_queue WHERE status='pending'")->fetchColumn();
    $oldestReserved = $db->query("SELECT MIN(reserved_at) FROM job_queue WHERE status='reserved'")->fetchColumn();
    $throughput15 = $db->query("SELECT COUNT(*) FROM job_runs WHERE started_at >= (NOW() - INTERVAL 15 MINUTE) AND status='success'")->fetchColumn();
    $byType = $db->query("SELECT job_type, COUNT(*) c FROM job_queue WHERE status='pending' GROUP BY job_type ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $attempts = $db->query("SELECT attempts, COUNT(*) c FROM job_queue GROUP BY attempts ORDER BY attempts")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $avgDur = $db->query("SELECT job_key, AVG(TIMESTAMPDIFF(SECOND, started_at, finished_at)) avg_sec, COUNT(*) c FROM job_runs WHERE finished_at IS NOT NULL AND started_at IS NOT NULL AND status='success' AND started_at >= (NOW() - INTERVAL 1 DAY) GROUP BY job_key ORDER BY avg_sec DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $out = [
        'pending' => (int)$pending,
        'reserved' => (int)$reserved,
        'failed' => (int)$failed,
        'oldest_pending_at' => $oldestPending ?: null,
        'oldest_reserved_at' => $oldestReserved ?: null,
        'throughput_15min' => (int)$throughput15,
        'pending_by_type' => $byType,
        'attempts' => $attempts,
        'avg_duration_sec_by_job_key_24h' => $avgDur,
    ];
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
}

function usage(int $code = 0): void {
    fwrite($code ? STDERR : STDOUT, "\nqueue_admin.php usage:\n" .
        "  php scripts/queue_admin.php stats\n" .
        "  php scripts/queue_admin.php reap\n" .
        "  php scripts/queue_admin.php unlock-all\n" .
        "  php scripts/queue_admin.php purge\n" .
        "  php scripts/queue_admin.php metrics\n\n");
    exit($code);
}

$action = $argv[1] ?? '';
if ($action === '') {
    usage(1);
}

$db = getDb();

switch ($action) {
    case 'stats':
        stats($db);
        break;
    case 'reap':
        reap($db);
        break;
    case 'unlock-all':
        unlockAll($db);
        break;
    case 'purge':
        purge($db);
        break;
    case 'metrics':
        metrics($db);
        break;
    default:
        usage(1);
}


