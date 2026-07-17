<?php
if (!isset($new_appointments_count)) {
    $new_appointments_count = 0;
    try {
        if (isset($pdo)) {
            $pending_stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'");
            $new_appointments_count = (int) $pending_stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $new_appointments_count = 0;
    }
}
// ── LIVE UNREAD MESSAGE COUNT (works on every page, not just messages.php) ──
if (!isset($new_messages_count)) {
    $new_messages_count = 0;
    try {
        if (isset($pdo)) {
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
            $msg_count_stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'New'");
            $new_messages_count = (int) $msg_count_stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $new_messages_count = 0;
    }
}
?>
<aside>
    <a href="admin_dashboard.php?page=dashboard" class="brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24">
                <path
                    d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z" />
            </svg>
        </div>
        <span class="brand-text">Dent<span>Care</span></span>
    </a>

    <button class="menu-toggle" id="menu-toggle" aria-label="Hap menunë">
        <span></span>
        <span></span>
    </button>

    <ul class="menu" id="nav-links">
        <li class="menu-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=dashboard">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
                <span class="notif-badge <?= $new_appointments_count == 0 ? 'hidden' : '' ?>">
                    <?= $new_appointments_count > 99 ? '99+' : $new_appointments_count ?>
                </span>
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'appointments' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=appointments">
                <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Aplikimet
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'register_patient' ? 'active' : '' ?>">
            <a href="register_patient.php">
                <svg viewBox="0 0 24 24">
                    <path d="M9 12h6"></path>
                    <path d="M12 9v6"></path>
                    <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                </svg>
                Regjistro Pacientin
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'messages' ? 'active' : '' ?>">
            <a href="messages.php">
                <svg viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Mesazhet e Klientëve
                <span class="notif-badge <?= ($new_messages_count ?? 0) == 0 ? 'hidden' : '' ?>">
                    <?= ($new_messages_count ?? 0) > 99 ? '99+' : ($new_messages_count ?? 0) ?>
                </span>
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'schedules' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=schedules">
                <svg viewBox="0 0 24 24">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                    <path d="M12 6v6l4 2"></path>
                </svg>
                Oraret
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'register_worker' ? 'active' : '' ?>">
            <a href="register_worker.php">
                <svg viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                Regjistro Punëtor
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'checkin' ? 'active' : '' ?>">
            <a href="check_in.php">
                <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                Check In
            </a>
        </li>
    </ul>

    <!-- Logout Button Trigger -->
    <div class="menu-item logout-btn">
        <a href="#" id="logoutTrigger" style="color: #c0392b;">
            <svg viewBox="0 0 24 24" style="stroke: #c0392b;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            Log Out
        </a>
    </div>

    <!-- Custom Styled Logout Modal Overlay -->
    <div id="logoutModal" style="position: fixed; inset: 0; width: 100vw; height: 100vh; background: rgba(26, 26, 24, 0.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 99999; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
        <div style="background: #ffffff; border: 1px solid rgba(26,26,24,0.10); border-radius: 14px; padding: 32px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.08); transform: translateY(20px); transition: transform 0.3s ease;" id="logoutModalContent">
            <div style="width: 56px; height: 56px; background: #fdf2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; border: 1px solid #f5baba;">
                <svg viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #c0392b; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <h3 style="font-family: 'DM Serif Display', serif; font-size: 22px; color: #1a1a18; margin-bottom: 8px;">A jeni të sigurt?</h3>
            <p style="font-family: 'DM Sans', sans-serif; font-size: 14px; color: #4a4a44; margin-bottom: 24px; line-height: 1.5;">Dëshironi të çndajeni nga sistemi i DentCare Pejë?</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <button id="cancelLogout" style="padding: 12px; background: #faf8f4; border: 1px solid rgba(26,26,24,0.10); border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500; color: #4a4a45; cursor: pointer; transition: background 0.2s;">Anulo</button>
                <a href="logout.php" style="padding: 12px; background: #c0392b; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500; color: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: background 0.2s; border: none;">Po, Çkyçu</a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const trigger = document.getElementById('logoutTrigger');
        const modal = document.getElementById('logoutModal');
        const content = document.getElementById('logoutModalContent');
        const cancelBtn = document.getElementById('cancelLogout');

        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.opacity = '1';
            modal.style.pointerEvents = 'auto';
            content.style.transform = 'translateY(0)';
        });

        function closeModal() {
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
            content.style.transform = 'translateY(20px)';
        }

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    });
    </script>
</aside>