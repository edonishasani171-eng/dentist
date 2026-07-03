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
$allowed_page_roles = ['admin', 'manager'];
if (!in_array($_SESSION['user_role'] ?? '', $allowed_page_roles)) {
    http_response_code(403);
    die('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses i Ndaluar — DentCare</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #1a7a5e; --green-dark: #0f5441; --green-light: #e8f5f1;
            --cream: #faf8f4; --text: #1a1a18; --text-mid: #4a4a45;
            --text-soft: #8a8a82; --white: #ffffff; --border: rgba(26,26,24,0.10);
            --red: #c0392b;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "DM Sans", sans-serif;
            background: var(--cream);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(26,122,94,0.08);
            animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .icon-wrap {
            width: 72px; height: 72px;
            background: #fdf0ef;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        h1 {
            font-family: "DM Serif Display", serif;
            font-size: 24px;
            color: var(--text);
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }
        p {
            font-size: 14px;
            color: var(--text-soft);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin-bottom: 24px;
        }
        .prompt {
            font-size: 14px;
            color: var(--text-mid);
            margin-bottom: 16px;
        }
        .btn-login {
            display: inline-block;
            background: var(--green);
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            font-family: "DM Sans", sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
            text-decoration: none;
        }
        .btn-login:hover { background: var(--green-dark); }
        .back-link {
            display: block;
            margin-top: 16px;
            font-size: 13px;
            color: var(--text-soft);
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--green); }

        /* LOGIN MODAL */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(26,26,24,0.5);
            backdrop-filter: blur(5px);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: var(--white);
            border-radius: 16px;
            padding: 32px;
            width: 90%;
            max-width: 380px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            transform: scale(0.92) translateY(16px);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.16,1,0.3,1), opacity 0.3s ease;
        }
        .modal-overlay.active .modal-box { transform: scale(1) translateY(0); opacity: 1; }
        .modal-box h2 {
            font-family: "DM Serif Display", serif;
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--text);
        }
        .field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .field label {
            font-size: 11px; font-weight: 500; color: var(--text-mid);
            text-transform: uppercase; letter-spacing: 0.06em;
        }
        .field input {
            padding: 11px 14px;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: "DM Sans", sans-serif;
            font-size: 14px; color: var(--text); outline: none;
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }
        .field input:focus {
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 3px rgba(26,122,94,0.1);
        }
        .error-msg {
            background: #fdf0ef; border: 1px solid #f5c6c2;
            color: var(--red); padding: 10px 14px;
            border-radius: 8px; font-size: 13px;
            margin-bottom: 14px; display: none;
        }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px; }
        .btn-cancel {
            padding: 10px 18px; border-radius: 8px;
            border: 1px solid var(--border); background: var(--cream);
            color: var(--text-mid); cursor: pointer; font-size: 14px;
        }
        .btn-submit {
            padding: 10px 20px; border-radius: 8px; border: none;
            background: var(--green); color: white;
            cursor: pointer; font-size: 14px; font-weight: 500;
            transition: background .2s;
        }
        .btn-submit:hover { background: var(--green-dark); }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon-wrap">🔒</div>
        <h1>Akses i Kufizuar</h1>
        <p>Ju nuk keni leje për të hyrë në këtë faqe. Kjo zonë është e rezervuar vetëm për administratorët dhe menaxherët.</p>
        <hr class="divider">
        <p class="prompt">Dëshironi të hyni me një llogari tjetër?</p>
        <button class="btn-login" onclick="openLoginModal()">Hyr si Administrator</button>
        <a href="admin_dashboard.php" class="back-link">← Kthehu tek Dashboard</a>
    </div>

    <div id="loginModal" class="modal-overlay">
        <div class="modal-box">
            <h2>Hyrja e Administratorit</h2>
            <div class="error-msg" id="loginError"></div>
            <form id="adminLoginForm">
                <div class="field">
                    <label>Username</label>
                    <input type="text" id="adminUsername" placeholder="username..." autocomplete="off">
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" id="adminPassword" placeholder="fjalëkalimi...">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeLoginModal()">Anulo</button>
                    <button type="submit" class="btn-submit">Hyr</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLoginModal() {
            setTimeout(() => document.getElementById("loginModal").classList.add("active"), 10);
        }
        function closeLoginModal() {
            document.getElementById("loginModal").classList.remove("active");
        }
        window.onclick = function(e) {
            const m = document.getElementById("loginModal");
            if (e.target === m) closeLoginModal();
        };
        document.getElementById("adminLoginForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const username = document.getElementById("adminUsername").value.trim();
            const password = document.getElementById("adminPassword").value;
            const errorEl = document.getElementById("loginError");

            if (!username || !password) {
                errorEl.textContent = "Ju lutem plotësoni të dyja fushat.";
                errorEl.style.display = "block";
                return;
            }

            const form = document.createElement("form");
            form.method = "POST";
            form.action = "login.php";
            const u = document.createElement("input");
            u.type = "hidden"; u.name = "username"; u.value = username;
            const p = document.createElement("input");
            p.type = "hidden"; p.name = "password"; p.value = password;
            form.appendChild(u); form.appendChild(p);
            document.body.appendChild(form);
            form.submit();
        });
    </script>
