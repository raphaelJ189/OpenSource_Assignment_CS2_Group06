<?php
// search.php - Search for a student by Registration Number

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$student = null;
$searched = false;
$reg_no = strtoupper(trim($_GET['reg_no'] ?? $_POST['reg_no'] ?? ''));

if (!empty($reg_no)) {
    $searched = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE reg_no = :reg_no");
        $stmt->execute(['reg_no' => $reg_no]);
        $student = $stmt->fetch();
    } catch (PDOException $e) {
        $student = null;
    }
}

function age_from_dob($dob) {
    if (empty($dob)) return '—';
    try {
        $birth = new DateTime($dob);
        $today = new DateTime();
        return $today->diff($birth)->y . ' yrs';
    } catch (Exception $e) {
        return '—';
    }
}
?>

<!-- Search Form -->
<div class="glass-card" style="padding: 32px; margin-bottom: 28px;">
    <form method="GET" action="search.php" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 260px; margin-bottom: 0;">
            <label class="form-label" for="reg_no_input">Search by Registration Number</label>
            <div class="search-box">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="reg_no_input" type="text" name="reg_no" class="form-input" 
                       placeholder="e.g. REG/2026/0001"
                       value="<?php echo htmlspecialchars($reg_no); ?>"
                       style="text-transform: uppercase;" autofocus>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 48px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </button>
        <?php if (!empty($reg_no)): ?>
            <a href="search.php" class="btn btn-secondary" style="height: 48px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Result Card -->
<?php if ($searched): ?>
    <?php if ($student): ?>
        <!-- Student Found: Profile Card -->
        <div class="glass-card animate-fade-in" style="overflow: hidden;">
            <!-- Profile Header Banner -->
            <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); padding: 36px 40px; position: relative; overflow: hidden;">
                <div style="position: absolute; right: -40px; top: -40px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.08);"></div>
                <div style="position: absolute; right: 40px; bottom: -60px; width: 160px; height: 160px; border-radius: 50%; background: rgba(255,255,255,0.06);"></div>
                <div style="position: relative; display: flex; align-items: center; gap: 24px;">
                    <div style="width: 80px; height: 80px; border-radius: 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white; flex-shrink: 0;">
                        <?php echo strtoupper(substr($student['full_name'], 0, 2)); ?>
                    </div>
                    <div style="color: white;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 6px;"><?php echo htmlspecialchars($student['full_name']); ?></h2>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; opacity: 0.9; font-size: 0.9rem;">
                            <span>📋 <?php echo htmlspecialchars($student['reg_no']); ?></span>
                            <span>🏫 <?php echo htmlspecialchars($student['school_level']); ?> School</span>
                            <span>📍 <?php echo htmlspecialchars($student['region']); ?>, Tanzania</span>
                        </div>
                    </div>
                    <div style="margin-left: auto;">
                        <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); padding: 8px 16px; border-radius: 20px; color: white; font-weight: 600; font-size: 0.85rem;">
                            ✓ <?php echo htmlspecialchars($student['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <div style="padding: 36px 40px;">
                <h3 style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Student Information</h3>

                <div class="profile-grid" style="margin-bottom: 36px;">
                    <div class="profile-item">
                        <div class="profile-label">Registration Number</div>
                        <div class="profile-value">
                            <span class="badge badge-primary" style="font-size: 1rem; padding: 6px 14px;"><?php echo htmlspecialchars($student['reg_no']); ?></span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Full Name</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Gender</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['gender']); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Date of Birth / Age</div>
                        <div class="profile-value">
                            <?php echo htmlspecialchars($student['date_of_birth']); ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;"> (<?php echo age_from_dob($student['date_of_birth']); ?>)</span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">School Level</div>
                        <div class="profile-value">
                            <span class="badge <?php echo $student['school_level']==='Primary'?'badge-secondary':'badge-primary'; ?>">
                                <?php echo htmlspecialchars($student['school_level']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Grade / Form</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['grade_level']); ?></div>
                    </div>
                    <div class="profile-item" style="grid-column: span 2;">
                        <div class="profile-label">School Name</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['school_name']); ?></div>
                    </div>
                </div>

                <h3 style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Location</h3>
                <div class="profile-grid" style="margin-bottom: 36px;">
                    <div class="profile-item">
                        <div class="profile-label">Region</div>
                        <div class="profile-value">📍 <?php echo htmlspecialchars($student['region']); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">District</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['district']); ?></div>
                    </div>
                </div>

                <h3 style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Guardian / Parent Details</h3>
                <div class="profile-grid" style="margin-bottom: 36px;">
                    <div class="profile-item">
                        <div class="profile-label">Guardian Name</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['guardian_name']); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">Guardian Phone</div>
                        <div class="profile-value">📞 <?php echo htmlspecialchars($student['guardian_phone']); ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="students.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        All Students
                    </a>
                    <a href="register_student.php" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Register New Student
                    </a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Student Not Found -->
        <div class="glass-card animate-fade-in" style="padding: 64px 40px; text-align: center;">
            <div style="width: 80px; height: 80px; border-radius: 20px; background: hsl(0, 85%, 95%); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="hsl(0, 75%, 55%)" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">Student Not Found</h2>
            <p style="color: var(--text-muted); max-width: 420px; margin: 0 auto 28px; line-height: 1.6;">
                No student record found with registration number
                <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($reg_no); ?></strong>.
                Please check the number and try again, or browse the student directory.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="search.php" class="btn btn-secondary">Try Again</a>
                <a href="students.php" class="btn btn-primary">Browse Directory</a>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Initial state: no search yet -->
    <div class="glass-card" style="padding: 64px 40px; text-align: center;">
        <div style="width: 80px; height: 80px; border-radius: 20px; background: var(--primary-light); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="var(--primary)" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">Search Student Records</h2>
        <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">
            Enter a registration number above in the format <strong>REG/YYYY/XXXX</strong> to instantly retrieve a student's full profile from the system.
        </p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
