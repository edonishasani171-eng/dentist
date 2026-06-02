<?php
// admin/logout.php
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Destroy the actual session cookies if they exist
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session completely
session_destroy();

// we render the HTML/CSS animation screen before letting JS send them home.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Mirupafshim</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:       #1a7a5e;
            --green-light: #e8f5f1;
            --cream:       #faf8f4;
            --text:        #1a1a18;
            --text-soft:   #8a8a82;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Fullscreen Screen Setup */
        .logout-screen {
            text-align: center;
            opacity: 0;
            transform: scale(0.98);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .logout-screen.active {
            opacity: 1;
            transform: scale(1);
        }

        /* Pulse Loader Matching the Login Page */
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

        .pulse-loader svg {
            width: 36px;
            height: 36px;
            fill: white;
            z-index: 2;
        }

        .pulse-loader::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--green);
            border-radius: 20px;
            z-index: 1;
            opacity: 0.4;
            animation: pulse-ring 2s infinite ease-out;
        }

        .logout-title {
            font-family: 'DM Serif Display', serif;
            font-size: 32px;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .logout-subtitle {
            font-size: 15px;
            color: var(--text-soft);
        }

        @keyframes pulse-main {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.4); opacity: 0; }
        }
    </style>
</head>
<body>

    <div id="logoutContainer" class="logout-screen">
        <div class="pulse-loader">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
            </svg>
        </div>
        <h1 class="logout-title">Shihemi së shpejti!</h1>
        <p class="logout-subtitle">Duke u shkëputur sigurt...</p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('logoutContainer');
            
            // Instantly transition the elements smoothly into view
            setTimeout(() => {
                container.classList.add('active');
            }, 50);
            
            // Hold the animation on screen for 2 seconds, then go to home page
            setTimeout(function() {
                window.location.href = '../index.php';
            }, 2000);
        });
    </script>

</body>
</html>