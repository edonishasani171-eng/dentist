<?php
// get_slots.php
header('Content-Type: application/json');
require_once 'db.php'; // Loads your standard $pdo connector directly from the root

$date = $_GET['date'] ?? '';

if (empty($date)) {
    echo json_encode(['booked' => []]);
    exit;
}

try {
    // Find all times that are already taken on this specific date
    $stmt = $pdo->prepare("SELECT time FROM appointments WHERE date = :date");
    $stmt->execute(['date' => $date]);
    
    // Flatten array columns down to simple strings like ['09:00', '10:30']
    $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Formatting times to HH:MM just in case database returns them with seconds (e.g. 08:30:00)
    $formatted_slots = array_map(function($time) {
        return substr($time, 0, 5);
    }, $booked_slots);
    
    echo json_encode(['booked' => $formatted_slots]);
} catch (PDOException $e) {
    echo json_encode(['booked' => []]);
}
?>