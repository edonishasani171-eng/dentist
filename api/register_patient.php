<?php
// admin/register_pacient.php
session_start();

$staff_name = $_SESSION['username'] ?? $_SESSION['staff_name'] ?? 'Admin Staf';

// Security check
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$errors  = [];
$success = false;

// ── HANDLE FORM SUBMISSION ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_patient') {
    $patient = trim($_POST['patient'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $date    = trim($_POST['date'] ?? '');
    $time    = trim($_POST['time'] ?? '');

    if ($patient === '') $errors[] = 'Ju lutem shkruani emrin e plotë të pacientit.';
    if ($phone === '')   $errors[] = 'Ju lutem shkruani numrin e telefonit.';
    if ($date === '')    $errors[] = 'Ju lutem zgjidhni një datë.';
    if ($time === '')    $errors[] = 'Ju lutem zgjidhni një orë.';

    // Basic date sanity checks (mirrors the public booking page rules)
    if ($date !== '') {
        $ts = strtotime($date);
        if ($ts === false) {
            $errors[] = 'Data e dhënë nuk është e vlefshme.';
        } else {
            $dayOfWeek = (int) date('w', $ts); // 0 = Sunday, 6 = Saturday
            $today = strtotime(date('Y-m-d'));

            if ($ts < $today) {
                $errors[] = 'Nuk mund të zgjidhni një datë që ka kaluar.';
            }
            if ($dayOfWeek === 0) {
                $errors[] = 'Klinika është e mbyllur të Dielën.';
            }
            if ($dayOfWeek === 6 && $time !== '' && (int) substr($time, 0, 2) >= 15) {
                $errors[] = 'Të Shtunën orari mbyllet në 15:00.';
            }
        }
    }

    // Prevent double-booking the same slot
    if (empty($errors)) {
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE date = :date AND time = :time AND status != 'Cancelled'");
            $check->execute(['date' => $date, 'time' => $time]);
            if ($check->fetchColumn() > 0) {
                $errors[] = 'Ky orar është zënë tashmë. Ju lutem zgjidhni një orë tjetër.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Ndodhi një gabim gjatë kontrollit të orarit.';
        }
    }

    // Insert new record
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (patient, email, phone, service, date, time, notes, status, kryer)
                                    VALUES (:patient, '', :phone, :service, :date, :time, '', 'Confirmed', 0)");
            $stmt->execute([
                'patient' => $patient,
                'phone'   => $phone,
                'service' => 'Regjistrim nga Stafi',
                'date'    => $date,
                'time'    => $time,
            ]);
            header('Location: register_pacient.php?success=1');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Ndodhi një gabim gjatë ruajtjes së pacientit.';
        }
    }
}

if (isset($_GET['success'])) {
    $success = true;
}

// Notification badge count (kept consistent with the rest of the admin panel)
try {
    $pending_stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'");
    $new_appointments_count = (int) $pending_stmt->fetchColumn();
} catch (PDOException $e) {
    $new_appointments_count = 0;
}

