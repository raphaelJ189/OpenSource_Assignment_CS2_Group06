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
    die("Database error: " . database_error_message($e));
}

$current_year = (int)date('Y');
$years = range($current_year, $current_year - 5);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $class_grade = $_POST['class_grade'] ?? '';
    $enrolment_year = (int)($_POST['enrolment_year'] ?? $student['enrolment_year']);

    // Validation
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required.';
    
    $birth_timestamp = null;
    $today_timestamp = strtotime(date('Y-m-d'));
    if (empty($date_of_birth)) {
        $errors[] = 'Date of birth is required.';
    } else {
        $birth_timestamp = strtotime($date_of_birth);
        if ($birth_timestamp > $today_timestamp) {
            $errors[] = 'Date of birth cannot be in the future.';
        }
    }
    
    if (!in_array($gender, ['Male', 'Female'])) $errors[] = 'Invalid gender selected.';
    if (empty($class_grade)) $errors[] = 'Class/Grade is required.';
    
    if ($enrolment_year < 2000 || $enrolment_year > $current_year) {
        $errors[] = 'Invalid enrolment year.';
    } elseif ($birth_timestamp !== null && $birth_timestamp <= $today_timestamp) {
        $birth_year = (int)date('Y', $birth_timestamp);
        if (($enrolment_year - $birth_year) < 3) {
            $errors[] = 'Student must be at least 3 years old at the time of enrolment.';
        }
    }

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
            $error = 'Database update failed: ' . database_error_message($e);
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<?php if (!empty($success)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.SRMS.notify(<?php echo json_encode($success); ?>, 'success', 'Profile Updated');
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

<div class="glass-card animate-fade-in-up">
    <div class="card-header">
        <div class="card-header-inner">
            <div class="card-icon primary">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h2 class="card-title">Edit Student Profile</h2>
                <p class="card-subtitle">Update details for student: <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>. The registration number cannot be changed.</p>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="edit_student.php?student_id=<?php echo urlencode($student_id); ?>" autocomplete="off" novalidate id="edit-student-form">
            
            <fieldset class="form-section">
                <legend class="form-section-title">
                    <i class="fa-solid fa-id-card"></i> Student Identification
                </legend>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="reg_number_display">
                            Registration Number <i class="fa-solid fa-lock" title="Immutable Field" style="margin-left: 2px;"></i>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-id-badge"></i></span>
                            <input type="text" id="reg_number_display" class="form-input has-icon-left input-readonly" value="<?php echo htmlspecialchars($student['reg_number']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="full_name">
                            Full Name <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-id-card"></i></span>
                            <input type="text" id="full_name" name="full_name" class="form-input has-icon-left" placeholder="e.g. Amina Juma" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                        <div class="form-hint" style="display: flex; justify-content: space-between; margin-top: 4px;">
                            <span>Min 2 characters, alphabetic characters and spaces only.</span>
                            <span id="char-counter">0 / 100</span>
                        </div>
                        <div class="form-error" id="error-full_name"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="date_of_birth">
                            Date of Birth <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-calendar"></i></span>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input has-icon-left" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                        </div>
                        <div class="form-error" id="error-date_of_birth"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="gender">
                            Gender <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-venus-mars"></i></span>
                            <select id="gender" name="gender" class="form-input has-icon-left" required>
                                <option value="">— Select Gender —</option>
                                <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-error" id="error-gender"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend class="form-section-title">
                    <i class="fa-solid fa-graduation-cap"></i> Academic Details
                </legend>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="class_grade">
                            Class / Grade <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-school"></i></span>
                            <select id="class_grade" name="class_grade" class="form-input has-icon-left" required>
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
                        <div class="form-error" id="error-class_grade"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="enrolment_year">
                            Year of Enrolment <span class="required-dot">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon left"><i class="fa-solid fa-calendar-days"></i></span>
                            <select id="enrolment_year" name="enrolment_year" class="form-input has-icon-left" required>
                                <?php foreach ($years as $yr): ?>
                                    <option value="<?php echo $yr; ?>" <?php echo ((int)$student['enrolment_year'] === $yr) ? 'selected' : ''; ?>>
                                        <?php echo $yr; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-error" id="error-enrolment_year"></div>
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <a href="students.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-submit-update">
                    <span class="btn-text">Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('edit-student-form');
    const fullName = document.getElementById('full_name');
    const dob = document.getElementById('date_of_birth');
    const gender = document.getElementById('gender');
    const classGrade = document.getElementById('class_grade');
    const enrolmentYear = document.getElementById('enrolment_year');
    const charCounter = document.getElementById('char-counter');
    const submitBtn = document.getElementById('btn-submit-update');

    // Update character counter initially
    charCounter.textContent = `${fullName.value.length} / 100`;

    // 1. Character counter
    fullName.addEventListener('input', () => {
        const len = fullName.value.length;
        charCounter.textContent = `${len} / 100`;
        if (len > 100) {
            fullName.classList.add('is-invalid');
        } else {
            fullName.classList.remove('is-invalid');
        }
        validateFullName();
    });

    function showError(input, elementId, message) {
        const errorDiv = document.getElementById(elementId);
        if (message) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            errorDiv.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${message}`;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            errorDiv.innerHTML = '';
        }
    }

    // 2. Validate functions
    function validateFullName() {
        const val = fullName.value.trim();
        if (val.length < 2) {
            showError(fullName, 'error-full_name', 'Full name must be at least 2 characters long.');
            return false;
        }
        if (!/^[a-zA-Z\s]+$/.test(val)) {
            showError(fullName, 'error-full_name', 'Full name can only contain letters and spaces.');
            return false;
        }
        showError(fullName, 'error-full_name', '');
        return true;
    }

    function validateDOB() {
        const val = dob.value;
        if (!val) {
            showError(dob, 'error-date_of_birth', 'Date of birth is required.');
            return false;
        }
        const birthDate = new Date(val);
        const today = new Date();
        if (birthDate > today) {
            showError(dob, 'error-date_of_birth', 'Date of birth cannot be in the future.');
            return false;
        }
        
        // Age check at enrolment year
        const selectedYear = parseInt(enrolmentYear.value);
        const birthYear = birthDate.getFullYear();
        if (selectedYear - birthYear < 3) {
            showError(dob, 'error-date_of_birth', 'Student must be at least 3 years old at enrolment.');
            return false;
        }
        
        showError(dob, 'error-date_of_birth', '');
        return true;
    }

    function validateGender() {
        if (!gender.value) {
            showError(gender, 'error-gender', 'Gender is required.');
            return false;
        }
        showError(gender, 'error-gender', '');
        return true;
    }

    function validateClassGrade() {
        if (!classGrade.value) {
            showError(classGrade, 'error-class_grade', 'Class/Grade is required.');
            return false;
        }
        showError(classGrade, 'error-class_grade', '');
        return true;
    }

    // Blur/Change listeners
    fullName.addEventListener('blur', validateFullName);
    dob.addEventListener('blur', validateDOB);
    gender.addEventListener('change', validateGender);
    classGrade.addEventListener('change', validateClassGrade);
    enrolmentYear.addEventListener('change', validateDOB);

    // Form submit check
    form.addEventListener('submit', (e) => {
        const f1 = validateFullName();
        const f2 = validateDOB();
        const f3 = validateGender();
        const f4 = validateClassGrade();

        if (!(f1 && f2 && f3 && f4)) {
            e.preventDefault();
            window.SRMS.notify('Please correct the validation errors before submitting.', 'danger', 'Validation Error');
        } else {
            // Show loading spinner on button
            submitBtn.classList.add('btn-loading');
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
