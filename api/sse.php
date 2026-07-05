<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    http_response_code(403);
    exit;
}

require_once 'db.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

$last_id = (int)($_GET['last_id'] ?? 0);

while (true) {
    try {
        $stmt = $pdo->prepare("SELECT id, patient, service, date, time, status FROM appointments WHERE id > :last_id ORDER BY id DESC LIMIT 20");
        $stmt->execute(['last_id' => $last_id]);
        $new = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($new)) {
            $last_id = (int)$new[0]['id'];
            echo "data: " . json_encode([
                'appointments' => $new,
                'last_id'      => $last_id
            ]) . "\n\n";
        } else {
            // Heartbeat to keep connection alive
            echo ": ping\n\n";
        }
    } catch (PDOException $e) {
        echo ": db error\n\n";
    }

    ob_flush();
    flush();
    sleep(5); // Check every 5 seconds
}