$current_page = 'register_pacient';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Regjistro Pacientin</title>
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
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { background: var(--cream); scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR (same as admin_dashboard.php) ── */
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
            width: 32px; height: 32px;
            background: var(--green);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-icon svg { width: 18px; height: 18px; fill: white; }

        .brand-text { font-family: 'DM Serif Display', serif; font-size: 18px; color: var(--text); letter-spacing: -0.02em; }
        .brand-text span { color: var(--green); }

        .menu { list-style: none; display: flex; flex-direction: column; gap: 8px; }

        .menu-item a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            color: var(--text-mid);
            text-decoration: none;
            font-size: 14px; font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }

        .menu-item.active a, .menu-item a:hover { background: var(--green-light); color: var(--green-dark); }

        .menu-item a svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; }

        .notif-badge {
            display: inline-flex; align-items: center; justify-content: center;
            margin-left: auto; min-width: 18px; height: 18px; padding: 0 4px;
            background: #e53935; border: 2px solid #fff; border-radius: 50px;
            color: #fff; font-size: 10px; font-weight: bold;
        }
        .notif-badge.hidden { display: none; }

        .logout-btn { margin-top: auto; border-top: 1px solid var(--border); padding-top: 24px; }

        /* ── MAIN ── */
        main { flex: 1; padding: 40px 48px; overflow-y: auto; height: 100vh; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        header h1 { font-family: 'DM Serif Display', serif; font-size: 28px; letter-spacing: -0.01em; }

        /* ── FORM CARD (design lifted from the public booking page / style.css) ── */
        .register-wrap { max-width: 640px; }

        .form-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            max-width: 700px;
            margin: 0 auto;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        @media (max-width: 768px) {
            main { 
                padding: 20px 16px;
             }
        }
        @media (max-width: 480px) {
            main { 
                padding: 16px 12px;
             }
        }

        .form-section { padding: 28px 32px; border-bottom: 1px solid var(--border); }
        .form-section:last-child { border-bottom: none; }

        .form-section-title { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }

        .section-num {
            width: 26px; height: 26px;
            background: var(--green); color: white; border-radius: 50%;
            font-size: 12px; font-weight: 500;
            display: flex; align-items: center; justify-content: center;
        }

        .form-section-title h3 { font-size: 15px; font-weight: 500; color: var(--text); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-row.single { grid-template-columns: 1fr; }

        .field { display: flex; flex-direction: column; gap: 5px; }

        label {
            font-size: 12px; font-weight: 500; color: var(--text-mid);
            text-transform: uppercase; letter-spacing: 0.06em;
        }

        input[type="text"], input[type="tel"], input[type="date"] {
            padding: 11px 14px;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px; color: var(--text);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            width: 100%;
        }

        input:focus { border-color: var(--green); background: white; box-shadow: 0 0 0 3px rgba(26,122,94,0.1); }
        input::placeholder { color: var(--text-soft); }

        .field-error { font-size: 11px; color: var(--red); margin-top: 3px; display: none; }
        input.error { border-color: var(--red) !important; }

        /* ── TIME SLOTS (identical design to index.php) ── */
        .time-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 4px; }

        .time-btn {
            padding: 10px 6px; text-align: center;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-mid);
            cursor: pointer;
            transition: all .18s;
            font-weight: 400;
        }

        .time-btn:hover:not(.taken) { border-color: var(--green); color: var(--green); background: var(--green-light); }

        .time-btn.selected { background: var(--green); border-color: var(--green); color: white; font-weight: 500; }

        .time-btn.taken {
            background-color: #fdf2f2 !important;
            color: #df473c !important;
            border: 1px solid #f5baba !important;
            text-decoration: line-through;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.65;
        }

        .slots-placeholder {
            grid-column: 1/-1; text-align: center; padding: 24px;
            color: var(--text-soft); font-size: 13px;
            background: var(--cream); border-radius: 10px;
            border: 1px dashed var(--border);
        }

        /* ── SUBMIT ── */
        .submit-area { padding: 24px 32px; }

        .btn-submit {
            width: 100%; padding: 15px;
            background: var(--green); color: white;
            border: none; border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px; font-weight: 500;
            cursor: pointer;
            transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: var(--green-dark); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,122,94,0.25); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── ALERTS ── */
        .alert-banner {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fdf2f2;
            border: 1px solid #f5baba;
            color: #c0392b;
        }
        .alert-success {
            background: var(--green-light);
            border: 1px solid var(--green-mid);
            color: var(--green-dark);
        }
        .alert-banner ul { margin: 6px 0 0 18px; }

        @media (max-width: 768px) {
            body { flex-direction: column; }

            .menu-toggle { display: flex; }

            aside { width: 100%; height: auto; position: relative; padding: 16px 20px; flex-direction: row; align-items: center; flex-wrap: wrap; gap: 12px; border-right: none; border-bottom: 1px solid var(--border); }

            .brand { margin-bottom: 0; flex: 1; }

            .menu {
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--white);
                box-shadow: 0 10px 15px rgba(0,0,0,0.05);
                border-bottom: 0 solid var(--border);
                padding: 0;
                max-height: 0;
                opacity: 0;
                overflow: hidden;
                gap: 8px;
                z-index: 1000;
                transition: max-height 0.5s ease-in-out, opacity 0.3s ease-in-out, padding 0.4s ease-in-out;
            }

            .menu.active {
                max-height: 500px;
                opacity: 1;
                padding: 16px 0;
                border-bottom: 1px solid var(--border);
            }

            .menu-item a { padding: 10px 20px; font-size: 14px; }

            .logout-btn {
                margin-top: 0;
                border-top: 1px solid var(--border);
                padding-top: 0;
                padding-left: 0;
                border-left: none;
                width: 100%;
            }

            .logout-btn a { padding: 12px 20px; }

            main { padding: 20px 16px; }
            .form-row { grid-template-columns: 1fr; }
            .time-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <aside>
        <a href="admin_dashboard.php?page=dashboard" class="brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
                </svg>
            </div>
            <span class="brand-text">Dent<span>Care</span></span>
        </a>

        <button class="menu-toggle" id="menu-toggle" aria-label="Hap menunë">
            <span></span>
            <span></span>
        </button>

        <ul class="menu" id="nav-links">
            <li class="menu-item">
                <a href="admin_dashboard.php?page=dashboard">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                    <span class="notif-badge <?= $new_appointments_count == 0 ? 'hidden' : '' ?>">
                        <?= $new_appointments_count > 99 ? '99+' : $new_appointments_count ?>
                    </span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_dashboard.php?page=appointments">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Aplikimet
                </a>
            </li>
            <li class="menu-item active">
                <a href="register_pacient.php">
                    <svg viewBox="0 0 24 24"><path d="M9 12h6"></path><path d="M12 9v6"></path><rect x="3" y="4" width="18" height="16" rx="2"></rect></svg>
                    Regjistro Pacientin
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_dashboard.php?page=schedules">
                    <svg viewBox="0 0 24 24"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 6v6l4 2"></path></svg>
                    Oraret
                </a>
            </li>
            <li class="menu-item">
                <a href="register_worker.php">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Regjistro Punëtor
                </a>
            </li>
            <li class="menu-item">
                <a href="check_in.php">
                    <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Check In
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
        <header>
            <div><h1>Regjistro Pacientin</h1></div>
            <div class="user-profile" style="display:flex; align-items:center; gap:12px; background:var(--white); padding:8px 16px; border-radius:50px; border:1px solid var(--border); font-size:14px; font-weight:500;">
                <span><?= htmlspecialchars($staff_name) ?></span>
            </div>
        </header>

        <div class="register-wrap">
            <?php if ($success): ?>
                <div class="alert-banner alert-success">✓ Pacienti u regjistrua me sukses!</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-banner alert-error">
                    <strong>Ndodhën këto probleme:</strong>
                    <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="register_pacient.php">
                <input type="hidden" name="action" value="register_patient">
                <input type="hidden" name="time" id="time-input" value="">

                <div class="form-card">
                    <div class="form-section">
                        <div class="form-section-title">
                            <div class="section-num">1</div>
                            <h3>Informacioni i Pacientit</h3>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="patient">Emri i Plotë *</label>
                                <input type="text" id="patient" name="patient" placeholder="Besa Krasniqi">
                                <span class="field-error" id="err-patient">Ju lutem jepni emrin e plotë</span>
                            </div>
                            <div class="field">
                                <label for="phone">Numri i Telefonit *</label>
                                <input type="tel" id="phone" name="phone" placeholder="+383 44 ...">
                                <span class="field-error" id="err-phone">Ju lutem jepni numrin e telefonit</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <div class="section-num">2</div>
                            <h3>Data e Terminit</h3>
                        </div>
                        <div class="form-row single">
                            <div class="field">
                                <label for="date">Data *</label>
                                <input type="date" id="date" name="date" onchange="loadTimeSlots()">
                                <span class="field-error" id="err-date">Ju lutem zgjidhni një datë</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <div class="section-num">3</div>
                            <h3>Zgjidhni një kohë të lirë</h3>
                        </div>
                        <div class="time-grid" id="time-grid">
                            <div class="slots-placeholder">Zgjidhni një datë më lart për të parë kohët e lira</div>
                        </div>
                        <span class="field-error" id="err-time" style="margin-top:8px;">Ju lutem zgjidhni një orë</span>
                    </div>

                    <div class="submit-area">
                        <button type="submit" class="btn-submit" id="submit-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Regjistro Pacientin
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

