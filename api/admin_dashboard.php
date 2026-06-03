<?php
// admin/admin_dashboard.php
session_start();

$staff_name = $_SESSION['username'] ?? $_SESSION['staff_name'] ?? 'Admin Staf';

// Security check
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

// Track current view state 
$current_page = $_GET['page'] ?? 'dashboard';

// Connect using your existing db config
require_once 'db.php';

// ── 1. IMPORT PHPMAILER FILES ──
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

/**
 * ── 2. AUTOMATED EMAIL DISPATCH FUNCTION ──
 */
function sendStatusEmail($patientEmail, $patientName, $statusType, $appointmentDetails) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;
        
        // 🚨 CONFIGURATION: ENTER YOUR DETAILS HERE 🚨
        $mail->Username   = 'edonishasaniii17@gmail.com';  // <-- Put your real Gmail address here
        $mail->Password   = 'iykuuwcjhhsktplj';        // <-- Paste your 16-character App Password here (no spaces)
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->Timeout    = 3;       // Max seconds to wait for a response from SMTP
        $mail->SMTPKeepAlive = false;
        // Recipients
        $mail->setFrom('edonishasaniii17@gmail.com', 'DentCare Pejë'); // <-- Put your real Gmail address here too
        $mail->addAddress($patientEmail, $patientName);

        // Styling templates based on status updates
        if ($statusType === 'Confirmed') {
            $statusText = 'Konfirmuar';
            $badgeColor = '#1a7a5e';
            $statusDescription = 'Termini juaj është konfirmuar me sukses! Ju mirëpresim në klinikën tonë.';
        } elseif ($statusType === 'Cancelled') {
            $statusText = 'Anuluar';
            $badgeColor = '#df773c';
            $statusDescription = 'Ju njoftojmë se termini juaj është anuluar. Për çdo pyetje ose për të caktuar një kohë tjetër, ju lutem na kontaktoni.';
        } else {
            $statusText = 'Në pritje';
            $badgeColor = '#ba7517';
            $statusDescription = 'Aplikimi juaj për termin është pranuar dhe është në proces shqyrtimi.';
        }

        // Email Design Template
        $mail->isHTML(true);
        $mail->Subject = "Përditësim mbi Terminin Tuaj — DentCare Pejë";
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; background-color: #faf8f4; padding: 30px; color: #1a1a18;'>
            <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(26,26,24,0.1);'>
                <h2 style='color: #1a7a5e; margin-bottom: 5px; font-family: Georgia, serif;'>DentCare Pejë</h2>
                <hr style='border: none; border-top: 1px solid #e0ece3; margin-bottom: 20px;'>
                
                <p style='font-size: 16px; line-height: 1.5;'>Përshëndetje <strong>{$patientName}</strong>,</p>
                <p style='font-size: 15px; line-height: 1.5; color: #4a4a45;'>{$statusDescription}</p>
                
                <div style='margin: 25px 0; padding: 20px; background-color: #faf9f6; border-radius: 8px; border-left: 4px solid {$badgeColor};'>
                    <h4 style='margin-top: 0; color: #1a1a18; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;'>Detajet e Terminit:</h4>
                    <p style='margin: 8px 0; font-size: 14px;'><strong>Shërbimi:</strong> {$appointmentDetails['service']}</p>
                    <p style='margin: 8px 0; font-size: 14px;'><strong>Data & Ora:</strong> {$appointmentDetails['date']} @ {$appointmentDetails['time']}</p>
                    <p style='margin: 8px 0; font-size: 14px;'><strong>Statusi i Ri:</strong> <span style='background-color: {$badgeColor}; color: white; padding: 3px 10px; border-radius: 50px; font-size: 12px; font-weight: bold;'>{$statusText}</span></p>
                </div>
                
                <hr style='border: none; border-top: 1px solid #e0ece3; margin-top: 25px;'>
                <p style='font-size: 12px; color: #8a8a82; text-align: center; margin-top: 20px;'>
                    Kjo është një porosi automatike nga sistemi. Ju lutem mos iu përgjigjeni direkt këtij emaili.<br>
                    <strong>DentCare Pejë</strong> • Rruga Mbretëresha Teutë, Pejë
                </p>
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email delivery error: " . $mail->ErrorInfo);
        return false;
    }
}

