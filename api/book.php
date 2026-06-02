<?php
// book.php
header('Content-Type: application/json');
require_once 'db.php'; // Using your standard PDO database file

// Read the raw JSON data sent by JavaScript's fetch request
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Të dhëna të pavlefshme!']);
    exit;
}

// Map decoded JSON data keys to clean variables matching JavaScript keys
$patient_name  = trim($data['name'] ?? '');
$patient_phone = trim($data['phone'] ?? '');
$patient_email = trim($data['email'] ?? '');
$service_type  = trim($data['service'] ?? '');
$booking_date  = trim($data['date'] ?? '');
$booking_time  = trim($data['time'] ?? '');
$notes         = trim($data['notes'] ?? '');

// Validation
if (empty($patient_name) || empty($patient_phone) || empty($service_type) || empty($booking_date) || empty($booking_time)) {
    echo json_encode(['success' => false, 'message' => 'Ju lutem plotësoni të gjitha fushat e detyrueshme!']);
    exit;
}

// --- NEW SERVER-SIDE PAST DATE VALIDATION ---
$today_date = date('Y-m-d');
if (strtotime($booking_date) < strtotime($today_date)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Nuk mund të rezervoni një termin në një datë që ka kaluar! Ju lutem zgjidhni një datë të vlefshme.'
    ]);
    exit;
}

try {
    // Optional: Double check if this exact time slot was booked by someone else in the meantime
    $check_stmt = $pdo->prepare("SELECT id FROM appointments WHERE date = :date AND time = :time");
    $check_stmt->execute(['date' => $booking_date, 'time' => $booking_time]);
    
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ky orar sapo u rezervua nga dikush tjetër! Zgjidhni një orar tjetër.']);
        exit;
    }

    // Insert your row using 'Pending' as your standard baseline status condition
    $stmt = $pdo->prepare("INSERT INTO appointments (patient, email, phone, service, date, time, notes, status) 
                           VALUES (:patient, :email, :phone, :service, :date, :time, :notes, 'Pending')");
    
    $stmt->execute([
        'patient' => $patient_name,
        'email'   => $patient_email,
        'phone'   => $patient_phone,
        'service' => $service_type,
        'date'    => $booking_date,
        'time'    => $booking_time,
        'notes'   => $notes
    ]);

    // Return success to trigger the beautiful "You're all booked!" overlay window
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gabim në sistem: ' . $e->getMessage()]);
}
?>