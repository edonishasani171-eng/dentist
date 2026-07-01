<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$staff_name = $_SESSION['username'] ?? $_SESSION['staff_name'] ?? 'Admin Staf';
$today = date('Y-m-d');

// ── STAFF CHECK IN ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'staff_checkin') {
    $user_id = (int)$_POST['user_id'];
    try {
        $check = $pdo->prepare("SELECT id FROM staff_attendance WHERE user_id = :uid AND work_date = :d AND check_out_time IS NULL");
        $check->execute(['uid' => $user_id, 'd' => $today]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("INSERT INTO staff_attendance (user_id, check_in_time, work_date) VALUES (:uid, NOW(), :d)");
            $ins->execute(['uid' => $user_id, 'd' => $today]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: check_in.php");
    exit;
}

// ── STAFF CHECK OUT ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'staff_checkout') {
    $user_id = (int)$_POST['user_id'];
    try {
        $upd = $pdo->prepare("UPDATE staff_attendance SET check_out_time = NOW() WHERE user_id = :uid AND work_date = :d AND check_out_time IS NULL");
        $upd->execute(['uid' => $user_id, 'd' => $today]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    header("Location: check_in.php");
    exit;
}

// ── FETCH DATA ──
try {
    $staffStmt = $pdo->prepare(
        "SELECT * FROM (
            SELECT DISTINCT ON (u.id)
                u.id, COALESCE(u.full_name, u.username) AS display_name, u.role,
                sa.check_in_time, sa.check_out_time
            FROM users u
            LEFT JOIN staff_attendance sa ON sa.user_id = u.id AND sa.work_date = :d
            WHERE u.status = 'Active'
            ORDER BY u.id, sa.check_in_time DESC NULLS LAST
        ) latest
        ORDER BY display_name ASC"
    );
    $staffStmt->execute(['d' => $today]);
    $staff = $staffStmt->fetchAll(PDO::FETCH_ASSOC);

    $present_count = 0;
    foreach ($staff as $s) {
        if (!empty($s['check_in_time']) && empty($s['check_out_time'])) $present_count++;
    }

    $history_date = $_GET['history_date'] ?? '';

if ($history_date !== '') {
    $historyStmt = $pdo->prepare(
        "SELECT COALESCE(u.full_name, u.username) AS display_name, u.role, sa.check_in_time, sa.check_out_time, sa.work_date
         FROM staff_attendance sa
         JOIN users u ON u.id = sa.user_id
         WHERE sa.work_date = :hd
         ORDER BY sa.check_in_time DESC"
    );
    $historyStmt->execute(['hd' => $history_date]);
} else {
    $historyStmt = $pdo->prepare(
        "SELECT COALESCE(u.full_name, u.username) AS display_name, u.role, sa.check_in_time, sa.check_out_time, sa.work_date
         FROM staff_attendance sa
         JOIN users u ON u.id = sa.user_id
         ORDER BY sa.check_in_time DESC
         LIMIT 50"
    );
    $historyStmt->execute();
}
$attendance_history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Dështoi leximi i të dhënave: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Check In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #1a7a5e;
            --green-dark: #0f5441;
            --green-light: #e8f5f1;
            --green-mid: #c2e8dc;
            --cream: #faf8f4;
            --cream-dark: #f0ece3;
            --text: #1a1a18;
            --text-mid: #4a4a45;
            --text-soft: #8a8a82;
            --white: #ffffff;
            --border: rgba(26,26,24,0.10);
            --red: #c0392b;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow: 0 2px 24px rgba(26,122,94,0.08);
            --yellow: #ba7517;
            --yellow-light: #fdf6ed;
            --orange: #df773c;
            --orange-light: #fdf5f2;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

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
        }

        .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; margin-bottom: 40px; padding-left: 8px; }
        .brand-icon { width: 32px; height: 32px; background: var(--green); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .brand-icon svg { width: 18px; height: 18px; fill: white; }
        .brand-text { font-family: 'DM Serif Display', serif; font-size: 18px; color: var(--text); }
        .brand-text span { color: var(--green); }

        .menu { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .menu-item a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px; color: var(--text-mid); text-decoration: none;
            font-size: 14px; font-weight: 500; border-radius: var(--radius-sm);
            transition: all 0.2s;
        }
        .menu-item.active a, .menu-item a:hover { background: var(--green-light); color: var(--green-dark); }
        .menu-item a svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; }

        .logout-btn { margin-top: auto; border-top: 1px solid var(--border); padding-top: 24px; }

        main { flex: 1; padding: 40px 48px; overflow-y: auto; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        header h1 { font-family: 'DM Serif Display', serif; font-size: 28px; }
        .header-date { font-size: 14px; color: var(--text-soft); margin-top: 4px; }

        .user-profile {
            display: flex; align-items: center; gap: 12px; background: var(--white);
            padding: 8px 16px; border-radius: 50px; border: 1px solid var(--border);
            font-size: 14px; font-weight: 500;
        }
        .avatar {
            width: 28px; height: 28px; background: var(--green-mid); color: var(--green-dark);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px;
        }

        .metrics-grid { display: grid; grid-template-columns: minmax(220px, 300px); gap: 20px; margin-bottom: 40px; }
        .card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow); }
        .card-meta { font-size: 12px; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 500; margin-bottom: 8px; }
        .card-value { font-family: 'DM Serif Display', serif; font-size: 32px; }

        .panels { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
        .panel { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow); overflow: hidden; }
        .panel-header { padding: 20px 24px; border-bottom: 1px solid var(--border); }
        .panel-header h2 { font-family: 'DM Serif Display', serif; font-size: 18px; }

        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { background: #faf9f6; padding: 14px 24px; color: var(--text-mid); font-weight: 500; border-bottom: 1px solid var(--border); text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        td { padding: 16px 24px; border-bottom: 1px solid var(--border); }
        tr:last-child td { border-bottom: none; }

        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 500; }
        .badge-pending { background: var(--yellow-light); color: var(--yellow); }
        .badge-confirmed { background: var(--green-light); color: var(--green-dark); }
        .badge-cancelled { background: var(--orange-light); color: var(--orange); }
        .badge-out { background: var(--cream-dark); color: var(--text-soft); }

        .btn { padding: 6px 14px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500; cursor: pointer; border: none; transition: background 0.2s; }
        .btn-approve { background: var(--green); color: var(--white); }
        .btn-approve:hover { background: var(--green-dark); }
        .btn-cancel { background: var(--orange); color: var(--white); }
        .btn-cancel:hover { background: #c6632f; }
        .btn-secondary { background: var(--cream); color: var(--text-mid); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--cream-dark); }

        .empty-state { text-align: center; padding: 50px 20px; }
        .empty-state div { font-size: 36px; margin-bottom: 10px; }
        .empty-state p { color: var(--text-soft); font-size: 14px; }

        @media (max-width: 1100px) {
            .metrics-grid { grid-template-columns: repeat(1, 1fr); }
            .panels { grid-template-columns: 1fr; }
        }
        .history-filters {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
        }

        .history-search-wrapper {
            position: relative;
            flex: 1;
        }

        .history-search-wrapper input {
            width: 100%;
            padding: 9px 12px 9px 34px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            background: var(--white);
            color: var(--text);
        }

        .history-search-wrapper input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(26, 122, 94, 0.15);
        }

        .history-search-wrapper .search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
        }

        .history-date-input {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            background: var(--white);
            color: var(--text);
            outline: none;
        }

        .history-date-input:focus {
            border-color: var(--green);
        }

        .history-date-clear {
            font-size: 12px;
            color: var(--text-soft);
            text-decoration: none;
            white-space: nowrap;
        }

        .history-date-clear:hover {
            color: var(--red);
        }

        .table-scroll {
            max-height: 480px;
            overflow-y: auto;
        }

        .table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>