<script>
    const TIMES = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
    let selectedTime = null;

    document.getElementById('date').min = new Date().toISOString().split('T')[0];

    async function loadTimeSlots() {
        const date = document.getElementById('date').value;
        if (!date) return;

        const grid = document.getElementById('time-grid');
        grid.innerHTML = '<div class="slots-placeholder">Duke ngarkuar oraret...</div>';
        selectedTime = null;
        document.getElementById('time-input').value = '';

        try {
            // NOTE: assumes get_slots.php lives one folder above /admin/. Adjust the path if needed.
            const res = await fetch(`../get_slots.php?date=${date}`);
            const data = await res.json();
            renderSlots(data.booked || []);
        } catch (e) {
            renderSlots([]);
        }
    }

    function renderSlots(booked) {
        const grid = document.getElementById('time-grid');
        grid.innerHTML = '';

        const dateValue = document.getElementById('date').value;
        let isSaturday = false;
        if (dateValue) {
            const parts = dateValue.split('-');
            const selectedDate = new Date(parts[0], parts[1] - 1, parts[2]);
            if (selectedDate.getDay() === 6) isSaturday = true;
        }

        TIMES.forEach(t => {
            if (isSaturday) {
                const hour = parseInt(t.split(':')[0], 10);
                if (hour >= 15) return;
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-btn' + (booked.includes(t) ? ' taken' : '');
            btn.textContent = t;
            btn.disabled = booked.includes(t);

            if (!booked.includes(t)) {
                btn.onclick = () => {
                    document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                    selectedTime = t;
                    document.getElementById('time-input').value = t;
                    document.getElementById('err-time').style.display = 'none';
                };
            }
            grid.appendChild(btn);
        });

        if (grid.children.length === 0) {
            grid.innerHTML = '<div class="slots-placeholder">Nuk ka orare të lira për këtë ditë.</div>';
        }
    }

    document.getElementById('registerForm').addEventListener('submit', function (e) {
        let valid = true;

        const name = document.getElementById('patient').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const date = document.getElementById('date').value;

        toggleError('err-patient', 'patient', !name); if (!name) valid = false;
        toggleError('err-phone', 'phone', !phone); if (!phone) valid = false;
        toggleError('err-date', 'date', !date); if (!date) valid = false;

        if (!selectedTime) {
            document.getElementById('err-time').style.display = 'block';
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    function toggleError(errId, inputId, show) {
        document.getElementById(errId).style.display = show ? 'block' : 'none';
        document.getElementById(inputId).classList.toggle('error', show);
    }
    document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    if (!menuToggle || !navLinks) return;
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navLinks.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });
    document.querySelectorAll('.menu a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });
});
</script>
</body>
</html>