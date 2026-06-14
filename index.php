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
    die("Error fetching dashboard statistics: " . $e->getMessage());
}
?>

<div class="stats-grid">
    <!-- Card 1: Total Students -->
    <div class="glass-card stats-card">
        <div class="stats-icon primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div class="stats-label">Total Registered Students</div>
        <div class="stats-value"><?php echo number_format($total_students); ?></div>
    </div>

    <!-- Card 2: Role-based Stats -->
    <div class="glass-card stats-card">
        <div class="stats-icon secondary">
            <?php if ($current_user['role'] === 'admin'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            <?php endif; ?>
        </div>
        <?php if ($current_user['role'] === 'admin'): ?>
            <div class="stats-label">Active Teachers</div>
            <div class="stats-value"><?php echo number_format($total_teachers); ?></div>
        <?php else: ?>
            <div class="stats-label">My Registrations</div>
            <div class="stats-value"><?php echo number_format($my_registrations); ?></div>
        <?php endif; ?>
    </div>

    <!-- Card 3: Registered This Year -->
    <div class="glass-card stats-card">
        <div class="stats-icon accent">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <div class="stats-label">Enrolled in <?php echo $current_year; ?></div>
        <div class="stats-value"><?php echo number_format($students_this_year); ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 36px;">
    <!-- Recent Registrations Card -->
    <div class="glass-card" style="padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; font-weight: 600;">Recent Registrations</h3>
            <a href="students.php" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">View All</a>
        </div>
        
        <?php if (empty($recent_students)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.5;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p>No student records found. Click "Register Student" to add new records.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Full Name</th>
                            <th>Class/Grade</th>
                            <th>Gender</th>
                            <th>Enrolment Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_students as $student): ?>
                            <tr>
                                <td><span class="badge badge-primary"><?php echo htmlspecialchars($student['reg_number']); ?></span></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($student['full_name']); ?></td>
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

    <!-- Gender Breakdown Panel -->
    <div class="glass-card" style="padding: 28px; display: flex; flex-direction: column;">
        <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 24px;">Demographics</h3>
        
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 24px;">
            <!-- Male Stats -->
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 500;">
                    <span>Male Students</span>
                    <span>
                        <?php 
                        $male_pct = $total_students > 0 ? ($male_students / $total_students) * 100 : 0;
                        echo number_format($male_students) . " (" . number_format($male_pct, 1) . "%)"; 
                        ?>
                    </span>
                </div>
                <div style="width: 100%; height: 10px; background: var(--border-color); border-radius: 5px; overflow: hidden;">
                    <div style="width: <?php echo $male_pct; ?>%; height: 100%; background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 5px;"></div>
                </div>
            </div>

            <!-- Female Stats -->
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 500;">
                    <span>Female Students</span>
                    <span>
                        <?php 
                        $female_pct = $total_students > 0 ? ($female_students / $total_students) * 100 : 0;
                        echo number_format($female_students) . " (" . number_format($female_pct, 1) . "%)"; 
                        ?>
                    </span>
                </div>
                <div style="width: 100%; height: 10px; background: var(--border-color); border-radius: 5px; overflow: hidden;">
                    <div style="width: <?php echo $female_pct; ?>%; height: 100%; background: linear-gradient(90deg, var(--accent) 0%, #ec4899 100%); border-radius: 5px;"></div>
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
            <p><strong>System Note:</strong> Ensure all registration formats conform to the standard configuration format: S + School Number + / + Sequential Number + / + Enrolment Year.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
