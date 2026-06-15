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
            $error = 'Database write failed: ' . database_error_message($e);
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.SRMS.notify(<?php echo json_encode($success); ?>, 'success', 'Account Created');
        });
    </script>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.SRMS.notify(<?php echo json_encode($error); ?>, 'danger', 'Provisioning Failed');
        });
    </script>
<?php endif; ?>

<div class="glass-card animate-fade-in-up" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-header-inner">
            <div class="card-icon primary">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h2 class="card-title">Provision New Teacher</h2>
                <p class="card-subtitle">Create login credentials for a new teacher account. Password must be at least 6 characters.</p>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="create_teacher.php" autocomplete="off" novalidate id="create-teacher-form">
            <fieldset class="form-section">
                <legend class="form-section-title">
                    <i class="fa-solid fa-id-card"></i> Account Information
                </legend>
                
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label class="form-label" for="full_name">
                            Full Name <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="full_name" name="full_name" class="form-input has-icon-left" placeholder="e.g. John Doe Mwalimu" value="<?php echo fv('full_name', $form); ?>" required>
                        </div>
                        <div class="form-error" id="error-full_name"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="username">
                            Username <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-user-gear"></i></span>
                            <input type="text" id="username" name="username" class="form-input has-icon-left" placeholder="e.g. johndoe" value="<?php echo fv('username', $form); ?>" required autocomplete="off">
                        </div>
                        <div class="form-error" id="error-username"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            Password <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-input has-icon-left" placeholder="••••••••" required>
                        </div>
                        <div class="form-error" id="error-password"></div>
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Create Account</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('create-teacher-form');
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

        // Validate Password
        if (!window.SRMS.validate.required(password)) {
            password.classList.add('is-invalid');
            document.getElementById('error-password').textContent = 'Password is required.';
            valid = false;
        } else if (!window.SRMS.validate.minLength(password, 6)) {
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
