<?php
// register_student.php - Student Registration Form

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';
$form = [];

$current_year = (int)date('Y');
$years = range($current_year, $current_year - 5); // From current year down to 5 years ago

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $class_grade = $_POST['class_grade'] ?? '';
    $year = (int)($_POST['enrolment_year'] ?? $current_year);

    $form = [
        'full_name' => $full_name,
        'date_of_birth' => $date_of_birth,
        'gender' => $gender,
        'class_grade' => $class_grade,
        'enrolment_year' => $year
    ];

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
    
    if ($year < 2000 || $year > $current_year) {
        $errors[] = 'Invalid enrolment year.';
    } elseif ($birth_timestamp !== null && $birth_timestamp <= $today_timestamp) {
        $birth_year = (int)date('Y', $birth_timestamp);
        if (($year - $birth_year) < 3) {
            $errors[] = 'Student must be at least 3 years old at the time of enrolment.';
        }
    }

    if (empty($errors)) {
        // Generate registration number and insert within transaction
        $pdo->beginTransaction();
        try {
            // Find max sequential number for the given year
            $pattern = "S" . SCHOOL_CODE . "/%/" . $year;
            $stmt = $pdo->prepare("SELECT reg_number FROM students WHERE enrolment_year = :year AND reg_number LIKE :pattern FOR UPDATE");
            $stmt->execute([
                'year' => $year,
                'pattern' => $pattern
            ]);
            $reg_numbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $max_seq = 0;
            foreach ($reg_numbers as $reg) {
                $parts = explode('/', $reg);
                if (count($parts) === 3) {
                    $seq = (int)$parts[1];
                    if ($seq > $max_seq) {
                        $max_seq = $seq;
                    }
                }
            }

            $next_seq = $max_seq + 1;
            $stno = sprintf('%04d', $next_seq);
            $reg_number = "S" . SCHOOL_CODE . "/" . $stno . "/" . $year;

            // Double check uniqueness (safeguard)
            $chk = $pdo->prepare("SELECT COUNT(*) FROM students WHERE reg_number = :reg");
            $chk->execute(['reg' => $reg_number]);
            if ($chk->fetchColumn() > 0) {
                // If collision occurs (e.g. index mismatch), we try to increment once more
                $next_seq++;
                $stno = sprintf('%04d', $next_seq);
                $reg_number = "S" . SCHOOL_CODE . "/" . $stno . "/" . $year;
            }

            // Insert new record
            $ins = $pdo->prepare("
                INSERT INTO students 
                    (reg_number, full_name, date_of_birth, gender, class_grade, enrolment_year, registered_by)
                VALUES 
                    (:reg_number, :full_name, :date_of_birth, :gender, :class_grade, :enrolment_year, :registered_by)
            ");
            $ins->execute([
                'reg_number' => $reg_number,
                'full_name' => $full_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'class_grade' => $class_grade,
                'enrolment_year' => $year,
                'registered_by' => $_SESSION['user_id']
            ]);

            $pdo->commit();
            $success = 'Student <strong>' . htmlspecialchars($full_name) . '</strong> registered successfully with auto-generated Reg No: <strong>' . htmlspecialchars($reg_number) . '</strong>.';
            $form = []; // Clear form on success
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Database registration failed: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

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

<div class="glass-card" style="padding: 36px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid var(--border-color);">
        <div class="stats-icon primary" style="margin-bottom: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <div>
            <h2 style="font-size: 1.2rem; font-weight: 600;">Register New Student</h2>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Fill in the details below. The registration number will be auto-generated by the system upon submission.</p>
        </div>
    </div>

    <form method="POST" action="register_student.php" autocomplete="off" novalidate>
        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Personal Information</h3>
        
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. Amina Juma" value="<?php echo fv('full_name', $form); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="date_of_birth">Date of Birth *</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?php echo fv('date_of_birth', $form); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="gender">Gender *</label>
                <select id="gender" name="gender" class="form-input" required>
                    <option value="">— Select Gender —</option>
                    <option value="Male" <?php echo (fv('gender',$form)==='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo (fv('gender',$form)==='Female')?'selected':''; ?>>Female</option>
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
                        <option value="Standard 1" <?php echo (fv('class_grade',$form)==='Standard 1')?'selected':''; ?>>Standard 1</option>
                        <option value="Standard 2" <?php echo (fv('class_grade',$form)==='Standard 2')?'selected':''; ?>>Standard 2</option>
                        <option value="Standard 3" <?php echo (fv('class_grade',$form)==='Standard 3')?'selected':''; ?>>Standard 3</option>
                        <option value="Standard 4" <?php echo (fv('class_grade',$form)==='Standard 4')?'selected':''; ?>>Standard 4</option>
                        <option value="Standard 5" <?php echo (fv('class_grade',$form)==='Standard 5')?'selected':''; ?>>Standard 5</option>
                        <option value="Standard 6" <?php echo (fv('class_grade',$form)==='Standard 6')?'selected':''; ?>>Standard 6</option>
                        <option value="Standard 7" <?php echo (fv('class_grade',$form)==='Standard 7')?'selected':''; ?>>Standard 7</option>
                    </optgroup>
                    <optgroup label="Secondary School">
                        <option value="Form 1" <?php echo (fv('class_grade',$form)==='Form 1')?'selected':''; ?>>Form 1</option>
                        <option value="Form 2" <?php echo (fv('class_grade',$form)==='Form 2')?'selected':''; ?>>Form 2</option>
                        <option value="Form 3" <?php echo (fv('class_grade',$form)==='Form 3')?'selected':''; ?>>Form 3</option>
                        <option value="Form 4" <?php echo (fv('class_grade',$form)==='Form 4')?'selected':''; ?>>Form 4</option>
                        <option value="Form 5" <?php echo (fv('class_grade',$form)==='Form 5')?'selected':''; ?>>Form 5</option>
                        <option value="Form 6" <?php echo (fv('class_grade',$form)==='Form 6')?'selected':''; ?>>Form 6</option>
                    </optgroup>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="enrolment_year">Year of Enrolment *</label>
                <select id="enrolment_year" name="enrolment_year" class="form-input" required>
                    <?php foreach ($years as $yr): ?>
                        <option value="<?php echo $yr; ?>" <?php echo ($yr === $current_year && empty($form)) || (int)fv('enrolment_year',$form) === $yr ? 'selected' : ''; ?>>
                            <?php echo $yr; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: flex-end;">
            <a href="students.php" class="btn btn-secondary">Cancel</a>
            <button type="reset" class="btn btn-secondary">Clear Form</button>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Register Student
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
