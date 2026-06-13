<?php
// index.php - Dashboard main view

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch metrics
try {
    // 1. Total Students
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // 2. Primary Level Students
    $primary_students = $pdo->query("SELECT COUNT(*) FROM students WHERE school_level = 'Primary'")->fetchColumn();

    // 3. Secondary Level Students
    $secondary_students = $pdo->query("SELECT COUNT(*) FROM students WHERE school_level = 'Secondary'")->fetchColumn();

    // 4. Gender Counts
    $male_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Male'")->fetchColumn();
    $female_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Female'")->fetchColumn();

    // 5. Recent 5 registrations
    $recent_students = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll();

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

    <!-- Card 2: Primary Level -->
    <div class="glass-card stats-card">
        <div class="stats-icon secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <div class="stats-label">Primary School Level</div>
        <div class="stats-value"><?php echo number_format($primary_students); ?></div>
    </div>

    <!-- Card 3: Secondary Level -->
    <div class="glass-card stats-card">
        <div class="stats-icon accent">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
        </div>
        <div class="stats-label">Secondary School Level</div>
        <div class="stats-value"><?php echo number_format($secondary_students); ?></div>
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
                            <th>School Level</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_students as $student): ?>
                            <tr>
                                <td><span class="badge badge-primary"><?php echo htmlspecialchars($student['reg_no']); ?></span></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td>
                                    <span class="badge <?php echo $student['school_level'] === 'Primary' ? 'badge-secondary' : 'badge-primary'; ?>">
                                        <?php echo htmlspecialchars($student['school_level']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($student['grade_level']); ?></td>
                                <td><span class="badge badge-active"><?php echo htmlspecialchars($student['status']); ?></span></td>
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
            <p><strong>System Note:</strong> Ensure all primary school data is verified against the NECTA Standard VII census, and secondary school records align with Form IV registration profiles.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
