<?php
// login.php - Secure User Login Page

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';

// Check if redirect has error from auth.php
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Retrieve user from DB
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // Check password and status (BR-05: blocked if is_active is 0)
            if ($user && password_verify($password, $user['password_hash'])) {
                if ((int)$user['is_active'] === 1) {
                    // Regenerate session ID for security (prevents session fixation)
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];

                    header("Location: index.php");
                    exit();
                } else {
                    // Generic error to avoid revealing account status
                    $error = 'Invalid username or password.';
                }
            } else {
                // Generic error to avoid username enumeration
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'System error: ' . database_error_message($e, 'Please try again later.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | SRMS</title>
    <meta name="description" content="Access the Student Record Management System (SRMS). Secure institutional sign in.">
    
    <!-- Font Awesome Free 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Apply saved theme early to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="login-page">

    <div class="login-card glass-card animate-scale-in">
        <div class="login-brand">
            <div class="login-brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1 class="login-brand-name">SRMS</h1>
            <div class="login-brand-divider"></div>
            <p class="login-brand-sub">Tanzania School Information System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert" aria-live="assertive">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-body">
                    <div class="alert-title">Sign In Failed</div>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="on">
            <div class="form-group" style="margin-bottom: var(--sp-4);">
                <label for="username" class="form-label">
                    Username <span class="required-dot">*</span>
                </label>
                <div class="input-wrapper">
                    <span class="input-icon left"><i class="fa-solid fa-user"></i></span>
                    <input type="text" id="username" name="username" class="form-input has-icon-left" placeholder="e.g. admin" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-4);">
                <label for="password" class="form-label">
                    Password <span class="required-dot">*</span>
                </label>
                <div class="input-wrapper">
                    <span class="input-icon left"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-input has-icon-left has-icon-right" placeholder="••••••••" required autocomplete="current-password">
                    <span class="input-icon right clickable" id="password-toggle" role="button" aria-label="Toggle Password Visibility">
                        <i class="fa-solid fa-eye" id="toggle-eye-icon"></i>
                    </span>
                </div>
            </div>

            <div style="margin-bottom: 24px; display: flex; justify-content: flex-end;">
                <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" title="Toggle Theme" style="width: auto; padding: 0 16px; font-size: 0.85rem; font-weight: 500; gap: 6px;" aria-label="Toggle Dark/Light Theme">
                    <i class="fa-solid fa-moon" id="theme-moon-icon"></i>
                    <i class="fa-solid fa-sun" id="theme-sun-icon" style="display:none;"></i>
                    <span class="theme-label" style="margin-left: 4px;">Dark Mode</span>
                </button>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; font-size: var(--font-size-md);">
                <span>Sign In</span>
                <i class="fa-solid fa-right-to-bracket" style="margin-left: 4px;"></i>
            </button>
        </form>
        
        <div class="login-footer">
            <span class="login-footer-copy">Student Record Management System &copy; <?php echo date('Y'); ?></span>
        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');
        const toggleEyeIcon = document.getElementById('toggle-eye-icon');

        if (passwordToggle && passwordInput && toggleEyeIcon) {
            passwordToggle.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleEyeIcon.className = type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
            });
        }

        // Theme Toggle
        const themeBtn = document.getElementById('theme-toggle-btn');
        const sunIcon = document.getElementById('theme-sun-icon');
        const moonIcon = document.getElementById('theme-moon-icon');
        const themeLabel = themeBtn.querySelector('.theme-label');
        
        function applyThemeIcons(theme) {
            if (theme === 'dark') {
                if (sunIcon) sunIcon.style.display = 'inline-block';
                if (moonIcon) moonIcon.style.display = 'none';
                if (themeLabel) themeLabel.textContent = 'Light Mode';
            } else {
                if (sunIcon) sunIcon.style.display = 'none';
                if (moonIcon) moonIcon.style.display = 'inline-block';
                if (themeLabel) themeLabel.textContent = 'Dark Mode';
            }
        }

        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        applyThemeIcons(currentTheme);

        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme');
                const target = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', target);
                localStorage.setItem('theme', target);
                applyThemeIcons(target);
            });
        }
    </script>
</body>
</html>
