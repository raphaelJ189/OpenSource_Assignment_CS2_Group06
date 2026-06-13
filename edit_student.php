<?php
// edit_student.php - Edit Student Records

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';
$student = null;

$student_id = $_GET['student_id'] ?? '';

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
    die("Database error: " . $e->getMessage());
}

$current_year = (int)date('Y');
$years = range($current_year + 1, $current_year - 5);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $class_grade = $_POST['class_grade'] ?? '';
    $enrolment_year = (int)($_POST['enrolment_year'] ?? $student['enrolment_year']);

    // Validation
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($date_of_birth)) $errors[] = 'Date of birth is required.';
    if (!in_array($gender, ['Male', 'Female'])) $errors[] = 'Invalid gender selected.';
    if (empty($class_grade)) $errors[] = 'Class/Grade is required.';
    if ($enrolment_year < 2000 || $enrolment_year > ($current_year + 5)) $errors[] = 'Invalid enrolment year.';

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare("
                UPDATE students 
                SET full_name = :full_name, 
                    date_of_birth = :date_of_birth, 
                    gender = :gender, 
                    class_grade = :class_grade, 
                    enrolment_year = :enrolment_year 
                WHERE student_id = :id
            ");
            $upd->execute([
                'full_name' => $full_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'class_grade' => $class_grade,
                'enrolment_year' => $enrolment_year,
                'id' => $student_id
            ]);
            
            $success = 'Student record updated successfully.';
            
            // Reload student info
            $stmt->execute(['id' => $student_id]);
            $student = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Database update failed: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
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

<div class="glass-card" style="padding: 36px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid var(--border-color);">
        <div class="stats-icon primary" style="margin-bottom: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        </div>
        <div>
            <h2 style="font-size: 1.2rem; font-weight: 600;">Edit Student Profile</h2>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Update details for student: <?php echo htmlspecialchars($student['full_name']); ?>. The registration number cannot be changed.</p>
        </div>
    </div>

    <form method="POST" action="edit_student.php?student_id=<?php echo urlencode($student_id); ?>" autocomplete="off" novalidate>
        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Student Identification</h3>
        
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group">
                <label class="form-label" for="reg_number_display">Registration Number</label>
                <input type="text" id="reg_number_display" class="form-input" style="background-color: var(--primary-light); cursor: not-allowed; font-weight: 600; color: var(--primary);" value="<?php echo htmlspecialchars($student['reg_number']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. Amina Juma" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="date_of_birth">Date of Birth *</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="gender">Gender *</label>
                <select id="gender" name="gender" class="form-input" required>
                    <option value="">— Select Gender —</option>
                    <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
        </div>

        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Academic Details</h3>
        
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group">
                <label class="form-label" for="class_grade">Class / Grade *</label>
                <select id="class_grade" name="class_grade" class="form-input" required>
                    <option value="">— Select Class/Grade —</option>
                    <optgroup label="Primary School">
                        <option value="Standard 1" <?php echo ($student['class_grade'] === 'Standard 1') ? 'selected' : ''; ?>>Standard 1</option>
                        <option value="Standard 2" <?php echo ($student['class_grade'] === 'Standard 2') ? 'selected' : ''; ?>>Standard 2</option>
                        <option value="Standard 3" <?php echo ($student['class_grade'] === 'Standard 3') ? 'selected' : ''; ?>>Standard 3</option>
                        <option value="Standard 4" <?php echo ($student['class_grade'] === 'Standard 4') ? 'selected' : ''; ?>>Standard 4</option>
                        <option value="Standard 5" <?php echo ($student['class_grade'] === 'Standard 5') ? 'selected' : ''; ?>>Standard 5</option>
                        <option value="Standard 6" <?php echo ($student['class_grade'] === 'Standard 6') ? 'selected' : ''; ?>>Standard 6</option>
                        <option value="Standard 7" <?php echo ($student['class_grade'] === 'Standard 7') ? 'selected' : ''; ?>>Standard 7</option>
                    </optgroup>
                    <optgroup label="Secondary School">
                        <option value="Form 1" <?php echo ($student['class_grade'] === 'Form 1') ? 'selected' : ''; ?>>Form 1</option>
                        <option value="Form 2" <?php echo ($student['class_grade'] === 'Form 2') ? 'selected' : ''; ?>>Form 2</option>
                        <option value="Form 3" <?php echo ($student['class_grade'] === 'Form 3') ? 'selected' : ''; ?>>Form 3</option>
                        <option value="Form 4" <?php echo ($student['class_grade'] === 'Form 4') ? 'selected' : ''; ?>>Form 4</option>
                        <option value="Form 5" <?php echo ($student['class_grade'] === 'Form 5') ? 'selected' : ''; ?>>Form 5</option>
                        <option value="Form 6" <?php echo ($student['class_grade'] === 'Form 6') ? 'selected' : ''; ?>>Form 6</option>
                    </optgroup>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="enrolment_year">Year of Enrolment *</label>
                <select id="enrolment_year" name="enrolment_year" class="form-input" required>
                    <?php foreach ($years as $yr): ?>
                        <option value="<?php echo $yr; ?>" <?php echo ((int)$student['enrolment_year'] === $yr) ? 'selected' : ''; ?>>
                            <?php echo $yr; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: flex-end;">
            <a href="students.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
