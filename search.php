<?php
// search.php - Search for a student by Registration Number

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$student = null;
$searched = false;
$reg_number = strtoupper(trim($_GET['reg_number'] ?? $_POST['reg_number'] ?? ''));

if (!empty($reg_number)) {
    $searched = true;
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name AS registered_by_name, u.username AS registered_by_username
            FROM students s
            LEFT JOIN users u ON s.registered_by = u.user_id
            WHERE s.reg_number = :reg
        ");
        $stmt->execute(['reg' => $reg_number]);
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
            <label class="form-label" for="reg_no_input">Search by Student Registration Number</label>
            <div class="search-box">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="reg_no_input" type="text" name="reg_number" class="form-input" 
                       placeholder="e.g. S4558/0001/2026"
                       value="<?php echo htmlspecialchars($reg_number); ?>"
                       style="text-transform: uppercase;" autofocus>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 48px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </button>
        <?php if (!empty($reg_number)): ?>
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
                            <span>📋 <?php echo htmlspecialchars($student['reg_number']); ?></span>
                            <span>🏫 <?php echo htmlspecialchars($student['class_grade']); ?></span>
                            <span>📆 Enrolled: <?php echo htmlspecialchars($student['enrolment_year']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <div style="padding: 36px 40px;">
                <h3 style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Student Profile Details</h3>

                <div class="profile-grid" style="margin-bottom: 36px;">
                    <div class="profile-item">
                        <div class="profile-label">Registration Number</div>
                        <div class="profile-value">
                            <span class="badge badge-primary" style="font-size: 1rem; padding: 6px 14px;"><?php echo htmlspecialchars($student['reg_number']); ?></span>
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
                            <?php echo htmlspecialchars(date('d M Y', strtotime($student['date_of_birth']))); ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;"> (<?php echo age_from_dob($student['date_of_birth']); ?>)</span>
                        </div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Class / Grade</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['class_grade']); ?></div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Enrolment Year</div>
                        <div class="profile-value"><?php echo htmlspecialchars($student['enrolment_year']); ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Registered By</div>
                        <div class="profile-value">
                            <?php echo htmlspecialchars($student['registered_by_name'] ?? 'System'); ?> 
                            <span style="color: var(--text-muted); font-size: 0.85rem;">(<?php echo htmlspecialchars($student['registered_by_username'] ?? 'admin'); ?>)</span>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Registration Date</div>
                        <div class="profile-value"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($student['created_at']))); ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="students.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        All Students
                    </a>
                    
                    <a href="edit_student.php?student_id=<?php echo urlencode($student['student_id']); ?>" class="btn btn-primary" style="background: var(--secondary); border-color: var(--secondary);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit Student
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
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);">No student found</h2>
            <p style="color: var(--text-muted); max-width: 420px; margin: 0 auto 28px; line-height: 1.6;">
                No student record matches the registration number
                <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($reg_number); ?></strong>.
                Verify format (e.g. S4558/0001/2026) or try browsing the directory.
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
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);">Search Student Records</h2>
        <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">
            Enter a student registration number above (format: <strong>S4558/STNO/YEAR</strong>) to retrieve their full profile details.
        </p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
