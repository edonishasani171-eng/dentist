<?php
// admin/register_worker.php
session_start();

// Security check — must be logged in
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// Only admins can access this page
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Akses i ndaluar. Vetëm administratorët mund të regjistrojnë punëtorë të rinj.');
}

$error = '';
$success = '';

// ── HANDLE: REGISTER NEW WORKER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Ju lutem plotësoni Username dhe Password!";
    } elseif (strlen($password) < 6) {
        $error = "Fjalëkalimi duhet të ketë të paktën 6 karaktere!";
    } else {
        try {
            // Check if username already exists
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $check_stmt->execute(['username' => $username]);

            if ($check_stmt->fetch()) {
                $error = "Ky username ekziston tashmë. Ju lutem zgjidhni një tjetër.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $insert_stmt = $pdo->prepare(
                        "INSERT INTO users (username, password, role, status, email, full_name, phone)
                         VALUES (:username, :password, 'staff', 'Active', :email, :full_name, :phone)"
                    );
                    $insert_stmt->execute([
                        'username'  => $username,
                        'password'  => $hashed_password,
                        'email'     => $email !== '' ? $email : null,
                        'full_name' => $full_name !== '' ? $full_name : null,
                        'phone'     => $phone !== '' ? $phone : null,
                    ]);
                } catch (PDOException $colErr) {
                    // Fallback in case the 'phone' column doesn't exist yet in the users table
                    $insert_stmt = $pdo->prepare(
                        "INSERT INTO users (username, password, role, status, email, full_name)
                         VALUES (:username, :password, 'staff', 'Active', :email, :full_name)"
                    );
                    $insert_stmt->execute([
                        'username'  => $username,
                        'password'  => $hashed_password,
                        'email'     => $email !== '' ? $email : null,
                        'full_name' => $full_name !== '' ? $full_name : null,
                    ]);
                }

                $success = "Punëtori u regjistrua me sukses! Ai/Ajo mund të hyjë tani me username: " . htmlspecialchars($username);
            }
        } catch (PDOException $e) {
            $error = "Gabim gjatë regjistrimit: " . $e->getMessage();
        }
    }
}

// ── HANDLE: DELETE WORKER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_worker') {
    $target_id = (int)($_POST['user_id'] ?? 0);

    // Prevent admin from deleting themselves
    if ($target_id === (int)$_SESSION['user_id']) {
        $error = "Nuk mund ta fshini llogarinë tuaj nga këtu.";
    } else {
        try {
            $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
            $del_stmt->execute(['id' => $target_id]);
            $success = "Punëtori u fshi me sukses.";
        } catch (PDOException $e) {
            $error = "Gabim gjatë fshirjes: " . $e->getMessage();
        }
    }
}

// ── FETCH ALL WORKERS (role = staff) ──
try {
    $workers_stmt = $pdo->query(
        "SELECT id, username, full_name, email, phone, status, created_at
         FROM users
         WHERE role = 'staff'
         ORDER BY id DESC"
    );
    $workers = $workers_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if 'phone' column doesn't exist
    try {
        $workers_stmt = $pdo->query(
            "SELECT id, username, full_name, email, status, created_at
             FROM users
             WHERE role = 'staff'
             ORDER BY id DESC"
        );
        $workers = $workers_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($workers as &$w) { $w['phone'] = null; }
        unset($w);
    } catch (PDOException $e2) {
        $workers = [];
    }
}

