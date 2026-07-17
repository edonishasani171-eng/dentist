<?php
// admin/messages.php
session_start();

$staff_name = $_SESSION['user_username'] ?? $_SESSION['username'] ?? $_SESSION['staff_name'] ?? 'Admin Staf';

// Security check
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

$current_page = 'messages';

require_once 'db.php';

// Make sure the table exists (safe to run every time)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS messages (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'New',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

// ── HANDLE ACTION: DELETE MESSAGE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    $target_id = (int)$_POST['message_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: messages.php");
    exit;
}

// ── HANDLE ACTION: ACCEPT (PRANO) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'accept_message') {
    $target_id = (int)$_POST['message_id'];
    try {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'Prano' WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: messages.php");
    exit;
}

// ── HANDLE ACTION: CANCEL (ANULO) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_message') {
    $target_id = (int)$_POST['message_id'];
    try {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'Anulo' WHERE id = :id");
        $stmt->execute(['id' => $target_id]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: messages.php");
    exit;
}

// ── FETCH MESSAGES ──
try {
    $msg_stmt = $pdo->query("SELECT id, name, phone, email, subject, message, status, created_at FROM messages ORDER BY id DESC");
    $messages = $msg_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $messages = [];
}

$unread_messages_count = 0;
foreach ($messages as $m) {
    if ($m['status'] === 'New') $unread_messages_count++;
}
$new_messages_count = $unread_messages_count; // used by sidebar.php badge
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Mesazhet e Klientëve</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
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
            --orange:       #df773c;
            --orange-light: #fdf5f2;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { background: var(--cream); scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            display: flex;
            height: 100vh;
            overflow: hidden;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.4s ease-out, transform 0.4s ease-out;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        body.page-loaded { opacity: 1; transform: translateY(0); }

        main {
            flex: 1;
            padding: 40px 48px;
            z-index: 1;
            overflow-y: auto;
            height: 100vh;
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
            width: 28px; height: 28px;
            background: var(--green-mid);
            color: var(--green-dark);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(26,122,94,0.15); }

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

        .table-container {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            max-height: 620px;
            overflow-y: auto;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }

        th {
            background: #faf9f6;
            padding: 16px 24px;
            color: var(--text-mid);
            font-weight: 500;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        td { padding: 18px 24px; border-bottom: 1px solid var(--border); color: var(--text); }
        tr:last-child td { border-bottom: none; }

        .patient-name { font-weight: 500; color: var(--text); }
        .patient-phone { font-size: 13px; color: var(--text-soft); margin-top: 2px; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-unread { background: var(--green-light); color: var(--green-dark); }
        .badge-read   { background: var(--cream-dark); color: var(--text-soft); }
        .badge-approved  { background: var(--green-light); color: var(--green-dark); }
        .badge-cancelled { background: var(--orange-light); color: #df473c; }

        .msg-subject { font-weight: 500; color: var(--text); }
        .msg-preview {
            font-size: 13px;
            color: var(--text-soft);
            margin-top: 2px;
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions { display: flex; align-items: center; gap: 8px; }

        .btn {
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-approve { background: var(--green); color: var(--white); }
        .btn-approve:hover { background: var(--green-dark); }
        .btn-delete { background: #df473c; color: var(--white); }
        .btn-delete:hover { background: var(--red); }
        .btn-cancel { background: var(--orange); color: var(--white); }
        .btn-cancel:hover { background: #c6632f; }
        .btn-secondary { background: var(--cream); color: var(--text-mid); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--cream-dark); }

        /* ── MODAL ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            z-index: 99999;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }

        .modal-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            width: 90%; max-width: 520px;
            box-shadow: 0 20px 50px rgba(26, 26, 24, 0.18);
            transform: scale(0.95) translateY(10px);
            transition: transform 0.25s ease;
            padding: 32px;
        }
        .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

        .modal-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .modal-header h3 { font-family: 'DM Serif Display', serif; font-size: 22px; color: var(--text); letter-spacing: -0.01em; }
        .modal-close { background: none; border: none; font-size: 26px; color: var(--text-soft); cursor: pointer; line-height: 1; padding: 0 4px; }
        .modal-close:hover { color: var(--text); }

        .modal-body { display: flex; flex-direction: column; gap: 18px; }
        .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-group label {
            font-size: 10px; font-weight: 600; color: var(--text-soft);
            text-transform: uppercase; letter-spacing: 0.06em;
            display: block; margin-bottom: 4px;
        }
        .info-group p { font-size: 14px; color: var(--text); font-weight: 500; }

        .message-full-box {
            background: var(--cream);
            padding: 14px 16px;
            border-radius: var(--radius-sm);
            min-height: 90px;
            font-size: 14px;
            font-weight: 400 !important;
            color: var(--text-mid) !important;
            border: 1px solid var(--border);
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .modal-footer {
            margin-top: 14px; padding-top: 16px;
            border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: 8px;
        }

        .search-box-wrapper { position: relative; display: flex; align-items: center; }
        #msgSearch {
            width: 280px;
            padding: 10px 16px 10px 36px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background-color: var(--white);
            color: var(--text);
            outline: none;
            transition: all 0.2s ease;
        }
        #msgSearch:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(26, 122, 94, 0.15); }
        .search-icon { position: absolute; left: 12px; pointer-events: none; }
        .search-icon svg { width: 14px; height: 14px; stroke: var(--text-soft); fill: none; stroke-width: 2.5; }

        @media (max-width: 768px) {
            main { padding: 20px 16px; }
            header { flex-direction: column; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
            .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table { min-width: 600px; }
            .modal-box { padding: 20px 16px; width: 95%; }
            .info-row { grid-template-columns: 1fr; }
            #msgSearch { width: 100%; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main>
        <header>
            <div>
                <h1>Mesazhet e Klientëve</h1>
            </div>
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr(htmlspecialchars($staff_name), 0, 1)) ?></div>
                <span><?= htmlspecialchars($staff_name) ?></span>
            </div>
        </header>

        <div style="display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
            <div class="card" style="min-width:140px;">
                <div class="card-meta">Të Palexuara</div>
                <div class="card-value" style="color: var(--green);"><?= $unread_messages_count ?></div>
            </div>
            <div class="card" style="min-width:140px;">
                <div class="card-meta">Totali i Mesazheve</div>
                <div class="card-value"><?= count($messages) ?></div>
            </div>
        </div>

        <div style="margin-bottom:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div class="search-box-wrapper">
                <span class="search-icon">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <input type="text" id="msgSearch" placeholder="Kerko me emer, email ose subjekt" onkeyup="filterMsgTable()">
            </div>
        </div>

        <div class="table-container">
            <table id="msgTable">
                <thead>
                    <tr>
                        <th>Klienti</th>
                        <th>Subjekti / Mesazhi</th>
                        <th>Data</th>
                        <th>Statusi</th>
                        <th>Veprime</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr data-search="<?= strtolower(htmlspecialchars($msg['name'] . ' ' . $msg['email'] . ' ' . $msg['subject'])) ?>">
                        <td>
                            <div class="patient-name"><?= htmlspecialchars($msg['name']) ?></div>
                            <div style="font-size: 12px; color: var(--text-soft);"><?= htmlspecialchars($msg['email']) ?></div>
                            <?php if (!empty($msg['phone'])): ?>
                                <div class="patient-phone"><?= htmlspecialchars($msg['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="msg-subject"><?= htmlspecialchars($msg['subject']) ?></div>
                            <div class="msg-preview"><?= htmlspecialchars(substr($msg['message'], 0, 60) . (strlen($msg['message']) > 60 ? '...' : '')) ?></div>
                        </td>
                        <td><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></td>
                        <td>
                            <?php if ($msg['status'] === 'New'): ?>
                                <span class="badge badge-unread">● E Re</span>
                            <?php elseif ($msg['status'] === 'Read'): ?>
                                <span class="badge badge-read">✓ Lexuar</span>
                            <?php elseif ($msg['status'] === 'Prano'): ?>
                                <span class="badge badge-approved">✓ Prano</span>
                            <?php elseif ($msg['status'] === 'Anulo'): ?>
                                <span class="badge badge-cancelled">✕ Anulo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary" onclick="showMessageDetails(<?= htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8') ?>)">Shiko</button>

                                <form id="acceptmsg-form-<?= $msg['id'] ?>" method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <input type="hidden" name="action" value="accept_message">
                                    <button type="submit" class="btn btn-approve">Prano</button>
                                </form>

                                <form id="cancelmsg-form-<?= $msg['id'] ?>" method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <input type="hidden" name="action" value="cancel_message">
                                    <button type="submit" class="btn btn-cancel">Anulo</button>
                                </form>

                                <form id="delmsg-form-<?= $msg['id'] ?>" method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <input type="hidden" name="action" value="delete_message">
                                    <button type="button" class="btn btn-delete" onclick="triggerDeleteConfirm(<?= $msg['id'] ?>)">Fshije</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="noMsgResults" style="display:<?= count($messages) === 0 ? '' : 'none' ?>;">
                        <td colspan="5" style="text-align:center; padding:50px 20px;">
                            <div style="font-size:40px; margin-bottom:12px;">✉️</div>
                            <h4 style="font-size:16px; color:var(--text-mid); margin:0 0 6px 0;">Nuk ka mesazhe ende.</h4>
                            <p style="font-size:14px; color:var(--text-soft); margin:0;">Mesazhet e reja nga faqja e kontaktit do të shfaqen këtu.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MESSAGE DETAILS MODAL -->
    <div id="messageModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Mesazhi i Klientit</h3>
                <button class="modal-close" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="info-row">
                    <div class="info-group"><label>Emri</label><p id="msg-modal-name"></p></div>
                    <div class="info-group"><label>Email</label><p id="msg-modal-email"></p></div>
                </div>
                <div class="info-row">
                    <div class="info-group"><label>Telefoni</label><p id="msg-modal-phone"></p></div>
                    <div class="info-group"><label>Data</label><p id="msg-modal-date"></p></div>
                </div>
                <div class="info-group"><label>Subjekti</label><p id="msg-modal-subject"></p></div>
                <div class="info-group">
                    <label>Mesazhi</label>
                    <p id="msg-modal-message" class="message-full-box"></p>
                </div>

                <div class="modal-footer">

                    <form id="msg-modal-accept-form" method="POST" action="">
                        <input type="hidden" name="message_id" id="msg-modal-accept-id" value="">
                        <input type="hidden" name="action" value="accept_message">
                        <button type="submit" class="btn btn-approve">Prano</button>
                    </form>

                    <form id="msg-modal-cancel-form" method="POST" action="">
                        <input type="hidden" name="message_id" id="msg-modal-cancel-id" value="">
                        <input type="hidden" name="action" value="cancel_message">
                        <button type="submit" class="btn btn-cancel">Anulo</button>
                    </form>

                    <form id="msg-modal-delete-form" method="POST" action="">
                        <input type="hidden" name="message_id" id="msg-modal-delete-id" value="">
                        <input type="hidden" name="action" value="delete_message">
                        <button type="button" class="btn btn-delete" onclick="triggerModalDeleteConfirm()">Fshije</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- CONFIRM MODAL -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-box">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 40px; margin-bottom: 10px;">🗑️</div>
                <h3 style="font-size: 22px; margin-bottom: 10px; color: #333;">Fshije Mesazhin?</h3>
                <p style="color: #666; font-size: 15px; line-height: 1.5; margin: 0 10px;">
                    A jeni të sigurt që dëshironi të fshini përgjithmonë këtë mesazh? Kjo procedurë nuk mund të kthehet.
                </p>
            </div>
            <div style="display: flex; justify-content: center; gap: 12px; margin-top: 25px;">
                <button type="button" class="btn" style="background-color: #6c757d; color: white; padding: 10px 20px; border-radius: 8px;" onclick="closeConfirmModal()">Jo</button>
                <button type="button" id="confirmSubmitBtn" class="btn" style="background-color:#df473c; color: white; padding: 10px 20px; border-radius: 8px;">Po, Fshije</button>
            </div>
        </div>
    </div>

<script>
    // ── SEARCH FILTER ──
    function filterMsgTable() {
        const filter = (document.getElementById('msgSearch')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#msgTable tbody tr:not(#noMsgResults)');
        const noResults = document.getElementById('noMsgResults');
        let visible = 0;

        rows.forEach(row => {
            const searchVal = row.getAttribute('data-search') || '';
            const match = searchVal.includes(filter);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (noResults) noResults.style.display = visible === 0 ? '' : 'none';
    }

    // ── MESSAGE DETAILS MODAL ──
    function showMessageDetails(msgData) {
        document.getElementById('msg-modal-name').textContent = msgData.name;
        document.getElementById('msg-modal-email').textContent = msgData.email;
        document.getElementById('msg-modal-phone').textContent = msgData.phone ? msgData.phone : '—';
        document.getElementById('msg-modal-date').textContent = msgData.created_at;
        document.getElementById('msg-modal-subject').textContent = msgData.subject;
        document.getElementById('msg-modal-message').textContent = msgData.message;

        document.getElementById('msg-modal-accept-id').value = msgData.id;
        document.getElementById('msg-modal-cancel-id').value = msgData.id;
        document.getElementById('msg-modal-delete-id').value = msgData.id;

        document.getElementById('messageModal').classList.add('active');
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.remove('active');
    }

    // ── DELETE CONFIRM MODAL ──
    let targetFormToSubmit = null;

    function triggerDeleteConfirm(msgId) {
        targetFormToSubmit = document.getElementById(`delmsg-form-${msgId}`);
        document.getElementById('confirmModal').classList.add('active');
    }

    function triggerModalDeleteConfirm() {
        targetFormToSubmit = document.getElementById('msg-modal-delete-form');
        document.getElementById('confirmModal').classList.add('active');
    }

    document.getElementById('confirmSubmitBtn').onclick = function () {
        if (targetFormToSubmit) targetFormToSubmit.submit();
    };

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
        targetFormToSubmit = null;
    }

    window.onclick = function (event) {
        if (event.target === document.getElementById('messageModal')) closeMessageModal();
        if (event.target === document.getElementById('confirmModal')) closeConfirmModal();
    };

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => document.body.classList.add('page-loaded'), 50);

        const menuToggle = document.getElementById('menu-toggle');
        const navLinks = document.getElementById('nav-links');
        if (!menuToggle || !navLinks) return;

        menuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
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