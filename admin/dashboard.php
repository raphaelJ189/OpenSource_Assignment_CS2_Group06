<?php
// admin/dashboard.php - Teacher Accounts Management (Admin Only)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

// Restricted to admins only
require_role('admin');

require_once __DIR__ . '/../includes/header.php';

$teachers = [];
try {
    // Fetch all users with role 'teacher'
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY user_id DESC");
    $teachers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database query error: " . database_error_message($e));
}
?>

<div class="controls-bar animate-fade-in-up" style="margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Teacher Accounts Directory</h2>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Manage login credentials and activation status of teacher accounts.</p>
    </div>
    <a href="create_teacher.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-user-plus"></i>
        <span>Provision Teacher</span>
    </a>
</div>

<!-- Teacher Directory Table -->
<div class="glass-card animate-fade-in-up" style="overflow: hidden; animation-delay: 100ms;">
    <?php if (empty($teachers)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <h3 class="empty-state-title">No teachers registered</h3>
            <p class="empty-state-desc">No teacher accounts have been created yet. Click "Provision Teacher" to set up a new account.</p>
            <a href="create_teacher.php" class="btn btn-primary" style="margin-top: var(--sp-4);">Add First Teacher</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <caption>Directory list of all registered teachers</caption>
                <thead>
                    <tr>
                        <th scope="col" style="width: 60px;">#</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Username</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created Date</th>
                        <th scope="col" style="text-align: right; width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($teachers as $t): ?>
                        <tr>
                            <td class="row-num"><?php echo $i++; ?></td>
                            <td><div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($t['full_name']); ?></div></td>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($t['username']); ?></span></td>
                            <td style="text-transform: capitalize;"><?php echo htmlspecialchars($t['role']); ?></td>
                            <td>
                                <?php if ((int)$t['is_active'] === 1): ?>
                                    <span class="badge badge-success">
                                        <i class="fa-solid fa-circle-check"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">
                                        <i class="fa-solid fa-circle-xmark"></i> Deactivated
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($t['created_at']))); ?></td>
                            <td style="text-align: right;">
                                <div class="table-actions">
                                    <a href="edit_teacher.php?user_id=<?php echo urlencode($t['user_id']); ?>" class="btn btn-secondary btn-sm" title="Manage teacher profile and status">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span class="btn-text">Manage</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
