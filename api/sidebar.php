<?php
// admin/sidebar.php
// Expects two variables to be set BEFORE this file is included:
// $current_page          -> string identifying which page is active (e.g. 'dashboard', 'appointments', 'register_pacient', 'register_worker', 'checkin')
// $new_appointments_count -> int, for the notification badge (default to 0 if not set)

$new_appointments_count = $new_appointments_count ?? 0;
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

    <div class="menu-item logout-btn">
        <a href="logout.php" style="color: #c0392b;">
            <svg viewBox="0 0 24 24" style="stroke: #c0392b;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            Log Out
        </a>
    </div>
</aside>