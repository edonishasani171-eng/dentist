<?php
// admin/login.php
session_start();

// Include the centralized database connection file
require_once 'db.php'; 

$error = '';
$show_animation = false; // Flag to trigger the animation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            // $pdo is automatically created and configured inside your db.php file
            if (!isset($pdo)) {
                throw new Exception("Lidhja me databazën nuk është konfiguruar siç duhet.");
            }

            // Fetch the user from your Neon PostgreSQL database
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username AND status = 'Active'");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check against your encrypted password 'admin123'
                if (password_verify($password, $user['password'])) {
                    $_SESSION['authenticated'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Toggle animation flag to keep loader visible on redirect
                    $show_animation = true;
                } else {
                    $error = "Gabim fjalëkalimi!";
                }
            } else {
                $error = "Përdoruesi nuk ekziston ose është jo-aktiv!";
            }

        } catch (Exception $e) {
            $error = "Lidhja dështoi: " . $e->getMessage();
        }
    } else {
        $error = "Ju lutem plotësoni të gjitha fushat!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentCare Pejë — Staff Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:       #1a7a5e;
            --green-dark:  #0f5441;
            --green-light: #e8f5f1;
            --cream:       #faf8f4;
            --text:        #1a1a18;
            --text-mid:    #4a4a45;
            --text-soft:   #8a8a82;
            --white:       #ffffff;
            --border:      rgba(26,26,24,0.10);
            --red:         #c0392b;
            --shadow:      0 8px 32px rgba(26,122,94,0.06);
            --radius:      16px;
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
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            /* Na ndihmon që faqja të shfaqet me një fade-in të butë në fillim */
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        /* Ambient Background Glow */
        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--green-light) 0%, transparent 70%);
            top: 10%;
            left: 10%;
            z-index: 0;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: var(--green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .logo-icon svg {
            width: 24px;
            height: 24px;
            fill: white;
        }

        .login-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 26px;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .login-header p {
            font-size: 14px;
            color: var(--text-soft);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 11px;
            font-weight: 500;
            color: var(--text-mid);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper svg {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            stroke: var(--text-soft);
            fill: none;
            stroke-width: 2;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: all 0.2s;
        }

        input:focus {
            border-color: var(--green);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26,122,94,0.1);
        }

        input:focus + svg {
            stroke: var(--green);
        }

        /* Alert styling */
        .alert {
            background: #fdf0ef;
            border: 1px solid #f5c6c2;
            color: var(--red);
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: var(--green-dark);
            box-shadow: 0 4px 16px rgba(26,122,94,0.2);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--green);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--green-dark);
            text-decoration: underline;
        }

        /* FULLSCREEN LOADING TRANSITION OVERLAY */
        .login-loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--cream);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
        }

        .login-loading-screen.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-animation-box {
            text-align: center;
        }

        /* Smooth double pulse circle */
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

        /* Loaded states for entrance fade-ins */
        .login-loading-screen.active .welcome-title,
        .login-loading-screen.active .welcome-subtitle {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes pulse-main {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        .exit-active .loading-content, 
        .exit-active #loadingOverlay > div { 
            opacity: 0 !important;
            transform: translateY(60px) !important;
            transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.4s ease !important;
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

    <div id="loadingScreen" class="login-loading-screen <?php echo $show_animation ? 'active' : ''; ?>">
        <div class="loading-animation-box">
            <div class="pulse-loader">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
                </svg>
            </div>
            <h1 class="welcome-title">Mirëseerdhët, Staf!</h1>
            <p class="welcome-subtitle">Duke u hapur paneli i menaxhimit...</p>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="loginProgressBar"></div>
            </div>
        </div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C9.5 2 7.5 3.5 6.5 5.5C5.5 4.5 4 4 3 5C1.5 6.5 2 9 3 11C4 13 5 14 5.5 16C6 18 6 20 7 21C7.5 21.5 8.5 22 9 21C9.5 20 9.5 18 10 17C10.5 16 11 15.5 12 15.5C13 15.5 13.5 16 14 17C14.5 18 14.5 20 15 21C15.5 22 16.5 21.5 17 21C18 20 18 18 18.5 16C19 14 20 13 21 11C22 9 22.5 6.5 21 5C20 4 18.5 4.5 17.5 5.5C16.5 3.5 14.5 2 12 2Z"/>
                </svg>
            </div>
            <h2>Hyrja vetëm për Staf</h2>
            <p>Vetëm stafi i autorizuar mund të hyjë në këtë zonë.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Enter username..." required autocomplete="off">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter password..." required>
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
            </div>

            <button type="submit" class="btn-login">Hyr</button>
        </form>

        <a href="../index.php" id="backToMain" class="back-link">← Kthehu tek faqja Kryesore</a>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {  
        // 1. Smooth Fade-in kur hapet faqja e login-it nga linku "Portal i Personalit"
        document.body.style.opacity = "1";

        const loader = document.getElementById('loadingScreen');
        
        // 2. FIXED: Triggers the slide-down animation right before switching to the dashboard
        if (loader.classList.contains('active')) {
            animateProgress('loginProgressBar', 1800);

            setTimeout(function() {
                loader.classList.add('exit-active');
            }, 1800);

            setTimeout(function() {
                window.location.href = 'admin_dashboard.php';
            }, 2200);
        }

        // 3. Kur klikohet butoni "Hyr" - nisja e menjëhershme e animacionit smooth para procesimit të PHP
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(e) {
            // Kontrollojmë nëse fushat nuk janë të zbrazëta para se të nisim animacionin
            if(document.getElementById('username').value.trim() !== "" && document.getElementById('password').value !== "") {
                e.preventDefault(); // Ndalim përkohësisht dërgimin e formës
                
                // Shfaqim loader-in me fade-in të butë
                loader.classList.add('active');

                // Animate progress bar
                animateProgress('loginProgressBar', 600);

                setTimeout(() => {
                    form.submit();
                }, 600);
            }
        });

        // 4. Kalim i butë nëse klikon "Kthehu tek faqja Kryesore"
        const backLink = document.getElementById('backToMain');
        backLink.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.style.opacity = "0";
            setTimeout(() => {
                window.location.href = backLink.getAttribute('href');
            }, 400);
        });
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