</body>
</html>');
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
    $allowed_roles = ['staff', 'dentist', 'receptionist', 'manager', 'admin'];
    $role      = in_array(trim($_POST['role'] ?? ''), $allowed_roles) ? trim($_POST['role']) : 'staff';

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
                         VALUES (:username, :password, :role, 'Active', :email, :full_name, :phone)"
                    );
                    $insert_stmt->execute([
                        'username'  => $username,
                        'password'  => $hashed_password,
                        'role'      => $role,
                        'email'     => $email !== '' ? $email : null,
                        'full_name' => $full_name !== '' ? $full_name : null,
                        'phone'     => $phone !== '' ? $phone : null,
                    ]);
                } catch (PDOException $colErr) {
                    // Fallback in case the 'phone' column doesn't exist yet in the users table
                    $insert_stmt = $pdo->prepare(
                        "INSERT INTO users (username, password, role, status, email, full_name)
                         VALUES (:username, :password, :role, 'Active', :email, :full_name)"
                    );
                    $insert_stmt->execute([
                        'username'  => $username,
                        'password'  => $hashed_password,
                        'role'      => $role,
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
            $stmt = $pdo->prepare("
            UPDATE users
            SET status = 'Inactive'
            WHERE id = :id
            AND role != 'admin'
        ");

        $stmt->execute([
            'id' => $target_id
        ]);

        $success = "Punëtori u çaktivizua me sukses.";
        } catch (PDOException $e) {
            $error = "Gabim gjatë fshirjes: " . $e->getMessage();
        }
    }
}
// ── HANDLE: ACTIVATE WORKER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'activate_worker') {

    $target_id = (int)($_POST['user_id'] ?? 0);

    try {

        $stmt = $pdo->prepare("
            UPDATE users
            SET status = 'Active'
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $target_id
        ]);

        $success = "Punëtori u aktivizua me sukses.";

    } catch (PDOException $e) {

        $error = "Gabim gjatë aktivizimit: " . $e->getMessage();

    }
}

