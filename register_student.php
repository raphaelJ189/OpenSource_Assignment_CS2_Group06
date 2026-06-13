<?php
// register_student.php - Student Registration Form

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';
$form = [];

$tz_regions = [
    'Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera',
    'Katavi', 'Kigoma', 'Kilimanjaro', 'Lindi', 'Manyara', 'Mara',
    'Mbeya', 'Morogoro', 'Mtwara', 'Mwanza', 'Njombe', 'Pemba North',
    'Pemba South', 'Pwani', 'Rukwa', 'Ruvuma', 'Shinyanga', 'Simiyu',
    'Singida', 'Songwe', 'Tabora', 'Tanga', 'Unguja North',
    'Unguja South', 'Unguja Urban West', 'Zanzibar'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'reg_no'         => strtoupper(trim($_POST['reg_no'] ?? '')),
        'full_name'      => trim($_POST['full_name'] ?? ''),
        'school_level'   => $_POST['school_level'] ?? '',
        'school_name'    => trim($_POST['school_name'] ?? ''),
        'grade_level'    => $_POST['grade_level'] ?? '',
        'gender'         => $_POST['gender'] ?? '',
        'date_of_birth'  => $_POST['date_of_birth'] ?? '',
        'region'         => $_POST['region'] ?? '',
        'district'       => trim($_POST['district'] ?? ''),
        'guardian_name'  => trim($_POST['guardian_name'] ?? ''),
        'guardian_phone' => trim($_POST['guardian_phone'] ?? ''),
    ];

    // Validate
    $errors = [];
    if (empty($form['reg_no'])) $errors[] = 'Registration number is required.';
    if (!preg_match('/^REG\/\d{4}\/\d{4}$/', $form['reg_no'])) $errors[] = 'Registration number must follow the format REG/YYYY/XXXX (e.g. REG/2026/0001).';
    if (empty($form['full_name'])) $errors[] = 'Full name is required.';
    if (!in_array($form['school_level'], ['Primary', 'Secondary'])) $errors[] = 'Invalid school level.';
    if (empty($form['school_name'])) $errors[] = 'School name is required.';
    if (empty($form['grade_level'])) $errors[] = 'Grade level is required.';
    if (!in_array($form['gender'], ['Male', 'Female'])) $errors[] = 'Invalid gender selected.';
    if (empty($form['date_of_birth'])) $errors[] = 'Date of birth is required.';
    if (!in_array($form['region'], $tz_regions)) $errors[] = 'Please select a valid Tanzanian region.';
    if (empty($form['district'])) $errors[] = 'District is required.';
    if (empty($form['guardian_name'])) $errors[] = "Guardian's name is required.";
    if (empty($form['guardian_phone'])) $errors[] = "Guardian's phone number is required.";

    if (empty($errors)) {
        try {
            // Check for duplicate reg_no
            $chk = $pdo->prepare("SELECT COUNT(*) FROM students WHERE reg_no = :reg_no");
            $chk->execute(['reg_no' => $form['reg_no']]);
            if ($chk->fetchColumn() > 0) {
                $error = 'A student with registration number <strong>' . htmlspecialchars($form['reg_no']) . '</strong> already exists.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO students 
                        (reg_no, full_name, school_level, school_name, grade_level, gender, date_of_birth, region, district, guardian_name, guardian_phone)
                    VALUES
                        (:reg_no, :full_name, :school_level, :school_name, :grade_level, :gender, :date_of_birth, :region, :district, :guardian_name, :guardian_phone)
                ");
                $stmt->execute($form);
                $success = 'Student <strong>' . htmlspecialchars($form['full_name']) . '</strong> registered successfully with Reg No <strong>' . htmlspecialchars($form['reg_no']) . '</strong>.';
                $form = []; // Clear form
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
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
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="margin-bottom: 24px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="padding: 36px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid var(--border-color);">
        <div class="stats-icon primary" style="margin-bottom: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <div>
            <h2 style="font-size: 1.2rem; font-weight: 600;">New Student Record</h2>
            <p style="font-size: 0.9rem; color: var(--text-muted);">All fields marked are required. Registration numbers must follow REG/YYYY/XXXX format.</p>
        </div>
    </div>

    <form method="POST" action="register_student.php" id="registration-form" novalidate>
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8rem;">Student Identification</h3>
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group">
                <label class="form-label" for="reg_no">Registration Number *</label>
                <input type="text" id="reg_no" name="reg_no" class="form-input" placeholder="e.g. REG/2026/0001" value="<?php echo fv('reg_no', $form); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. Amina Juma Mwalimu" value="<?php echo fv('full_name', $form); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="gender">Gender *</label>
                <select id="gender" name="gender" class="form-input">
                    <option value="">— Select Gender —</option>
                    <option value="Male" <?php echo (fv('gender',$form)==='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo (fv('gender',$form)==='Female')?'selected':''; ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="date_of_birth">Date of Birth *</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?php echo fv('date_of_birth', $form); ?>" required>
            </div>
        </div>

        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">School Information</h3>
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group">
                <label class="form-label" for="school_level">School Level *</label>
                <select id="school_level" name="school_level" class="form-input" onchange="updateGrades(this.value)">
                    <option value="">— Select Level —</option>
                    <option value="Primary" <?php echo (fv('school_level',$form)==='Primary')?'selected':''; ?>>Primary School</option>
                    <option value="Secondary" <?php echo (fv('school_level',$form)==='Secondary')?'selected':''; ?>>Secondary School</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="grade_level">Grade / Standard / Form *</label>
                <select id="grade_level" name="grade_level" class="form-input">
                    <option value="">— Select Level First —</option>
                    <?php
                    $primary_grades = ['Standard 1','Standard 2','Standard 3','Standard 4','Standard 5','Standard 6','Standard 7'];
                    $secondary_grades = ['Form 1','Form 2','Form 3','Form 4','Form 5','Form 6'];
                    $saved_grade = fv('grade_level', $form);
                    $saved_level = fv('school_level', $form);
                    $grades = $saved_level === 'Primary' ? $primary_grades : ($saved_level === 'Secondary' ? $secondary_grades : []);
                    foreach ($grades as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo $saved_grade===$g?'selected':''; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" for="school_name">School Name *</label>
                <input type="text" id="school_name" name="school_name" class="form-input" placeholder="e.g. Shule ya Msingi ya Magomeni" value="<?php echo fv('school_name', $form); ?>" required>
            </div>
        </div>

        <h3 style="font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Location & Guardian Details</h3>
        <div class="form-grid" style="margin-bottom: 32px;">
            <div class="form-group">
                <label class="form-label" for="region">Region *</label>
                <select id="region" name="region" class="form-input">
                    <option value="">— Select Region —</option>
                    <?php foreach ($tz_regions as $region): ?>
                        <option value="<?php echo $region; ?>" <?php echo fv('region',$form)===$region?'selected':''; ?>><?php echo $region; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="district">District *</label>
                <input type="text" id="district" name="district" class="form-input" placeholder="e.g. Kinondoni" value="<?php echo fv('district', $form); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="guardian_name">Guardian / Parent Name *</label>
                <input type="text" id="guardian_name" name="guardian_name" class="form-input" placeholder="e.g. Juma Hassan Mwalimu" value="<?php echo fv('guardian_name', $form); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="guardian_phone">Guardian Phone *</label>
                <input type="tel" id="guardian_phone" name="guardian_phone" class="form-input" placeholder="e.g. +255 712 345 678" value="<?php echo fv('guardian_phone', $form); ?>" required>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: flex-end;">
            <a href="students.php" class="btn btn-secondary">Cancel</a>
            <button type="reset" class="btn btn-secondary">Clear Form</button>
            <button type="submit" class="btn btn-primary" id="submit-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Register Student
            </button>
        </div>
    </form>
</div>

<script>
const primaryGrades = ['Standard 1','Standard 2','Standard 3','Standard 4','Standard 5','Standard 6','Standard 7'];
const secondaryGrades = ['Form 1','Form 2','Form 3','Form 4','Form 5','Form 6'];

function updateGrades(level) {
    const sel = document.getElementById('grade_level');
    sel.innerHTML = '<option value="">— Select Grade —</option>';
    const grades = level === 'Primary' ? primaryGrades : (level === 'Secondary' ? secondaryGrades : []);
    grades.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g;
        opt.textContent = g;
        sel.appendChild(opt);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
