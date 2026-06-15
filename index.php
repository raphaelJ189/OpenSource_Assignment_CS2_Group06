<?php
// index.php - Dashboard main view

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch metrics
try {
    // 1. Total Students
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // 2. Total Teachers (Admin Only) or My Registrations (Teacher)
    $total_teachers = 0;
    $my_registrations = 0;
    if ($current_user['role'] === 'admin') {
        $total_teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
    } else {
        $stmt_my_regs = $pdo->prepare("SELECT COUNT(*) FROM students WHERE registered_by = :user_id");
        $stmt_my_regs->execute(['user_id' => $current_user['id']]);
        $my_registrations = $stmt_my_regs->fetchColumn();
    }

    // 3. Registered This Year
    $current_year = date('Y');
    $stmt_year = $pdo->prepare("SELECT COUNT(*) FROM students WHERE enrolment_year = :year");
    $stmt_year->execute(['year' => $current_year]);
    $students_this_year = $stmt_year->fetchColumn();

    // 4. Gender Counts
    $male_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Male'")->fetchColumn();
    $female_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Female'")->fetchColumn();

    // 5. Recent 5 registrations
    $recent_students = $pdo->query("SELECT * FROM students ORDER BY student_id DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    die("Error fetching dashboard statistics: " . database_error_message($e));
}
?>

<div class="stats-grid animate-fade-in-up">
    <!-- Card 1: Total Students -->
    <div class="stat-card primary">
        <div class="stat-top">
            <div class="stat-label">Total Registered Students</div>
            <div class="stat-icon primary">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($total_students); ?></div>
    </div>

    <!-- Card 2: Role-based Stats -->
    <?php if ($current_user['role'] === 'admin'): ?>
        <div class="stat-card secondary">
            <div class="stat-top">
                <div class="stat-label">Active Teachers</div>
                <div class="stat-icon secondary">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($total_teachers); ?></div>
        </div>
    <?php else: ?>
        <div class="stat-card secondary">
            <div class="stat-top">
                <div class="stat-label">My Registrations</div>
                <div class="stat-icon secondary">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($my_registrations); ?></div>
        </div>
    <?php endif; ?>

    <!-- Card 3: Registered This Year -->
    <div class="stat-card warning">
        <div class="stat-top">
            <div class="stat-label">Enrolled in <?php echo $current_year; ?></div>
            <div class="stat-icon warning">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($students_this_year); ?></div>
    </div>

    <!-- Card 4: Gender Ratio -->
    <div class="stat-card success">
        <div class="stat-top">
            <div class="stat-label">Gender Ratio (M/F)</div>
            <div class="stat-icon success">
                <i class="fa-solid fa-venus-mars"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($male_students) . ' / ' . number_format($female_students); ?></div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="quick-actions animate-fade-in-up" style="animation-delay: 100ms;">
    <a href="register_student.php" class="quick-action-card" id="qa-register">
        <i class="fa-solid fa-user-plus"></i>
        <span>Register Student</span>
    </a>
    <a href="students.php" class="quick-action-card" id="qa-directory">
        <i class="fa-solid fa-users"></i>
        <span>Student Directory</span>
    </a>
    <a href="search.php" class="quick-action-card" id="qa-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Search Student</span>
    </a>
    <?php if ($current_user['role'] === 'admin'): ?>
        <a href="admin/dashboard.php" class="quick-action-card" id="qa-teachers">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Teacher Accounts</span>
        </a>
    <?php endif; ?>
</div>

<div class="dashboard-grid animate-fade-in-up" style="animation-delay: 200ms;">
    <!-- Recent Registrations Card -->
    <div class="glass-card">
        <div class="card-header">
            <div class="card-header-inner">
                <div class="card-icon primary">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="card-title">Recent Registrations</h3>
                    <p class="card-subtitle">The last 5 student records registered</p>
                </div>
            </div>
            <a href="students.php" class="btn btn-secondary btn-sm" id="btn-view-all-recent">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recent_students)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <h4 class="empty-state-title">No student records found</h4>
                    <p class="empty-state-desc">No student records have been added to the system yet.</p>
                    <a href="register_student.php" class="btn btn-primary" style="margin-top: 12px;">Register First Student</a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <caption>Recent registrations list</caption>
                        <thead>
                            <tr>
                                <th scope="col">Reg No</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Class/Grade</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Enrolment Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_students as $student): ?>
                                <tr>
                                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($student['reg_number']); ?></span></td>
                                    <td><div style="font-weight: 600;"><?php echo htmlspecialchars($student['full_name']); ?></div></td>
                                    <td><?php echo htmlspecialchars($student['class_grade']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $student['gender'] === 'Male' ? 'badge-secondary' : 'badge-primary'; ?>">
                                            <?php echo htmlspecialchars($student['gender']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['enrolment_year']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gender Breakdown Panel -->
    <div class="glass-card" style="display: flex; flex-direction: column;">
        <div class="card-header">
            <div class="card-header-inner">
                <div class="card-icon secondary">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <h3 class="card-title">Demographics</h3>
                    <p class="card-subtitle">Gender distribution metrics</p>
                </div>
            </div>
        </div>
        <div class="card-body" style="display: flex; flex-direction: column; gap: var(--sp-5); min-width: 0; overflow: hidden;">
            <!-- Male Stats -->
            <div class="progress-group">
                <div class="progress-header">
                    <span class="progress-label">Male Students</span>
                    <span class="progress-value">
                        <?php 
                        $male_pct = $total_students > 0 ? ($male_students / $total_students) * 100 : 0;
                        echo number_format($male_students) . " (" . number_format($male_pct, 1) . "%)"; 
                        ?>
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill primary" style="width: <?php echo number_format($male_pct, 2, '.', ''); ?>%;"></div>
                </div>
            </div>

            <!-- Female Stats -->
            <div class="progress-group">
                <div class="progress-header">
                    <span class="progress-label">Female Students</span>
                    <span class="progress-value">
                        <?php 
                        $female_pct = $total_students > 0 ? ($female_students / $total_students) * 100 : 0;
                        echo number_format($female_students) . " (" . number_format($female_pct, 1) . "%)"; 
                        ?>
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill secondary" style="width: <?php echo number_format($female_pct, 2, '.', ''); ?>%;"></div>
                </div>
            </div>

            <!-- Total summary -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--sp-3) var(--sp-4); background: var(--color-primary-subtle); border-radius: var(--radius-md);">
                <span style="font-size: var(--font-size-sm); font-weight: 500; color: var(--text-secondary);">Total Students</span>
                <span style="font-size: var(--font-size-md); font-weight: 800; color: var(--color-primary);"><?php echo number_format($total_students); ?></span>
            </div>

            <div class="system-note">
                <i class="fa-solid fa-circle-info"></i>
                <span><strong>System Note:</strong> Ensure all registration formats conform to standard format: <code>S{SCHOOL_CODE}/{STNO}/{YEAR}</code>.</span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