// ── HANDLE: EDIT WORKER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_worker') {
    $target_id  = (int)($_POST['user_id'] ?? 0);
    $username   = trim($_POST['username'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $edit_allowed_roles = ['staff', 'dentist', 'receptionist', 'manager', 'admin'];
    $edit_role  = in_array(trim($_POST['role'] ?? ''), $edit_allowed_roles) ? trim($_POST['role']) : 'staff';

    if (empty($username)) {
        $error = "Username nuk mund të jetë bosh!";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Fjalëkalimi i ri duhet të ketë të paktën 6 karaktere!";
    } else {
        try {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "UPDATE users SET username=:username, full_name=:full_name, email=:email, phone=:phone, role=:role, password=:password, updated_at=CURRENT_TIMESTAMP WHERE id=:id AND role!='admin'"
                );
                $stmt->execute([
                    'username'  => $username,
                    'full_name' => $full_name !== '' ? $full_name : null,
                    'email'     => $email !== '' ? $email : null,
                    'phone'     => $phone !== '' ? $phone : null,
                    'role'      => $edit_role,
                    'password'  => $hashed,
                    'id'        => $target_id,
                ]);
            } else {
                try {
                    $stmt = $pdo->prepare(
                        "UPDATE users SET username=:username, full_name=:full_name, email=:email, phone=:phone, role=:role, updated_at=CURRENT_TIMESTAMP WHERE id=:id AND role!='admin'"
                    );
                    $stmt->execute([
                        'username'  => $username,
                        'full_name' => $full_name !== '' ? $full_name : null,
                        'email'     => $email !== '' ? $email : null,
                        'phone'     => $phone !== '' ? $phone : null,
                        'role'      => $edit_role,
                        'id'        => $target_id,
                    ]);
                } catch (PDOException $colErr) {
                    $stmt = $pdo->prepare(
                        "UPDATE users SET username=:username, full_name=:full_name, email=:email, role=:role, updated_at=CURRENT_TIMESTAMP WHERE id=:id AND role!='admin'"
                    );
                    $stmt->execute([
                        'username'  => $username,
                        'full_name' => $full_name !== '' ? $full_name : null,
                        'email'     => $email !== '' ? $email : null,
                        'role'      => $edit_role,
                        'id'        => $target_id,
                    ]);
                }
            }
            $success = "Të dhënat e punëtorit u përditësuan me sukses!";
        } catch (PDOException $e) {
            $error = "Gabim gjatë përditësimit: " . $e->getMessage();
        }
    }
}


