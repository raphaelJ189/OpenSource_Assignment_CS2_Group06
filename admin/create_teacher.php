<?php
// admin/create_teacher.php - Provision Teacher Accounts (Admin Only)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

// Restricted to admins only
require_role('admin');

$success = '';
$error = '';
$form = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $form = [
        'full_name' => $full_name,
        'username' => $username
    ];

    // Validation
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (empty($errors)) {
        try {
            // Check for duplicate username (BR-04)
            $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :uname");
            $chk->execute(['uname' => $username]);
            if ($chk->fetchColumn() > 0) {
                $error = 'Username <strong>' . htmlspecialchars($username) . '</strong> is already taken.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users 
                        (full_name, username, password_hash, role, is_active)
                    VALUES 
                        (:full_name, :username, :password_hash, 'teacher', 1)
                ");
                $stmt->execute([
                    'full_name' => $full_name,
                    'username' => $username,
                    'password_hash' => $hashed_password
                ]);

                $success = 'Teacher account for <strong>' . htmlspecialchars($full_name) . '</strong> created successfully.';
                $form = []; // Clear form
            }
        } catch (PDOException $e) {
            $error = 'Database write failed: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

require_once __DIR__ . '/../includes/header.php';

function fv($key, $form) {
    return htmlspecialchars($form[$key] ?? '');
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="margin-bottom: 24px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?php echo $success; ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="margin-bottom: 24px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<div class="glass-card" style="padding: 36px; max-width: 700px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid var(--border-color);">
        <div class="stats-icon primary" style="margin-bottom: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <div>
            <h2 style="font-size: 1.2rem; font-weight: 600;">Provision New Teacher</h2>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Create login credentials for a new teacher account. Password must be at least 6 characters.</p>
        </div>
    </div>

    <form method="POST" action="create_teacher.php" autocomplete="off" novalidate>
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. John Doe Mwalimu" value="<?php echo fv('full_name', $form); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="e.g. johndoe" value="<?php echo fv('username', $form); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: flex-end;">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create Account
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
