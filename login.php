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
            $error = 'System error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Record Management System (SRMS)</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Apply saved theme early to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="login-wrapper">

    <div class="login-card glass-card animate-fade-in">
        <div class="login-header">
            <div class="logo-icon">SRMS</div>
            <h2 class="logo-text" style="display: block; font-size: 1.6rem; margin-bottom: 8px;">SRMS</h2>
            <p>Student Record Management System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="e.g. admin" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <div style="margin-bottom: 24px; display: flex; justify-content: flex-end;">
                <button type="button" class="theme-toggle" id="theme-toggle-btn" title="Toggle Theme" style="width: auto; padding: 0 16px; font-size: 0.85rem; font-weight: 500; gap: 6px;">
                    <span class="theme-label">Dark Mode</span>
                </button>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Sign In
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 24px; font-size: 0.8rem; color: var(--text-muted);">
            Student Record Management System &copy; <?php echo date('Y'); ?>
        </div>
    </div>

    <script>
        const themeBtn = document.getElementById('theme-toggle-btn');
        const themeLabel = themeBtn.querySelector('.theme-label');
        
        function updateThemeUI(theme) {
            themeLabel.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
        }

        updateThemeUI(document.documentElement.getAttribute('data-theme'));

        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
        });
    </script>
</body>
</html>
