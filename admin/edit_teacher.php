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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.SRMS.notify(<?php echo json_encode($success); ?>, 'success', 'Account Updated');
        });
    </script>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.SRMS.notify(<?php echo json_encode($error); ?>, 'danger', 'Update Failed');
        });
    </script>
<?php endif; ?>

<div class="glass-card animate-fade-in-up" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-header-inner">
            <div class="card-icon primary">
                <i class="fa-solid fa-user-gear"></i>
            </div>
            <div>
                <h2 class="card-title">Manage Teacher Account</h2>
                <p class="card-subtitle">Edit account profile, reset password, or toggle activation status for <?php echo htmlspecialchars($teacher['full_name']); ?>.</p>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="edit_teacher.php?user_id=<?php echo urlencode($user_id); ?>" autocomplete="off" novalidate id="edit-teacher-form">
            <fieldset class="form-section">
                <legend class="form-section-title">
                    <i class="fa-solid fa-id-card"></i> Teacher Identification
                </legend>
                
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label class="form-label" for="full_name">
                            Full Name <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="full_name" name="full_name" class="form-input has-icon-left" placeholder="e.g. John Doe Mwalimu" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                        </div>
                        <div class="form-error" id="error-full_name"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="username">
                            Username <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-user-gear"></i></span>
                            <input type="text" id="username" name="username" class="form-input has-icon-left" placeholder="e.g. johndoe" value="<?php echo htmlspecialchars($teacher['username']); ?>" required autocomplete="off">
                        </div>
                        <div class="form-error" id="error-username"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            Password (leave blank to keep current)
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-input has-icon-left" placeholder="••••••••">
                        </div>
                        <div class="form-error" id="error-password"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="form-section-title">
                    <i class="fa-solid fa-shield-halved"></i> Account Status
                </legend>
                
                <div class="status-toggle-card">
                    <div class="status-toggle-info">
                        <strong class="status-toggle-title">Account Active Status</strong>
                        <span class="status-toggle-desc">Deactivated teachers will be blocked from logging in immediately.</span>
                    </div>
                    <div class="status-toggle-action">
                        <label class="switch-container">
                            <input type="checkbox" name="is_active" value="1" <?php echo ((int)$teacher['is_active'] === 1) ? 'checked' : ''; ?> class="switch-input">
                            <span class="switch-slider"></span>
                        </label>
                        <span class="switch-label-text">Active</span>
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Updates</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('edit-teacher-form');
    const fullName = document.getElementById('full_name');
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    form.addEventListener('submit', (e) => {
        let valid = true;

        // Reset errors
        document.querySelectorAll('.form-error').forEach(d => d.textContent = '');
        document.querySelectorAll('.form-input').forEach(i => i.classList.remove('is-invalid'));

        // Validate Full Name
        if (!window.SRMS.validate.required(fullName)) {
            fullName.classList.add('is-invalid');
            document.getElementById('error-full_name').textContent = 'Full name is required.';
            valid = false;
        }

        // Validate Username
        if (!window.SRMS.validate.required(username)) {
            username.classList.add('is-invalid');
            document.getElementById('error-username').textContent = 'Username is required.';
            valid = false;
        } else if (!window.SRMS.validate.minLength(username, 3)) {
            username.classList.add('is-invalid');
            document.getElementById('error-username').textContent = 'Username must be at least 3 characters.';
            valid = false;
        }

        // Validate Password (only if filled)
        if (password.value.length > 0 && !window.SRMS.validate.minLength(password, 6)) {
            password.classList.add('is-invalid');
            document.getElementById('error-password').textContent = 'Password must be at least 6 characters.';
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            window.SRMS.notify('Please correct the validation errors on the form.', 'warning', 'Validation Warning');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