</head>
<body>
    <aside>
        <a href="admin_dashboard.php?page=dashboard" class="brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/></svg>
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
            <li class="menu-item">
                <a href="register_worker.php">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Regjistro Punëtor
                </a>
            </li>
            <li class="menu-item active">
                <a href="check_in.php">
                    <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Check In
                </a>
            </li>
        </ul>

        <div class="menu-item logout-btn">
            <a href="logout.php" style="color: #c0392b; display:flex; align-items:center; gap:12px; padding:12px 14px; text-decoration:none; font-size:14px; font-weight:500;">
                <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#c0392b;fill:none;stroke-width:2;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Log Out
            </a>
        </div>
    </aside>

    <main>
        <header>
            <div>
                <h1>Check In i Ditës</h1>
                <div class="header-date"><?= date('l, d F Y') ?></div>
            </div>
            <div class="user-profile">
                <div class="avatar"><?= strtoupper(substr(htmlspecialchars($staff_name), 0, 1)) ?></div>
                <span><?= htmlspecialchars($staff_name) ?></span>
            </div>
        </header>

        <div class="metrics-grid">
            <div class="card">
                <div class="card-meta">Stafi Prezent</div>
                <div class="card-value" style="color: var(--green);"><?= $present_count ?> / <?= count($staff) ?></div>
            </div>
        </div>

        <div class="panels">
            <!-- STAFF PANEL -->
            <div class="panel">
                <div class="panel-header"><h2>Stafi</h2></div>

                <div class="history-filters">
                    <div class="history-search-wrapper">
                        <span class="search-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-soft)" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="text" id="staffSearch" placeholder="Kerko me emer..." onkeyup="filterStaffTable()">
                    </div>
                </div>

                <table id="staffTable">
                    <thead>
                        <tr>
                            <th>Emri</th>
                            <th>Statusi</th>
                            <th>Veprim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staff)): ?>
                            <tr><td colspan="3" class="empty-state"><div>👥</div><p>Nuk ka staf aktiv.</p></td></tr>
                        <?php else: foreach ($staff as $s): ?>
                            <tr data-search="<?= strtolower(htmlspecialchars($s['display_name'] . ' ' . $s['role'])) ?>">
                                <td>
                                    <strong><?= htmlspecialchars($s['display_name']) ?></strong>
                                    <div style="font-size:12px;color:var(--text-soft);"><?= htmlspecialchars($s['role']) ?></div>
                                </td>
                                    <?php if (!empty($s['check_in_time']) && empty($s['check_out_time'])): ?>
                                        <span class="badge badge-confirmed">✓ Në punë · <?= date('H:i', strtotime($s['check_in_time'])) ?></span>
                                    <?php elseif (!empty($s['check_out_time'])): ?>
                                        <span class="badge badge-out">Doli · <?= date('H:i', strtotime($s['check_out_time'])) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Nuk ka ardhur</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($s['check_in_time']) || !empty($s['check_out_time'])): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="action" value="staff_checkin">
                                            <button type="submit" class="btn btn-approve">Check In</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="action" value="staff_checkout">
                                            <button type="submit" class="btn btn-cancel">Check Out</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            <tr id="noStaffResults" style="display:none;">
                                <td colspan="3" class="empty-state"><div>🔍</div><p>Nuk u gjet asnjë rezultat.</p></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ATTENDANCE HISTORY PANEL -->
            <div class="panel">
                <div class="panel-header"><h2>Historiku i Check-Ineve</h2></div>

                <div class="history-filters">
                    <div class="history-search-wrapper">
                        <span class="search-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-soft)" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="text" id="historySearch" placeholder="Kerko me emer..." onkeyup="filterHistoryTable()">
                    </div>
                    <form method="GET" style="display:flex; align-items:center; gap:8px;">
                        <input type="date" name="history_date" class="history-date-input" value="<?= htmlspecialchars($history_date) ?>" onchange="this.form.submit()">
                        <?php if ($history_date !== ''): ?>
                            <a href="check_in.php" class="history-date-clear">Pastro</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-scroll">
                    <table id="historyTable">
                        <thead>
                            <tr>
                                <th>Emri</th>
                                <th>Roli</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance_history)): ?>
                                <tr><td colspan="4" class="empty-state"><div>🕒</div><p>Ende s'ka asnjë check-in.</p></td></tr>
                            <?php else: foreach ($attendance_history as $h): ?>
                                <tr data-search="<?= strtolower(htmlspecialchars($h['display_name'])) ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($h['display_name']) ?></strong>
                                        <div style="font-size:12px;color:var(--text-soft);"><?= date('d M Y', strtotime($h['work_date'])) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($h['role']) ?></td>
                                    <td>
                                        <?php if (!empty($h['check_in_time'])): ?>
                                            <span class="badge badge-confirmed"><?= date('H:i', strtotime($h['check_in_time'])) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-out">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($h['check_out_time'])): ?>
                                            <span class="badge badge-out"><?= date('H:i', strtotime($h['check_out_time'])) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Ende në punë</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                                <tr id="noHistoryResults" style="display:none;">
                                    <td colspan="4" class="empty-state"><div>🔍</div><p>Nuk u gjet asnjë rezultat.</p></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>
        function filterHistoryTable() {
            const filter = document.getElementById('historySearch').value.toLowerCase();
            const rows = document.querySelectorAll('#historyTable tbody tr:not(#noHistoryResults)');
            const noResults = document.getElementById('noHistoryResults');
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

            if (noResults) noResults.style.display = visible === 0 ? '' : 'none';
        }
        function filterStaffTable() {
            const filter = document.getElementById('staffSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#staffTable tbody tr:not(#noStaffResults)');
            const noResults = document.getElementById('noStaffResults');
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

            if (noResults) noResults.style.display = visible === 0 ? '' : 'none';
        }
    </script>
        
</body>
</html>