// ── 3. HANDLE ACTION: CONFIRM APPOINTMENT WITH AUTOMATION ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
    $target_id = (int)$_POST['appointment_id'];
    
    try {
        // Fetch patient credentials first before running structural data updates
        $stmt = $pdo->prepare("SELECT patient, email, service, date, time FROM appointments WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($app) {
            $update_stmt = $pdo->prepare("UPDATE appointments SET status = 'Confirmed' WHERE id = :id");
            if ($update_stmt->execute(['id' => $target_id])) {
                
                // MBROJTJA PËR RENDER: Izolojmë dërgimin e email-it që mos të bëjë crash faqja
                try {
                    sendStatusEmail($app['email'], $app['patient'], 'Confirmed', $app);
                } catch (Exception $email_error) {
                    // Nëse Render e bllokon portën, regjistrohet këtu por faqja NUK rrëzohet
                    error_log("Email confirmation failed to send on Render: " . $email_error->getMessage());
                }

            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    
    header("Location: admin_dashboard.php?page=" . $current_page);
    exit;
}

// ── 4. HANDLE ACTION: CANCEL APPOINTMENT WITH AUTOMATION ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $target_id = (int)$_POST['appointment_id'];
    
    try {
        // Fetch patient credentials first before running structural data updates
        $stmt = $pdo->prepare("SELECT patient, email, service, date, time FROM appointments WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($app) {
            $cancel_stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = :id");
            if ($cancel_stmt->execute(['id' => $target_id])) {
                
                // MBROJTJA PËR RENDER: Izolojmë dërgimin e email-it që mos të bëjë crash faqja
                try {
                    sendStatusEmail($app['email'], $app['patient'], 'Cancelled', $app);
                } catch (Exception $email_error) {
                    // Nëse dështon dërgimi, ruhet regjistri i gabimit dhe kodi vazhdon
                    error_log("Email cancellation failed to send on Render: " . $email_error->getMessage());
                }

            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    
    header("Location: admin_dashboard.php?page=" . $current_page);
    exit;
}
// ── HANDLE ACTION: MARK AS KRYER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kryer') {
    $target_id = (int)$_POST['appointment_id'];
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET kryer = 1 WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: admin_dashboard.php?page=appointments");
    exit;
}

// ── HANDLE ACTION: MARK AS PA KRYER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pakryer') {
    $target_id = (int)$_POST['appointment_id'];
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET kryer = 2 WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: admin_dashboard.php?page=appointments");
    exit;
}
// ── 5. HANDLE ACTION: DELETE RECORD (No dispatch needed) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $target_id = (int)$_POST['appointment_id'];
    
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
        $delete_stmt->execute(['id' => $target_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    
    header("Location: admin_dashboard.php?page=" . $current_page);
    exit;
}

// Fetch live metrics and rows from the database
try {
    // 1. Fetch appointments
    $query = "SELECT id, patient, email, phone, service, date, time, notes, status, kryer FROM appointments ORDER BY id DESC";
    $stmt = $pdo->query($query);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calculate dynamic dashboard stats
    $pending_count = 0;
    $confirmed_count = 0;
    $cancelled_count = 0; 
    
    foreach ($appointments as $app) {
        if ($app['status'] === 'Pending') {
            $pending_count++;
        } elseif ($app['status'] === 'Confirmed') {
            $confirmed_count++;
        } elseif ($app['status'] === 'Cancelled') {
            $cancelled_count++;
        }
    }

    // Fetch total unique patients based on unique phone numbers
    $patient_count_stmt = $pdo->query("SELECT COUNT(DISTINCT phone) FROM appointments");
    $total_unique_patients = $patient_count_stmt->fetchColumn();

    $kryer_count = 0;
    $pa_kryer_count = 0;
    foreach ($appointments as $app) {
        if ($app['kryer'] == 1) $kryer_count++;
        elseif ($app['kryer'] == 2) $pa_kryer_count++;
    }

    $metrics = [
        'pending' => $pending_count,
        'confirmed' => $confirmed_count,
        'cancelled' => $cancelled_count,
        'total_patients' => count($appointments), 
        'today_appointments' => count($appointments)
    ];

    // ✅ KETU DUHET TE JETË: Leximi i emrit të stafit të kyçur (Jashtë bllokut catch)
    $staff_name = "Admin Staf"; // Emri rezervë nëse diçka dështon
    if (isset($_SESSION['user_id'])) {
        $staff_stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
        $staff_stmt->execute(['id' => $_SESSION['user_id']]);
        $fetched_name = $staff_stmt->fetchColumn();
        if ($fetched_name) {
            $staff_name = $fetched_name;
        }
    }

} catch (PDOException $e) {
    die("Dështoi leximi i të dhënave: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:       #1a7a5e;
            --green-dark:  #0f5441;
            --green-light: #e8f5f1;
            --green-mid:   #c2e8dc;
            --cream:       #faf8f4;
            --cream-dark:  #f0ece3;
            --text:        #1a1a18;
            --text-mid:    #4a4a45;
            --text-soft:   #8a8a82;
            --white:       #ffffff;
            --border:      rgba(26,26,24,0.10);
            --red:         #c0392b;
            --radius:      14px;
            --radius-lg:   16px;
            --radius-md:   12px;
            --radius-sm:   8px;
            --shadow:      0 2px 24px rgba(26,122,94,0.08);
            --yellow:      #ba7517;
            --yellow-light:#fdf6ed;
            
            /* NEW: Color System Tokens for Canceled Assets */
            --orange:       #df773c;
            --orange-light: #fdf5f2;
        }

        *, *::before, *::after { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.4s ease-out, transform 0.4s ease-out;
        }

        /* ── NOISE TEXTURE OVERLAY ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }
        /* Target state: Visible and in its correct position */
        body.page-loaded {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── SIDEBAR ── */
        aside {
            width: 260px;
            background: var(--white);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 24px;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 40px;
            padding-left: 8px;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg { 
            width: 18px; 
            height: 18px; 
            fill: white; 
        }

        .brand-text {
            font-family: 'DM Serif Display', serif;
            font-size: 18px;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .brand-text span { color: var(--green); }

        .menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: var(--text-mid);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }

        .menu-item.active a, .menu-item a:hover {
            background: var(--green-light);
            color: var(--green-dark);
        }

        .menu-item a svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .logout-btn {
            margin-top: auto;
            border-top: 1px solid var(--border);
            padding-top: 24px;
        }

        /* ── MAIN CONTENT LAYER ── */
        main {
            flex: 1;
            padding: 40px 48px;
            overflow-y: auto;
            z-index: 1;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        header h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            letter-spacing: -0.01em;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--white);
            padding: 8px 16px;
            border-radius: 50px;
            border: 1px solid var(--border);
            font-size: 14px;
            font-weight: 500;
        }

        .avatar {
            width: 28px;
            height: 28px;
            background: var(--green-mid);
            color: var(--green-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        /* ── METRICS GRID ── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .card-meta {
            font-size: 12px;
            color: var(--text-soft);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .card-value {
            font-family: 'DM Serif Display', serif;
            font-size: 32px;
            color: var(--text);
        }

        /* ── DATA SECTION ── */
        .section-title {
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            margin-bottom: 16px;
            color: var(--text);
        }

        .table-container {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #faf9f6;
            padding: 16px 24px;
            color: var(--text-mid);
            font-weight: 500;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
        }

        td {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .patient-name {
            font-weight: 500;
            color: var(--text);
        }

        .patient-phone {
            font-size: 13px;
            color: var(--text-soft);
            margin-top: 2px;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-pending {
            background: var(--yellow-light);
            color: var(--yellow);
        }

        .badge-confirmed {
            background: var(--green-light);
            color: var(--green-dark);
        }

        /* NEW: Cancelled Badge Style */
        .badge-cancelled {
            background: var(--orange-light);
            color: var(--orange);
        }

        /* Action Buttons */
        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve {
            background: var(--green);
            color: var(--white);
        }

        .btn-approve:hover { background: var(--green-dark); }

        /* NEW: Cancel Button Style */
        .btn-cancel {
            background: var(--orange);
            color: var(--white);
        }

        .btn-cancel:hover { background: #c6632f; }

        .btn-delete {
            background: #df473c;
            color: var(--white);
        }

        .btn-delete:hover { background: var(--red); }

        .btn-secondary {
            background: var(--cream);
            color: var(--text-mid);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover { background: var(--cream-dark); }

        /* ── MODAL COMPONENT ── */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 520px;
            box-shadow: 0 20px 50px rgba(26, 26, 24, 0.18);
            transform: scale(0.95) translateY(10px);
            transition: transform 0.25s ease;
            padding: 32px;
        }

        .modal-overlay.active .modal-box {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h3 {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 26px;
            color: var(--text-soft);
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
            padding: 0 4px;
        }

        .modal-close:hover { color: var(--text); }

        .modal-body {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-group label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-soft);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: block;
            margin-bottom: 4px;
        }

        .info-group p {
            font-size: 14px;
            color: var(--text);
            font-weight: 500;
        }

        .notes-box {
            background: var(--cream);
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            min-height: 60px;
            font-size: 14px;
            font-style: italic;
            font-weight: 400 !important;
            color: var(--text-mid) !important;
            border: 1px solid var(--border);
            line-height: 1.5;
        }

        /* Modal Footer Action Form Area */
        .modal-footer {
            margin-top: 14px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 1100px) {
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }
        /* ── SEARCH BAR STYLING ── */
        .table-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .table-header-container .section-title {
            margin-bottom: 0; /* Override default margin since container handles spacing */
        }

        .search-box-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        #patientSearch {
            width: 280px;
            padding: 10px 16px 10px 36px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background-color: var(--white);
            color: var(--text);
            transition: all 0.2s ease;
            outline: none;
        }

        #patientSearch:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(26, 122, 94, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            pointer-events: none;
        }

        .search-icon svg {
            width: 14px;
            height: 14px;
            stroke: var(--text-soft);
            fill: none;
            stroke-width: 2.5;
        }
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff; 
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }

        #loadingOverlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .loading-content {
            text-align: center;
            opacity: 0;
            transform: translateY(50px); 
            transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.5s ease;
        }

        /* INTRO: Glides UP */
        #loadingOverlay.active .loading-content {
            opacity: 1;
            transform: translateY(0);
        }

        /* OUTRO: Glides back DOWN */
        #loadingOverlay.exit-active .loading-content {
            opacity: 0;
            transform: translateY(60px);
        }
        .pulse-loader {
            width: 70px;
            height: 70px;
            background: var(--green);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
            box-shadow: 0 10px 25px rgba(26,122,94,0.15);
            animation: pulse-main 2s infinite ease-in-out;
        }

        .pulse-loader::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--green);
            border-radius: 20px;
            opacity: 0.4;
            animation: pulse-ring 2s infinite ease-out;
        }

        .welcome-title {
            font-family: 'DM Serif Display', serif;
            font-size: 32px;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: -0.01em;
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s;
        }

        .welcome-subtitle {
            font-size: 15px;
            color: var(--text-soft);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.5s;
        }

        #loadingOverlay.active .welcome-title,
        #loadingOverlay.active .welcome-subtitle {
            opacity: 1;
            transform: translateY(0);
        }

        #loadingOverlay {
            background-color: var(--cream);
        }

        @keyframes pulse-main {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        .progress-bar-container {
            width: 220px;
            height: 3px;
            background: rgba(26, 122, 94, 0.15);
            border-radius: 999px;
            margin: 20px auto 0;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: var(--green);
            border-radius: 999px;
            transition: width 0.1s linear;
        }
    </style>
</head>
<body>
    <div id="loadingOverlay">
    <div class="loading-content">
        <div class="pulse-loader">
            <svg viewBox="0 0 24 24" style="width:36px;height:36px;fill:white;z-index:2;">
                <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
            </svg>
        </div>
        <h1 class="welcome-title">Mirupafshim</h1>
        <p class="welcome-subtitle">Duke u larguar nga paneli...</p>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" id="logoutProgressBar"></div>
        </div>
    </div>
</div>
    <aside>
        <a href="admin_dashboard.php?page=dashboard" class="brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
                </svg>
            </div>
            <span class="brand-text">Dent<span>Care</span></span>
        </a>

        <ul class="menu">
            <li class="menu-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <a href="admin_dashboard.php?page=dashboard">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
            </li>
            <li class="menu-item <?= $current_page === 'appointments' ? 'active' : '' ?>">
                <a href="admin_dashboard.php?page=appointments">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Aplikimet
                </a>
            </li>
            <li class="menu-item <?= $current_page === 'schedules' ? 'active' : '' ?>">
                <a href="admin_dashboard.php?page=schedules">
                    <svg viewBox="0 0 24 24"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 6v6l4 2"></path></svg>
                    Oraret
                </a>
            </li>
        </ul>

        <div class="menu-item logout-btn">
            <a href="logout.php" style="color: #c0392b;">
                <svg viewBox="0 0 24 24" style="stroke: #c0392b;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Log Out
            </a>
        </div>
    </aside>

    <main>
        <?php if ($current_page === 'dashboard'): ?>
            <header>
                <div>
                    <h1>Staf Dashboard</h1>
                </div>
                <div class="user-profile">
                    <div class="avatar">
                        <?= strtoupper(substr(htmlspecialchars($staff_name), 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($staff_name) ?></span>
                </div>
            </header>

            <div class="metrics-grid">
                <div class="card">
                    <div class="card-meta">Totali i Sotem</div>
                    <div class="card-value"><?= $metrics['today_appointments'] ?></div>
                </div>
                <div class="card">
                    <div class="card-meta">Në pritje</div>
                    <div class="card-value" style="color: var(--yellow);"><?= $metrics['pending'] ?></div>
                </div>
                <div class="card">
                    <div class="card-meta">Konfirmuar</div>
                    <div class="card-value" style="color: var(--green);"><?= $metrics['confirmed'] ?></div>
                </div>
                <div class="card">
                    <div class="card-meta">Totali i Pacientëve</div>
                    <div class="card-value"><?= number_format($metrics['total_patients']) ?></div>
                </div>
            </div>

            <div class="table-header-container">
                <h2 class="section-title">Aplikimet e tashme</h2>
                <div class="search-box-wrapper">
                    <span class="search-icon">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" id="patientSearch" placeholder="Kerko me emer, nr.telefonit ose email" onkeyup="filterTable()">
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Informacioni i Pacientit</th>
                            <th>Shërbimi i Caktuar</th>
                            <th>Data dhe Ora</th>
                            <th>Status</th>
                            <th>Veprime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                        <tr>
                            <td>
                                <div class="patient-name"><?= htmlspecialchars($app['patient']) ?></div>
                                <div class="patient-phone"><?= htmlspecialchars($app['phone']) ?></div>
                                <div style="font-size: 12px; color: var(--text-soft);"><?= htmlspecialchars($app['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($app['service']) ?></td>
                            <td>
                                <div><?= date('M d, Y', strtotime($app['date'])) ?></div>
                                <div style="font-size: 13px; color: var(--text-soft);"><?= htmlspecialchars($app['time']) ?></div>
                            </td>
                            <td>
                                <?php if ($app['status'] === 'Pending'): ?>
                                    <span class="badge badge-pending">⏳ Në pritje</span>
                                <?php elseif ($app['status'] === 'Cancelled'): ?>
                                    <span class="badge badge-cancelled">✕ Anuluar</span>
                                <?php else: ?>
                                    <span class="badge badge-confirmed">✓ Konfirmuar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($app['status'] === 'Pending'): ?>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn btn-approve">Konfirmo</button>
                                        </form>

                                        <form id="cancel-form-<?= $app['id'] ?>" method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="button" class="btn btn-cancel" onclick="triggerCustomConfirm('cancel', <?= $app['id'] ?>)">Anulo</button>
                                        </form>
                                    <?php endif; ?>

                                    <form id="delete-form-<?= $app['id'] ?>" method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="button" class="btn btn-delete" onclick="triggerCustomConfirm('delete', <?= $app['id'] ?>)">Fshije</button>
                                    </form>

                                    <button class="btn btn-secondary" onclick="showDetails(<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>)">View</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="5" style="text-align: center; padding: 50px 20px; background-color: #ffffff;">
                                <div style="font-size: 40px; margin-bottom: 12px;">🔍</div>
                                <h4 style="font-size: 16px; color: var(--text-mid); font-weight: 600; margin: 0 0 6px 0;">
                                    Nuk u gjetën rezultati që përputhen me kërkesat tuaja.
                                </h4>
                                <p style="font-size: 14px; color: var(--text-soft); margin: 0;">
                                    Ju lutemi provoni një term tjetër.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_page === 'appointments'): ?>
    <header>
        <div>
            <h1>Të gjitha Aplikimet</h1>
        </div>
    </header>

    <!-- Stat boxes -->
    <div style="display:flex; gap:16px; margin-bottom:24px;">
        <div style="background:var(--white); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px 24px; box-shadow:var(--shadow); display:flex; flex-direction:column; align-items:center; gap:4px; min-width:110px;">
            <span style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-soft); font-weight:500;">Pa Kryer</span>
            <span style="font-family:'DM Serif Display',serif; font-size:26px; color:var(--yellow);"><?= $pa_kryer_count ?></span>
        </div>
        <div style="background:var(--white); border:1px solid var(--border); border-radius:var(--radius-md); padding:14px 24px; box-shadow:var(--shadow); display:flex; flex-direction:column; align-items:center; gap:4px; min-width:110px;">
            <span style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-soft); font-weight:500;">Kryer</span>
            <span style="font-family:'DM Serif Display',serif; font-size:26px; color:var(--green);"><?= $kryer_count ?></span>
        </div>
    </div>

    <!-- Search bar -->
    <div style="margin-bottom:16px; position:relative; display:inline-flex; align-items:center;">
        <span style="position:absolute; left:12px; pointer-events:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-soft)" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </span>
        <input type="text" id="appSearch" placeholder="Kerko me emer, email ose nr.telefoni"
            onkeyup="filterAppTable()"
            style="width:320px; padding:10px 16px 10px 36px; font-size:13px; font-family:'DM Sans',sans-serif; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--white); color:var(--text); outline:none; transition:all 0.2s;"
            onfocus="this.style.borderColor='var(--green)'; this.style.boxShadow='0 0 0 3px rgba(26,122,94,0.15)';"
            onblur="this.style.borderColor=''; this.style.boxShadow='';">
    </div>

    <div class="table-container">
        <table style="width:100%" id="appTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pacienti</th>
                    <th>Email</th>
                    <th>Shërbimi</th>
                    <th>Orari</th>
                    <th>Shënime</th>
                    <th>Statusi</th>
                    <th>Kryer / Pa Kryer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $app): ?>
                <tr data-search="<?= strtolower(htmlspecialchars($app['patient'] . ' ' . $app['email'] . ' ' . $app['phone'])) ?>">
                    <td>#<?= $app['id'] ?></td>
                    <td><strong><?= htmlspecialchars($app['patient']) ?></strong><br><?= htmlspecialchars($app['phone']) ?></td>
                    <td><?= htmlspecialchars($app['email']) ?></td>
                    <td><?= htmlspecialchars($app['service']) ?></td>
                    <td><?= htmlspecialchars($app['date']) ?> at <?= htmlspecialchars($app['time']) ?></td>
                    <td><span style="color:var(--text-soft); font-size:13px;"><?= htmlspecialchars(substr($app['notes'], 0, 30) . (strlen($app['notes']) > 30 ? '...' : '')) ?></span></td>
                    <td>
                        <?php if ($app['status'] === 'Pending'): ?>
                            <span class="badge badge-pending">⏳ Në pritje</span>
                        <?php elseif ($app['status'] === 'Cancelled'): ?>
                            <span class="badge badge-cancelled">✕ Anuluar</span>
                        <?php else: ?>
                            <span class="badge badge-confirmed">✓ Konfirmuar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($app['kryer'] == 1): ?>
                            <span class="badge badge-confirmed">✓ Kryer</span>
                        <?php elseif ($app['kryer'] == 2): ?>
                            <span class="badge badge-cancelled">✕ Pa Kryer</span>
                        <?php else: ?>
                            <div style="display:flex; gap:6px;">
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                    <input type="hidden" name="action" value="kryer">
                                    <button type="submit" class="btn" style="background:var(--green); color:white; font-size:12px; padding:5px 12px;">✓ Kryer</button>
                                </form>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                    <input type="hidden" name="action" value="pakryer">
                                    <button type="submit" class="btn" style="background:var(--yellow); color:white; font-size:12px; padding:5px 12px;">✕ Pa Kryer</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr id="noAppResults" style="display:none;">
                    <td colspan="8" style="text-align:center; padding:50px 20px;">
                        <div style="font-size:40px; margin-bottom:12px;">🔍</div>
                        <h4 style="font-size:16px; color:var(--text-mid); margin:0 0 6px 0;">Nuk u gjetën rezultate.</h4>
                        <p style="font-size:14px; color:var(--text-soft); margin:0;">Provoni një term tjetër.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

        <?php elseif ($current_page === 'schedules'): ?>
            <header>
                <div>
                    <h1>Oraret</h1>
                </div>
            </header>
            <div class="card">
                <h3 style="margin-bottom:12px; font-family:'DM Serif Display'">Orari Javëor i Funksionimit</h3>
                <p style="color:var(--text-mid); font-size:14px; line-height:1.6;">
                    • Hënë - Premte: 08:00 - 17:00<br>
                    • Shtunë: 09:00 - 15:00<br>
                    • Dielë: Mbyllur
                </p>
            </div>
        <?php endif; ?>
    </main>

    <div id="detailsModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Informacioni i Pacientit</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="info-group">
                    <label>Emri i Pacientit</label>
                    <p id="modal-patient"></p>
                </div>
                <div class="info-row">
                    <div class="info-group">
                        <label>Numri i Telefonit</label>
                        <p id="modal-phone"></p>
                    </div>
                    <div class="info-group">
                        <label>Email Adresa</label>
                        <p id="modal-email"></p>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-group">
                        <label>Shërbimi i Caktuar</label>
                        <p id="modal-service"></p>
                    </div>
                    <div class="info-group">
                        <label>Data dhe Ora</label>
                        <p id="modal-datetime"></p>
                    </div>
                </div>
                <div class="info-group">
                    <label>Shënime shtesë</label>
                    <p id="modal-notes" class="notes-box"></p>
                </div>
                
                <div id="modal-action-footer" class="modal-footer" style="display: none;">
                    <form id="modal-cancel-form" method="POST" action="">
                        <input type="hidden" name="appointment_id" id="modal-cancel-id" value="">
                        <input type="hidden" name="action" value="cancel">
                        <button type="button" class="btn btn-cancel" onclick="triggerModalConfirm('cancel')">Anulo Terminin</button>
                    </form>

                    <form id="modal-delete-form" method="POST" action="">
                        <input type="hidden" name="appointment_id" id="modal-delete-id" value="">
                        <input type="hidden" name="action" value="delete">
                        <button type="button" class="btn btn-delete" onclick="triggerModalConfirm('delete')">Fshije Përgjithmonë</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal-overlay">
        <div class="modal-box">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 40px; margin-bottom: 10px;" id="confirmIcon">⚠️</div>
                <h3 id="confirmTitle" style="font-size: 22px; margin-bottom: 10px; font-family: inherit; color: #333;">Konfirmim</h3>
                <p id="confirmMessage" style="color: #666; font-size: 15px; line-height: 1.5; margin: 0 10px;"></p>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 12px; margin-top: 25px;">
                <button type="button" class="btn" style="background-color: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500;" onclick="closeConfirmModal()">Jo</button>
                <button type="button" id="confirmSubmitBtn" class="btn" style="color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500;">Vazhdo</button>
            </div>
        </div>
    </div>

<script>
    // --- EXISTING FILTER TABLE LOGIC ---
    function filterTable() {
        const input = document.getElementById('patientSearch');
        const filter = input.value.toLowerCase();
        
        const rows = document.querySelectorAll('main table tbody tr:not(#noResultsRow)');
        const noResultsRow = document.getElementById('noResultsRow');
        
        let visibleCount = 0;

        rows.forEach(row => {
            const detailsCell = row.querySelector('td:first-child');
            if (detailsCell) {
                const textValue = detailsCell.textContent || detailsCell.innerText;
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    row.style.display = "";
                    visibleCount++;
                } else {
                    row.style.display = "none";
                }
            }
        });

        if (visibleCount === 0) {
            noResultsRow.style.display = "";
        } else {
            noResultsRow.style.display = "none";
        }
    }
    function filterAppTable() {
    const filter = document.getElementById('appSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#appTable tbody tr:not(#noAppResults)');
    const noResults = document.getElementById('noAppResults');
    let visible = 0;

    rows.forEach(row => {
        const searchVal = row.getAttribute('data-search') || '';
        if (searchVal.includes(filter)) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    noResults.style.display = visible === 0 ? '' : 'none';
    }

    // --- EXISTING VIEW DETAILS MODAL LOGIC ---
    function showDetails(appData) {
        document.getElementById('modal-patient').textContent = appData.patient;
        document.getElementById('modal-phone').textContent = appData.phone;
        document.getElementById('modal-email').textContent = appData.email;
        document.getElementById('modal-service').textContent = appData.service;
        document.getElementById('modal-datetime').textContent = appData.date + ' @ ' + appData.time;
        document.getElementById('modal-notes').textContent = appData.notes ? appData.notes : 'Nuk ka shënime shtesë.';

        const footerArea = document.getElementById('modal-action-footer');
        const cancelForm = document.getElementById('modal-cancel-form');
        const cancelIdInput = document.getElementById('modal-cancel-id');
        const deleteIdInput = document.getElementById('modal-delete-id');

        deleteIdInput.value = appData.id;
        footerArea.style.display = 'flex';

        if (appData.status === 'Pending') {
            cancelIdInput.value = appData.id;
            cancelForm.style.display = 'inline-block';
        } else {
            cancelIdInput.value = '';
            cancelForm.style.display = 'none';
        }

        document.getElementById('detailsModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('detailsModal').classList.remove('active');
    }


    // --- FIXED CUSTOM MODAL CONFIGURATION LOGIC ---
    let targetFormToSubmit = null;

    // Handles row buttons directly from your loop table
    function triggerCustomConfirm(actionType, appointmentId) {
        setupConfirmModal(actionType, document.getElementById(`${actionType}-form-${appointmentId}`));
    }

    // Handles action buttons inside your View Details modal window
    function triggerModalConfirm(actionType) {
        setupConfirmModal(actionType, document.getElementById(`modal-${actionType}-form`));
    }

    // Prepares and displays your matching custom modal popup
    function setupConfirmModal(actionType, formElement) {
        const modal = document.getElementById('confirmModal');
        const title = document.getElementById('confirmTitle'); // Fixed ID mismatch
        const message = document.getElementById('confirmMessage'); // Fixed ID mismatch
        const icon = document.getElementById('confirmIcon'); // Fixed ID mismatch
        const submitBtn = document.getElementById('confirmSubmitBtn'); // Fixed ID mismatch

        targetFormToSubmit = formElement;

        if (actionType === 'cancel') {
            icon.innerHTML = '🛑';
            title.innerText = 'Anulo Terminin?';
            message.innerText = 'A jeni të sigurt që dëshironi të anuloni këtë termin?';
            submitBtn.style.backgroundColor = '#df773c'; // Matches system orange token
            submitBtn.innerText = 'Po, Anuloje';
        } else if (actionType === 'delete') {
            icon.innerHTML = '🗑️';
            title.innerText = 'Fshije Përgjithmonë?';
            message.innerText = 'A jeni të sigurt që dëshironi të fshini plotësisht këtë rezervim? Kjo procedurë nuk mund të kthehet.';
            submitBtn.style.backgroundColor = '#df473c'; // Matches system red button color
            submitBtn.innerText = 'Po, Fshije';
        }

        submitBtn.onclick = function() {
            if (targetFormToSubmit) targetFormToSubmit.submit();
        };

        modal.classList.add('active');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
        targetFormToSubmit = null;
    }

    // Close overlays automatically when clicking away outside modal boxes
    window.onclick = function(event) {
        let detailsOverlay = document.getElementById('detailsModal');
        let confirmOverlay = document.getElementById('confirmModal');
        
        if (event.target == detailsOverlay) {
            closeModal();
        }
        if (event.target == confirmOverlay) {
            closeConfirmModal();
        }
    }
    // --- SMOOTH LOGOUT ANIMATION (matches login animation) ---
    document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.querySelector('a[href*="logout.php"]');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('href');
            const overlay = document.getElementById('loadingOverlay');

            // Step 1: Fade the overlay in
            overlay.classList.add('active');

            // First quick run (mimics the login form-submit flash)
            animateProgress('logoutProgressBar', 500);

            // Second smooth run after reset (mimics the login PHP-return crawl)
            setTimeout(() => {
                const bar = document.getElementById('logoutProgressBar');
                if (bar) bar.style.width = '0%';
                animateProgress('logoutProgressBar', 2200);
            }, 520);

            setTimeout(() => {
                window.location.href = targetUrl;
            }, 2800);
        });
    }
});
    document.addEventListener('DOMContentLoaded', function() {
        // Small timeout ensures the browser registers the initial 0 opacity first
        setTimeout(() => {
            document.body.classList.add('page-loaded');
        }, 50);
    });
    function animateProgress(barId, duration) {
    const bar = document.getElementById(barId);
    if (!bar) return;

    const steps = 60;
    const interval = duration / steps;
    let current = 0;

    // Easing: starts fast, slows near end for natural feel
    const ease = (t) => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;

    const timer = setInterval(() => {
        current++;
        const progress = ease(current / steps) * 100;
        bar.style.width = progress + '%';

        if (current >= steps) {
            clearInterval(timer);
            bar.style.width = '100%';
        }
    }, interval);
}
</script>
</body>
</html>
