<?php
// delete_student.php - Delete Student Record (Admin Only)

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

// BR-06: Restricted to admins only
require_role('admin');

require_once __DIR__ . '/includes/header.php';

$student_id = $_GET['student_id'] ?? '';
$error = '';
$student = null;

if (empty($student_id)) {
    header("Location: students.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :id");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: students.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . database_error_message($e));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $del = $pdo->prepare("DELETE FROM students WHERE student_id = :id");
        $del->execute(['id' => $student_id]);
        
        // Redirect to students page
        header("Location: students.php");
        exit();
    } catch (PDOException $e) {
        $error = 'Failed to delete student record: ' . database_error_message($e);
    }
}
?>

<?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.SRMS.notify(<?php echo json_encode($error); ?>, 'danger', 'Deletion Failed');
        });
    </script>
<?php endif; ?>

<div class="delete-confirm-wrap animate-fade-in-up">
    <div class="danger-card">
        <div class="danger-card-header">
            <div class="danger-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h2 class="danger-card-title">Confirm Record Deletion</h2>
                <p class="danger-card-subtitle">Are you sure you want to permanently delete this student record? This action cannot be undone.</p>
            </div>
        </div>

        <div class="danger-card-body">
            <div class="student-preview">
                <div class="student-preview-row">
                    <i class="fa-solid fa-id-badge"></i>
                    <span class="preview-key">Registration Number:</span>
                    <span class="preview-val"><?php echo htmlspecialchars($student['reg_number']); ?></span>
                </div>
                <div class="student-preview-row">
                    <i class="fa-solid fa-user"></i>
                    <span class="preview-key">Full Name:</span>
                    <span class="preview-val" id="target-student-name"><?php echo htmlspecialchars($student['full_name']); ?></span>
                </div>
                <div class="student-preview-row">
                    <i class="fa-solid fa-school"></i>
                    <span class="preview-key">Class / Grade:</span>
                    <span class="preview-val"><?php echo htmlspecialchars($student['class_grade']); ?></span>
                </div>
                <div class="student-preview-row">
                    <i class="fa-solid fa-venus-mars"></i>
                    <span class="preview-key">Gender:</span>
                    <span class="preview-val"><?php echo htmlspecialchars($student['gender']); ?></span>
                </div>
                <div class="student-preview-row">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="preview-key">Enrolment Year:</span>
                    <span class="preview-val"><?php echo htmlspecialchars($student['enrolment_year']); ?></span>
                </div>
            </div>

            <form method="POST" action="delete_student.php?student_id=<?php echo urlencode($student_id); ?>" id="delete-student-form">
                <div class="confirm-name-wrap">
                    <label class="confirm-name-label" for="confirm_name">
                        Type the student's name <strong id="target-name-strong"><?php echo htmlspecialchars($student['full_name']); ?></strong> to confirm:
                    </label>
                    <input type="text" id="confirm_name" class="form-input" placeholder="Type name exactly as shown above" required autocomplete="off">
                    <div class="form-error" id="confirm-name-error" style="margin-top: 4px;"></div>
                </div>

                <div class="danger-card-footer">
                    <a href="students.php" class="btn btn-secondary" id="cancel-btn" autofocus>Cancel</a>
                    <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Yes, Delete Record</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const confirmInput = document.getElementById('confirm_name');
    const deleteBtn = document.getElementById('delete-btn');
    const targetName = document.getElementById('target-student-name').textContent.trim();
    const errorDiv = document.getElementById('confirm-name-error');
    const cancelBtn = document.getElementById('cancel-btn');

    // Autofocus cancel button as safe default
    if (cancelBtn) {
        cancelBtn.focus();
    }

    confirmInput.addEventListener('input', () => {
        const value = confirmInput.value.trim();
        if (value === targetName) {
            confirmInput.classList.remove('is-invalid');
            confirmInput.classList.add('is-valid');
            deleteBtn.removeAttribute('disabled');
            errorDiv.innerHTML = '';
        } else {
            confirmInput.classList.remove('is-valid');
            deleteBtn.setAttribute('disabled', 'true');
            if (value.length > 0) {
                confirmInput.classList.add('is-invalid');
                errorDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Name does not match exactly.';
            } else {
                confirmInput.classList.remove('is-invalid');
                errorDiv.innerHTML = '';
            }
        }
    });

    const form = document.getElementById('delete-student-form');
    form.addEventListener('submit', (e) => {
        if (confirmInput.value.trim() !== targetName) {
            e.preventDefault();
        } else {
            deleteBtn.classList.add('btn-loading');
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
