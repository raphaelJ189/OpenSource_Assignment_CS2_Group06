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
    die("Database query error: " . $e->getMessage());
}
?>

<div class="controls-bar" style="margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 1.2rem; font-weight: 600;">Teacher Accounts Directory</h2>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Manage login credentials and activation status of teacher accounts.</p>
    </div>
    <a href="create_teacher.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        Provision Teacher
    </a>
</div>

<!-- Teacher Directory Table -->
<div class="glass-card" style="overflow: hidden;">
    <?php if (empty($teachers)): ?>
        <div style="text-align: center; padding: 64px 40px; color: var(--text-muted);">
            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.4;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 style="font-size: 1.1rem; margin-bottom: 8px; color: var(--text-secondary);">No teachers registered</h3>
            <p style="margin-bottom: 24px;">No teacher accounts have been created yet. Click "Provision Teacher" to set up a new account.</p>
            <a href="create_teacher.php" class="btn btn-primary">Add First Teacher</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($teachers as $t): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $i++; ?></td>
                            <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($t['full_name']); ?></td>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($t['username']); ?></span></td>
                            <td style="text-transform: capitalize;"><?php echo htmlspecialchars($t['role']); ?></td>
                            <td>
                                <?php if ((int)$t['is_active'] === 1): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: var(--border-color); color: var(--text-muted);">Deactivated</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($t['created_at']))); ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                                    <a href="edit_teacher.php?user_id=<?php echo urlencode($t['user_id']); ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;" title="Edit details & status">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Manage
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
