<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    http_response_code(403);
    echo json_encode(['appointments' => []]);
    exit;
}

require_once 'db.php';

$last_id = (int)($_GET['last_id'] ?? 0);

try {
    $stmt = $pdo->prepare(
        "SELECT id, patient, email, phone, service, date, time, notes, status
         FROM appointments
         WHERE id > :last_id
         ORDER BY id DESC"
    );
    $stmt->execute(['last_id' => $last_id]);
    $new = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'appointments' => $new,
        'last_id'      => !empty($new) ? (int)$new[0]['id'] : $last_id
    ]);
} catch (PDOException $e) {
    echo json_encode(['appointments' => [], 'error' => $e->getMessage()]);
}