try {
    $workers_stmt = $pdo->query(
        "SELECT id, username, full_name, email, phone, role, status, created_at
         FROM users
         WHERE role != 'admin'
         ORDER BY id DESC"
    );
    $workers = $workers_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if 'phone' column doesn't exist
    try {
        $workers_stmt = $pdo->query(
            "SELECT id, username, full_name, email, role, status, created_at
             FROM users
             WHERE role != 'admin'
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
            transform: scale(0.88) translateY(20px);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.16,1,0.3,1),
                        opacity 0.3s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .result-overlay.active .result-box {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

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
        input[type="password"],
        select {
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

        .btn-activate-worker{
            background:#28a745;
            color:#fff;
            border:none;
            border-radius:8px;
            padding:8px 16px;
            cursor:pointer;
            transition:.3s;
        }

        .btn-activate-worker:hover{
            background:#218838;
        }

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
        /* ── CUSTOM ROLE SELECT ── */
        .custom-select-wrapper {
            position: relative;
            user-select: none;
        }

        .custom-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 14px;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .custom-select-trigger:hover,
        .custom-select-wrapper.open .custom-select-trigger {
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 3px rgba(26,122,94,0.1);
        }

        .custom-select-arrow {
            width: 14px;
            height: 14px;
            stroke: var(--text-soft);
            fill: none;
            stroke-width: 2.5;
            transition: transform 0.25s ease;
            flex-shrink: 0;
        }

        .custom-select-wrapper.open .custom-select-arrow {
            transform: rotate(180deg);
        }

        .custom-select-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(26,26,24,0.12);
            z-index: 999;
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transform: translateY(-6px);
            transition: max-height 0.3s cubic-bezier(0.16,1,0.3,1),
                        opacity 0.2s ease,
                        transform 0.25s cubic-bezier(0.16,1,0.3,1);
            pointer-events: none;
        }

        .custom-select-wrapper.open .custom-select-dropdown {
            max-height: 300px;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .custom-select-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: var(--text-mid);
            cursor: pointer;
            transition: background .15s;
        }

        .custom-select-option:hover {
            background: var(--green-light);
            color: var(--green-dark);
        }

        .custom-select-option.selected {
            color: var(--green-dark);
            font-weight: 500;
        }

        .custom-select-option .role-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
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

                    <?php
                        // Only repopulate fields when there was an error (preserve user input).
                        // On success, all fields are cleared so the form is ready for the next entry.
                        $keep = !empty($error) && ($_POST['action'] ?? '') === 'register';
                    ?>
                    <div class="field">
                        <label for="full_name">Emri i Plotë</label>
                        <input type="text" id="full_name" name="full_name" placeholder="p.sh. Dr. Arben Krasniqi" value="<?= $keep ? htmlspecialchars($_POST['full_name'] ?? '') : '' ?>">
                    </div>

                    <div class="field">
                        <label for="email">Email (opsionale)</label>
                        <input type="email" id="email" name="email" placeholder="arben@dentcare.com" value="<?= $keep ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                    </div>

                    <div class="field">
                        <label for="phone">Numri i Telefonit (opsionale)</label>
                        <input type="text" id="phone" name="phone" placeholder="p.sh. 044 123 456" value="<?= $keep ? htmlspecialchars($_POST['phone'] ?? '') : '' ?>">
                    </div>

                    <div class="field">
                        <label>Roli</label>
                        <div class="custom-select-wrapper" id="roleSelectWrapper">
                            <input type="hidden" name="role" id="roleHiddenInput" value="<?= $keep ? htmlspecialchars($_POST['role'] ?? 'staff') : 'staff' ?>">
                            <div class="custom-select-trigger" onclick="toggleRoleDropdown()">
                                <span id="roleSelectedLabel">Staff</span>
                                <svg class="custom-select-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-option" data-value="staff" onclick="selectRole(this)">
                                    <span class="role-dot" style="background:#6c757d;"></span> Staff
                                </div>
                                <div class="custom-select-option" data-value="dentist" onclick="selectRole(this)">
                                    <span class="role-dot" style="background:var(--green);"></span> Dentist
                                </div>
                                <div class="custom-select-option" data-value="receptionist" onclick="selectRole(this)">
                                    <span class="role-dot" style="background:var(--yellow);"></span> Receptionist
                                </div>
                                <div class="custom-select-option" data-value="manager" onclick="selectRole(this)">
                                    <span class="role-dot" style="background:#5b4fcf;"></span> Manager
                                </div>
                                <div class="custom-select-option" data-value="admin" onclick="selectRole(this)">
                                    <span class="role-dot" style="background:#c0392b;"></span> Admin
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="arben.k" required autocomplete="off" value="<?= $keep ? htmlspecialchars($_POST['username'] ?? '') : '' ?>">
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
                    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                    <table id="workersTable" style="min-width:640px;">
                        <thead>
                            <tr>
                                <th>Punëtori</th>
                                <th>Username</th>
                                <th>Roli</th>
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
                                <td>
                                    <?php
                                        $roleLabels = ['staff'=>'Staff','dentist'=>'Dentist','receptionist'=>'Receptionist','manager'=>'Manager'];
                                        $roleColors = ['staff'=>'#6c757d','dentist'=>'var(--green-dark)','receptionist'=>'var(--yellow)','manager'=>'#5b4fcf'];
                                        $roleBg    = ['staff'=>'#f0f0f0','dentist'=>'var(--green-light)','receptionist'=>'var(--yellow-light)','manager'=>'#eeebfb'];
                                        $r = $w['role'] ?? 'staff';
                                        $rl = $roleLabels[$r] ?? ucfirst($r);
                                        $rc = $roleColors[$r] ?? '#6c757d';
                                        $rb = $roleBg[$r] ?? '#f0f0f0';
                                    ?>
                                    <span style="background:<?= $rb ?>; color:<?= $rc ?>; padding:3px 10px; border-radius:50px; font-size:12px; font-weight:500;"><?= htmlspecialchars($rl) ?></span>
                                </td>
                                <td><?= htmlspecialchars($w['phone'] ?? '—') ?></td>
                                <td>
                                    <?php if ($w['status'] === 'Active'): ?>
                                        <span class="badge badge-confirmed">✓ Aktiv</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelled">✕ Joaktiv</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($w['created_at']) ? date('M d, Y', strtotime($w['created_at'])) : '—' ?></td>
                                <td>
                                    <div style="display:flex; gap:6px; align-items:center;">
                                        <button type="button"
                                            style="padding:6px 12px; border-radius:var(--radius-sm); font-size:13px; font-weight:500; cursor:pointer; border:1px solid var(--border); background:var(--cream); color:var(--text-mid); transition:background .2s;"
                                            onmouseover="this.style.background='var(--green-light)';this.style.color='var(--green-dark)';"
                                            onmouseout="this.style.background='var(--cream)';this.style.color='var(--text-mid)';"
                                            onclick="openEditModal(
                                                <?= $w['id'] ?>,
                                                '<?= htmlspecialchars(addslashes($w['full_name'] ?? '')) ?>',
                                                '<?= htmlspecialchars(addslashes($w['email'] ?? '')) ?>',
                                                '<?= htmlspecialchars(addslashes($w['phone'] ?? '')) ?>',
                                                '<?= htmlspecialchars(addslashes($w['username'])) ?>',
                                                '<?= htmlspecialchars($w['role'] ?? 'staff') ?>'
                                            )">Ndrysho</button>
                                        <form id="delete-worker-form-<?= $w['id'] ?>" method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= $w['id'] ?>">
                                            <?php if ($w['status'] === 'Active'): ?>

                                            <input type="hidden" name="action" value="delete_worker">

                                            <button
                                                type="button"
                                                class="btn-delete-worker"
                                                onclick="triggerDeleteConfirm(<?= $w['id'] ?>, '<?= htmlspecialchars(addslashes($w['full_name'] ?: $w['username'])) ?>')">
                                                Çaktivizo
                                            </button>

                                        <?php else: ?>

                                            <input type="hidden" name="action" value="activate_worker">

                                            <button
                                                type="submit"
                                                class="btn-activate-worker">
                                                Aktivizo
                                            </button>

                                        <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr id="noWorkerResults" style="display:none;">
                                <td colspan="7" style="text-align:center; padding:40px 20px;">
                                    <div style="font-size:32px; margin-bottom:8px;">🔍</div>
                                    <p style="color:var(--text-soft); font-size:14px;">Nuk u gjet asnjë punëtor.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- ── FULLSCREEN RESULT OVERLAY ── -->
    <div id="resultOverlay" class="result-overlay">
        <div class="result-box" id="resultBox">
            <div class="result-icon" id="resultIcon"></div>
            <h2 id="resultTitle"></h2>
            <p id="resultMessage"></p>
            <button onclick="closeResultOverlay()">Vazhdo</button>
        </div>
    </div>

    <!-- ── DELETE CONFIRM MODAL ── -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-box">
            <div class="icon">🗑️</div>
            <h3>Çaktivizo Punëtorin?</h3>
            <p id="confirmMessage">A jeni i sigurt që dëshironi të Çaktivizoni këtë punëtor?</p>
            <div class="modal-actions">
                <button type="button" class="btn-no" onclick="closeConfirmModal()">Jo</button>
                <button type="button" class="btn-yes" id="confirmDeleteBtn">Po, Çaktivizo</button>
            </div>
        </div>
    </div>

    <!-- ── EDIT WORKER MODAL ── -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box" style="max-width:480px; text-align:left; padding:28px 32px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border);">
                <h3 style="font-family:'DM Serif Display',serif; font-size:20px;">Ndrysho Punëtorin</h3>
                <button onclick="closeEditModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-soft); line-height:1;">&times;</button>
            </div>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="action" value="edit_worker">
                <input type="hidden" name="user_id" id="edit_user_id">

                <div class="field" style="margin-bottom:14px;">
                    <label for="edit_full_name">Emri i Plotë</label>
                    <input type="text" id="edit_full_name" name="full_name" placeholder="Dr. Arben Krasniqi">
                </div>
                <div class="field" style="margin-bottom:14px;">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" placeholder="arben@dentcare.com">
                </div>
                <div class="field" style="margin-bottom:14px;">
                    <label for="edit_phone">Numri i Telefonit</label>
                    <input type="text" id="edit_phone" name="phone" placeholder="044 123 456">
                </div>
                <div class="field" style="margin-bottom:14px;">
                    <label>Roli</label>
                    <div class="custom-select-wrapper" id="editRoleSelectWrapper">
                        <input type="hidden" name="role" id="editRoleHiddenInput" value="staff">
                        <div class="custom-select-trigger" onclick="toggleEditRoleDropdown()">
                            <span id="editRoleSelectedLabel">Staff</span>
                            <svg class="custom-select-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-option" data-value="staff" onclick="selectEditRole(this)">
                                <span class="role-dot" style="background:#6c757d;"></span> Staff
                            </div>
                            <div class="custom-select-option" data-value="dentist" onclick="selectEditRole(this)">
                                <span class="role-dot" style="background:var(--green);"></span> Dentist
                            </div>
                            <div class="custom-select-option" data-value="receptionist" onclick="selectEditRole(this)">
                                <span class="role-dot" style="background:var(--yellow);"></span> Receptionist
                            </div>
                            <div class="custom-select-option" data-value="manager" onclick="selectEditRole(this)">
                                <span class="role-dot" style="background:#5b4fcf;"></span> Manager
                            </div>
                            <div class="custom-select-option" data-value="admin" onclick="selectEditRole(this)">
                                <span class="role-dot" style="background:#c0392b;"></span> Admin
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field" style="margin-bottom:14px;">
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="username" required>
                </div>
                <div class="field" style="margin-bottom:20px;">
                    <label for="edit_password">Password i Ri (lër bosh për të mos ndryshuar)</label>
                    <input type="password" id="edit_password" name="password" placeholder="Min. 6 karaktere">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeEditModal()" style="padding:10px 20px; border-radius:8px; border:1px solid var(--border); background:var(--cream); color:var(--text-mid); cursor:pointer; font-size:14px; font-weight:500;">Anulo</button>
                    <button type="submit" style="padding:10px 20px; border-radius:8px; border:none; background:var(--green); color:white; cursor:pointer; font-size:14px; font-weight:500;">Ruaj Ndryshimet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ── PHP STATE PASSED TO JS ──
        const PHP_ERROR   = <?= json_encode($error) ?>;
        const PHP_SUCCESS = <?= json_encode($success) ?>;

        // ── RESULT OVERLAY ──
        function showResult(type, title, message) {
            const overlay = document.getElementById('resultOverlay');
            const box     = document.getElementById('resultBox');
            const icon    = document.getElementById('resultIcon');
            document.getElementById('resultTitle').innerText   = title;
            document.getElementById('resultMessage').innerText = message;

            if (type === 'success') {
                icon.innerHTML  = '✓';
                icon.className  = 'result-icon success';
                icon.style.cssText = 'color:var(--green); font-size:34px; font-weight:700;';
                box.classList.remove('is-error');
            } else {
                icon.innerHTML  = '✕';
                icon.className  = 'result-icon error';
                icon.style.cssText = 'color:var(--red); font-size:34px; font-weight:700;';
                box.classList.add('is-error');
            }
            overlay.classList.add('active');
        }

        function closeResultOverlay() {
            document.getElementById('resultOverlay').classList.remove('active');
        }

        // ── DELETE CONFIRM ──
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

        // ── EDIT MODAL ──
        function openEditModal(id, fullName, email, phone, username, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_password').value = '';
            const editRole = role || 'staff';
            document.getElementById('editRoleHiddenInput').value = editRole;
            document.getElementById('editRoleSelectedLabel').textContent = roleMeta[editRole]?.label || editRole;
            document.querySelectorAll('#editRoleSelectWrapper .custom-select-option').forEach(o => o.classList.remove('selected'));
            const editMatch = document.querySelector(`#editRoleSelectWrapper [data-value="${editRole}"]`);
            if (editMatch) editMatch.classList.add('selected');
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        // ── WORKER SEARCH ──
        function filterWorkers() {
            const filter = document.getElementById('workerSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#workersTable tbody tr:not(#noWorkerResults)');
            const noResults = document.getElementById('noWorkerResults');
            let visible = 0;

            rows.forEach(row => {
                const val = row.getAttribute('data-search') || '';
                if (val.includes(filter)) { row.style.display = ''; visible++; }
                else { row.style.display = 'none'; }
            });

            if (noResults) noResults.style.display = visible === 0 ? '' : 'none';
        }

        // ── CLOSE OVERLAYS ON BACKDROP CLICK ──
        window.onclick = function(e) {
            if (e.target === document.getElementById('confirmModal')) closeConfirmModal();
            if (e.target === document.getElementById('editModal'))    closeEditModal();
            if (e.target === document.getElementById('resultOverlay')) closeResultOverlay();
        };

        // ── TRIGGER RESULT OVERLAY FROM PHP STATE ──
        document.addEventListener('DOMContentLoaded', function() {
            if (PHP_ERROR && PHP_ERROR.length > 0) {
                setTimeout(() => showResult('error', 'Gabim!', PHP_ERROR), 80);
            }
            if (PHP_SUCCESS && PHP_SUCCESS.length > 0) {

                const isRegister   = PHP_SUCCESS.includes('u regjistrua');
                const isDeactivate = PHP_SUCCESS.includes('çaktivizua');
                const isActivate   = PHP_SUCCESS.includes('aktivizua');
                const isEdit       = PHP_SUCCESS.includes('përditësua');

                let title = 'Sukses!';

                if (isRegister) {
                    title = 'U regjistrua me sukses!';
                } else if (isDeactivate || isActivate) {
                    title = 'Statusi u përditësua!';
                } else if (isEdit) {
                    title = 'Ndryshimet u ruajtën!';
                }

                setTimeout(() => showResult('success', title, PHP_SUCCESS), 80);
            }
        });
// ── CUSTOM ROLE DROPDOWN ──
const roleMeta = {
    staff:        { label: 'Staff',        dot: '#6c757d' },
    dentist:      { label: 'Dentist',      dot: 'var(--green)' },
    receptionist: { label: 'Receptionist', dot: 'var(--yellow)' },
    manager:      { label: 'Manager',      dot: '#5b4fcf' },
    admin:        { label: 'Admin',        dot: '#c0392b' },
};

function toggleRoleDropdown() {
    document.getElementById('roleSelectWrapper').classList.toggle('open');
}

function selectRole(el) {
    const value = el.getAttribute('data-value');
    document.getElementById('roleHiddenInput').value = value;
    document.getElementById('roleSelectedLabel').textContent = roleMeta[value]?.label || value;
    document.querySelectorAll('#roleSelectWrapper .custom-select-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('roleSelectWrapper').classList.remove('open');
}
function toggleEditRoleDropdown() {
    document.getElementById('editRoleSelectWrapper').classList.toggle('open');
}

function selectEditRole(el) {
    const value = el.getAttribute('data-value');
    document.getElementById('editRoleHiddenInput').value = value;
    document.getElementById('editRoleSelectedLabel').textContent = roleMeta[value]?.label || value;
    document.querySelectorAll('#editRoleSelectWrapper .custom-select-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('editRoleSelectWrapper').classList.remove('open');
}
// Set initial selected state from hidden input value on page load
document.addEventListener('DOMContentLoaded', function() {
    const initial = document.getElementById('roleHiddenInput')?.value || 'staff';
    document.getElementById('roleSelectedLabel').textContent = roleMeta[initial]?.label || initial;
    const match = document.querySelector(`#roleSelectWrapper [data-value="${initial}"]`);
    if (match) match.classList.add('selected');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('roleSelectWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        wrapper.classList.remove('open');
    }
    const editWrapper = document.getElementById('editRoleSelectWrapper');
    if (editWrapper && !editWrapper.contains(e.target)) {
        editWrapper.classList.remove('open');
    }
});
    </script>
</body>
</html>