$staff_name = $_SESSION['username'] ?? 'Admin Staf';
$current_page = 'register_worker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Regjistro Punëtor</title>
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
            --orange:      #df773c;
            --orange-light:#fdf5f2;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
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

        .brand-icon svg { width: 18px; height: 18px; fill: white; }

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

        /* ── MAIN ── */
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

        /* ── ALERTS ── */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            margin-bottom: 24px;
        }

        .alert-error {
            background: #fdf0ef;
            border: 1px solid #f5c6c2;
            color: var(--red);
        }

        .alert-success {
            background: var(--green-light);
            border: 1px solid var(--green-mid);
            color: var(--green-dark);
        }

        /* ── FULLSCREEN RESULT OVERLAY ── */
        .result-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(26,26,24,0.55);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .result-overlay.active { opacity: 1; pointer-events: auto; }

        .result-box {
            background: var(--white);
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 460px;
            width: 90%;
            text-align: center;
            transform: scale(0.92) translateY(14px);
            transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .result-overlay.active .result-box { transform: scale(1) translateY(0); }

        .result-icon {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 36px;
        }

        .result-icon.success { background: var(--green-light); }
        .result-icon.error { background: #fdf0ef; }

        .result-box h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .result-box p {
            color: var(--text-mid);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 26px;
        }

        .result-box button {
            background: var(--green);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 50px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s;
        }

        .result-box button:hover { background: var(--green-dark); }
        .result-box.is-error button { background: var(--red); }
        .result-box.is-error button:hover { background: #a52e22; }

        /* ── WORKER SEARCH BAR ── */
        .worker-search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        #workerSearch {
            width: 240px;
            padding: 9px 14px 9px 34px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background-color: var(--cream);
            color: var(--text);
            outline: none;
            transition: all 0.2s ease;
        }

        #workerSearch:focus {
            border-color: var(--green);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26,122,94,0.12);
        }

        .worker-search-icon {
            position: absolute;
            left: 11px;
            pointer-events: none;
        }

        .worker-search-icon svg {
            width: 13px;
            height: 13px;
            stroke: var(--text-soft);
            fill: none;
            stroke-width: 2.5;
        }

        /* ── LAYOUT GRID ── */
        .register-grid {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── FORM CARD ── */
        .form-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 28px;
        }

        .form-card h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            margin-bottom: 6px;
        }

        .form-card .form-sub {
            font-size: 13px;
            color: var(--text-soft);
            margin-bottom: 24px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        label {
            font-size: 11px;
            font-weight: 500;
            color: var(--text-mid);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 11px 14px;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            width: 100%;
        }

        input:focus {
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 3px rgba(26,122,94,0.1);
        }

        .hint {
            font-size: 11px;
            color: var(--text-soft);
            margin-top: 2px;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            background: var(--green);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
            margin-top: 8px;
        }

        .btn-register:hover { background: var(--green-dark); }

        /* ── WORKERS LIST CARD ── */
        .list-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .list-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .list-card-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }

        th {
            background: #faf9f6;
            padding: 14px 24px;
            color: var(--text-mid);
            font-weight: 500;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: top;
        }

        tr:last-child td { border-bottom: none; }

        .worker-name { font-weight: 500; }
        .worker-meta { font-size: 12px; color: var(--text-soft); margin-top: 2px; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-confirmed { background: var(--green-light); color: var(--green-dark); }
        .badge-cancelled { background: var(--orange-light); color: var(--orange); }

        .btn-delete-worker {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: #df473c;
            color: white;
            transition: background .2s;
        }

        .btn-delete-worker:hover { background: var(--red); }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-soft);
        }

        /* ── CONFIRM MODAL ── */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.active { opacity: 1; pointer-events: auto; }

        .modal-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 420px;
            box-shadow: 0 20px 50px rgba(26,26,24,0.18);
            transform: scale(0.95) translateY(10px);
            transition: transform 0.25s ease;
            padding: 32px;
            text-align: center;
        }

        .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

        .modal-box .icon { font-size: 40px; margin-bottom: 10px; }
        .modal-box h3 { font-size: 20px; margin-bottom: 8px; }
        .modal-box p { color: var(--text-mid); font-size: 14px; margin-bottom: 24px; }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .modal-actions button {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-no { background: #6c757d; color: white; }
        .btn-yes { background: #df473c; color: white; }

        @media (max-width: 1100px) {
            .register-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            aside {
                width: 100%; height: auto; position: relative;
                padding: 16px 20px; flex-direction: row; align-items: center;
                flex-wrap: wrap; gap: 12px; border-right: none; border-bottom: 1px solid var(--border);
            }
            .brand { margin-bottom: 0; flex: 1; }
            .menu { flex-direction: row; flex-wrap: wrap; gap: 4px; width: 100%; }
            .menu-item a { padding: 8px 12px; font-size: 13px; }
            .logout-btn { margin-top: 0; border-top: none; padding-top: 0; border-left: 1px solid var(--border); padding-left: 12px; }
            main { padding: 20px 16px; }
            header { flex-direction: column; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
            .list-card { overflow-x: auto; }
            table { min-width: 560px; }
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

        <ul class="menu">
            <li class="menu-item">
                <a href="admin_dashboard.php?page=dashboard">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_dashboard.php?page=appointments">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Aplikimet
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_dashboard.php?page=schedules">
                    <svg viewBox="0 0 24 24"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 6v6l4 2"></path></svg>
                    Oraret
                </a>
            </li>
            <li class="menu-item active">
                <a href="register_worker.php">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Regjistro Punëtor
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
            <div>
                <h1>Regjistro Punëtor</h1>
            </div>
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr(htmlspecialchars($staff_name), 0, 1)) ?></div>
                <span><?= htmlspecialchars($staff_name) ?></span>
            </div>
        </header>

        <div class="register-grid">
            <!-- REGISTRATION FORM -->
            <div class="form-card">
                <h2>Punëtor i Ri</h2>
                <p class="form-sub">Krijo një llogari të re për stafin, e cila do të lejojë login direkt në panel.</p>

                <form method="POST" action="" id="registerForm">
                    <input type="hidden" name="action" value="register">

                    <div class="field">
                        <label for="full_name">Emri i Plotë</label>
                        <input type="text" id="full_name" name="full_name" placeholder="p.sh. Dr. Arben Krasniqi" value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                    </div>

                    <div class="field">
                        <label for="email">Email (opsionale)</label>
                        <input type="email" id="email" name="email" placeholder="arben@dentcare.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div class="field">
                        <label for="phone">Numri i Telefonit (opsionale)</label>
                        <input type="text" id="phone" name="phone" placeholder="p.sh. 044 123 456" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>

                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="arben.k" required autocomplete="off" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Min. 6 karaktere" required minlength="6">
                        <span class="hint">Punëtori do ta përdorë këtë password për të hyrë në login.php</span>
                    </div>

                    <button type="submit" class="btn-register">Regjistro Punëtorin</button>
                </form>
            </div>

            <!-- WORKERS LIST -->
            <div class="list-card">
                <div class="list-card-header" style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
                    <h2>Punëtorët Aktual (<?= count($workers) ?>)</h2>
                    <div class="worker-search-wrapper">
                        <span class="worker-search-icon">
                            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="text" id="workerSearch" placeholder="Kërko punëtor..." onkeyup="filterWorkers()">
                    </div>
                </div>

                <?php if (count($workers) === 0): ?>
                    <div class="empty-state">
                        <div style="font-size:36px; margin-bottom:10px;">👥</div>
                        <p>Nuk ka punëtorë të regjistruar ende.</p>
                    </div>
                <?php else: ?>
                    <table id="workersTable">
                        <thead>
                            <tr>
                                <th>Punëtori</th>
                                <th>Username</th>
                                <th>Telefoni</th>
                                <th>Statusi</th>
                                <th>Regjistruar</th>
                                <th>Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workers as $w): ?>
                            <tr data-search="<?= strtolower(htmlspecialchars(($w['full_name'] ?? '') . ' ' . $w['username'] . ' ' . ($w['email'] ?? '') . ' ' . ($w['phone'] ?? ''))) ?>">
                                <td>
                                    <div class="worker-name"><?= htmlspecialchars($w['full_name'] ?: '—') ?></div>
                                    <div class="worker-meta"><?= htmlspecialchars($w['email'] ?: 'Pa email') ?></div>
                                </td>
                                <td><?= htmlspecialchars($w['username']) ?></td>
                                <td><?= htmlspecialchars($w['phone'] ?: '—') ?></td>
                                <td>
                                    <?php if ($w['status'] === 'Active'): ?>
                                        <span class="badge badge-confirmed">✓ Aktiv</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelled">✕ Joaktiv</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($w['created_at']) ? date('M d, Y', strtotime($w['created_at'])) : '—' ?></td>
                                <td>
                                    <form id="delete-worker-form-<?= $w['id'] ?>" method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $w['id'] ?>">
                                        <input type="hidden" name="action" value="delete_worker">
                                        <button type="button" class="btn-delete-worker" onclick="triggerDeleteConfirm(<?= $w['id'] ?>, '<?= htmlspecialchars(addslashes($w['full_name'] ?: $w['username'])) ?>')">Fshije</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr id="noWorkerResults" style="display:none;">
                                <td colspan="6" style="text-align:center; padding:40px 20px;">
                                    <div style="font-size:32px; margin-bottom:8px;">🔍</div>
                                    <p style="color:var(--text-soft); font-size:14px;">Nuk u gjet asnjë punëtor.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- DELETE CONFIRM MODAL -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-box">
            <div class="icon">🗑️</div>
            <h3>Fshi Punëtorin?</h3>
            <p id="confirmMessage">A jeni i sigurt që dëshironi të fshini këtë punëtor? Kjo veprim nuk mund të kthehet.</p>
            <div class="modal-actions">
                <button type="button" class="btn-no" onclick="closeConfirmModal()">Jo</button>
                <button type="button" class="btn-yes" id="confirmDeleteBtn">Po, Fshije</button>
            </div>
        </div>
    </div>

    <script>
        let targetForm = null;

        function triggerDeleteConfirm(workerId, workerName) {
            targetForm = document.getElementById('delete-worker-form-' + workerId);
            document.getElementById('confirmMessage').innerText =
                'A jeni i sigurt që dëshironi të fshini "' + workerName + '"? Kjo veprim nuk mund të kthehet.';
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
            targetForm = null;
        }

        document.getElementById('confirmDeleteBtn').onclick = function() {
            if (targetForm) targetForm.submit();
        };

        window.onclick = function(event) {
            const overlay = document.getElementById('confirmModal');
            if (event.target === overlay) closeConfirmModal();
        };
    </script>
</body>
</html>