<?php
// admin/edit_teacher.php - Edit Teacher Account details and Status (Admin Only)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

// Restricted to admins only
require_role('admin');

$success = '';
$error = '';
$teacher = null;

$user_id = $_GET['user_id'] ?? '';

if (empty($user_id)) {
    header("Location: dashboard.php");
    exit();
}

try {
    // Fetch teacher details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id AND role = 'teacher'");
    $stmt->execute(['id' => $user_id]);
    $teacher = $stmt->fetch();
    
    if (!$teacher) {
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!empty($password) && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (empty($errors)) {
        try {
            // Check for duplicate username (excluding current user)
            $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :uname AND user_id != :id");
            $chk->execute(['uname' => $username, 'id' => $user_id]);
            if ($chk->fetchColumn() > 0) {
                $error = 'Username <strong>' . htmlspecialchars($username) . '</strong> is already in use by another account.';
            } else {
                if (!empty($password)) {
                    // Update details including password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("
                        UPDATE users 
                        SET full_name = :full_name, 
                            username = :username, 
                            password_hash = :password_hash, 
                            is_active = :is_active 
                        WHERE user_id = :id
                    ");
                    $upd->execute([
                        'full_name' => $full_name,
                        'username' => $username,
                        'password_hash' => $hashed_password,
                        'is_active' => $is_active,
                        'id' => $user_id
                    ]);
                } else {
                    // Update details excluding password
                    $upd = $pdo->prepare("
                        UPDATE users 
                        SET full_name = :full_name, 
                            username = :username, 
                            is_active = :is_active 
                        WHERE user_id = :id
                    ");
                    $upd->execute([
                        'full_name' => $full_name,
                        'username' => $username,
                        'is_active' => $is_active,
                        'id' => $user_id
                    ]);
                }
                
                $success = 'Teacher account details updated successfully.';
                
                // Reload teacher details
                $stmt->execute(['id' => $user_id]);
                $teacher = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = 'Database write failed: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

require_once __DIR__ . '/../includes/header.php';
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
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </div>
        <div>
            <h2 style="font-size: 1.2rem; font-weight: 600;">Manage Teacher Account</h2>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Edit account profile, reset password, or toggle activation status for <?php echo htmlspecialchars($teacher['full_name']); ?>.</p>
        </div>
    </div>

    <form method="POST" action="edit_teacher.php?user_id=<?php echo urlencode($user_id); ?>" autocomplete="off" novalidate>
        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Teacher Identification</h3>
        
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. John Doe Mwalimu" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="e.g. johndoe" value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••">
            </div>
        </div>

        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Account Status</h3>
        
        <div style="background-color: var(--primary-light); padding: 20px; border-radius: 12px; margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">Account Active Status</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Deactivated teachers will be blocked from logging in immediately.</span>
            </div>
            <div>
                <label class="form-label" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 0; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?php echo ((int)$teacher['is_active'] === 1) ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                    <span style="font-weight: 600; font-size: 0.95rem;">Active</span>
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: flex-end;">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save Updates
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
