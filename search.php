<?php
// search.php - Search for a student by Registration Number

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$student = null;
$searched = false;
$search_query = trim($_GET['reg_number'] ?? $_POST['reg_number'] ?? '');
$students_found = [];

if (!empty($search_query)) {
    $searched = true;
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name AS registered_by_name, u.username AS registered_by_username
            FROM students s
            LEFT JOIN users u ON s.registered_by = u.user_id
            WHERE s.reg_number = :exact_reg 
               OR s.full_name LIKE :like_name
        ");
        $stmt->execute([
            'exact_reg' => $search_query,
            'like_name' => '%' . $search_query . '%'
        ]);
        $students_found = $stmt->fetchAll();
        if (count($students_found) === 1) {
            $student = $students_found[0];
        }
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

<div class="glass-card animate-fade-in-up" style="margin-bottom: 28px;">
    <div class="card-body">
        <form method="GET" action="search.php" class="search-form-row">
            <div class="form-group search-input-group">
                <label class="form-label" for="search-input">Search by Name or Registration Number</label>
                <div class="input-wrapper">
                    <span class="input-icon left"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input id="search-input" type="text" name="reg_number" class="form-input has-icon-left" 
                           placeholder="e.g. S4558/0001/2026 or John Doe"
                           value="<?php echo htmlspecialchars($search_query); ?>" autofocus autocomplete="off">
                </div>
                <div class="form-hint" style="margin-top: 4px;">Press <kbd>Enter</kbd> to search <span class="shortcut-hint">or use shortcut <kbd>Ctrl</kbd>+<kbd>K</kbd> to focus</span>.</div>
            </div>
            <div class="search-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                    Search
                </button>
                <?php if (!empty($search_query)): ?>
                    <a href="search.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Result Card -->
<?php if ($searched): ?>
    <?php if (count($students_found) === 1): ?>
        <?php $student = $students_found[0]; ?>
        <!-- Student Found: Profile Card -->
        <div class="glass-card animate-fade-in" style="overflow: hidden;">
            <!-- Profile Header Banner -->
            <div class="profile-banner">
                <div class="profile-banner-inner">
                    <div class="profile-initials">
                        <?php echo strtoupper(substr($student['full_name'], 0, 2)); ?>
                    </div>
                    <div class="profile-banner-text">
                        <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
                        <div class="profile-banner-meta">
                            <span><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student['reg_number']); ?></span>
                            <span><i class="fa-solid fa-school"></i> <?php echo htmlspecialchars($student['class_grade']); ?></span>
                            <span><i class="fa-solid fa-calendar-check"></i> Enrolled: <?php echo htmlspecialchars($student['enrolment_year']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <div class="profile-details-body">
                <h3 class="profile-section-title">
                    <i class="fa-solid fa-user-tag"></i> Student Profile Details
                </h3>

                <div class="profile-grid">
                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-fingerprint"></i> Registration Number</div>
                        <div class="profile-item-value">
                            <span class="badge badge-primary"><?php echo htmlspecialchars($student['reg_number']); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-id-card"></i> Full Name</div>
                        <div class="profile-item-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-venus-mars"></i> Gender</div>
                        <div class="profile-item-value">
                            <span class="badge <?php echo $student['gender']==='Male'?'badge-secondary':'badge-primary'; ?>">
                                <?php echo htmlspecialchars($student['gender']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-cake-candles"></i> Date of Birth / Age</div>
                        <div class="profile-item-value">
                            <?php echo htmlspecialchars(date('d M Y', strtotime($student['date_of_birth']))); ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;"> (<?php echo age_from_dob($student['date_of_birth']); ?>)</span>
                        </div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-school"></i> Class / Grade</div>
                        <div class="profile-item-value"><?php echo htmlspecialchars($student['class_grade']); ?></div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-calendar"></i> Enrolment Year</div>
                        <div class="profile-item-value"><?php echo htmlspecialchars($student['enrolment_year']); ?></div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-user-check"></i> Registered By</div>
                        <div class="profile-item-value">
                            <?php echo htmlspecialchars($student['registered_by_name'] ?? 'System'); ?> 
                            <span style="color: var(--text-muted); font-size: 0.85rem;">(<?php echo htmlspecialchars($student['registered_by_username'] ?? 'admin'); ?>)</span>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-item-label"><i class="fa-solid fa-clock"></i> Registration Date</div>
                        <div class="profile-item-value"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($student['created_at']))); ?></div>
                    </div>
                </div>

                <div class="form-actions" style="border-top: 1px solid var(--border-default); margin-top: 0; padding-top: var(--sp-6);">
                    <a href="students.php" class="btn btn-secondary">
                        <i class="fa-solid fa-users"></i>
                        <span>All Students</span>
                    </a>
                    
                    <a href="edit_student.php?student_id=<?php echo urlencode($student['student_id']); ?>" class="btn btn-primary" style="background: var(--color-secondary); border-color: var(--color-secondary);">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Edit Student</span>
                    </a>
                </div>
            </div>
        </div>

    <?php elseif (count($students_found) > 1): ?>
        <!-- Multiple Students Found -->
        <div class="glass-card animate-fade-in">
            <div class="card-header">
                <div class="card-header-inner">
                    <div class="card-icon primary">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <div>
                        <h2 class="card-title">Multiple Students Found</h2>
                        <p class="card-subtitle">Multiple records matched your search query. Please select one to view.</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="data-table">
                        <caption>Search results matches</caption>
                        <thead>
                            <tr>
                                <th scope="col">Reg Number</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Class/Grade</th>
                                <th scope="col" style="text-align: right; width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students_found as $s): ?>
                            <tr>
                                <td><span class="badge badge-primary"><?php echo htmlspecialchars($s['reg_number']); ?></span></td>
                                <td><div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($s['full_name']); ?></div></td>
                                <td><?php echo htmlspecialchars($s['class_grade']); ?></td>
                                <td style="text-align: right;">
                                    <a href="search.php?reg_number=<?php echo urlencode($s['reg_number']); ?>" class="btn btn-secondary btn-sm">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="btn-text">View Profile</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Student Not Found -->
        <div class="glass-card animate-fade-in">
            <div class="empty-state">
                <div class="empty-state-icon" style="background: var(--color-danger-subtle); color: var(--color-danger);">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <h2 class="empty-state-title">No student found</h2>
                <p class="empty-state-desc">
                    No student record matches your search for 
                    <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($search_query); ?></strong>.
                    Verify the format or try browsing the directory.
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: var(--sp-4);">
                    <a href="search.php" class="btn btn-secondary">Try Again</a>
                    <a href="students.php" class="btn btn-primary">Browse Directory</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Initial state: no search yet -->
    <div class="glass-card animate-fade-in">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h2 class="empty-state-title">Search Student Records</h2>
            <p class="empty-state-desc">
                Enter a student name or registration number above (e.g. <strong>S4558/STNO/YEAR</strong>) to retrieve their full profile details.
            </p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
