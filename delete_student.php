<?php
// delete_student.php - Delete Student Record (Admin Only)

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

// BR-06: Restricted to admins only
require_role('admin');

require_once __DIR__ . '/includes/header.php';

$student_id = $_GET['student_id'] ?? '';
$error = '';
$student = null;

if (empty($student_id)) {
    header("Location: students.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :id");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: students.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $del = $pdo->prepare("DELETE FROM students WHERE student_id = :id");
        $del->execute(['id' => $student_id]);
        
        // Redirect to students page
        header("Location: students.php");
        exit();
    } catch (PDOException $e) {
        $error = 'Failed to delete student record: ' . $e->getMessage();
    }
}
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="margin-bottom: 24px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<div class="glass-card" style="padding: 36px; max-width: 600px; margin: 40px auto;">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="stats-icon accent" style="margin: 0 auto 16px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary);">Confirm Deletion</h2>
        <p style="color: var(--text-muted); margin-top: 8px;">Are you sure you want to permanently delete this student record? This action cannot be undone.</p>
    </div>

    <div style="background-color: var(--primary-light); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted); font-weight: 500; width: 40%;">Registration Number:</td>
                <td style="padding: 6px 0; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($student['reg_number']); ?></td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted); font-weight: 500;">Full Name:</td>
                <td style="padding: 6px 0; font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($student['full_name']); ?></td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted); font-weight: 500;">Class / Grade:</td>
                <td style="padding: 6px 0; color: var(--text-primary);"><?php echo htmlspecialchars($student['class_grade']); ?></td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted); font-weight: 500;">Gender:</td>
                <td style="padding: 6px 0; color: var(--text-primary);"><?php echo htmlspecialchars($student['gender']); ?></td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted); font-weight: 500;">Enrolment Year:</td>
                <td style="padding: 6px 0; color: var(--text-primary);"><?php echo htmlspecialchars($student['enrolment_year']); ?></td>
            </tr>
        </table>
    </div>

    <form method="POST" action="delete_student.php?student_id=<?php echo urlencode($student_id); ?>">
        <div style="display: flex; gap: 16px; justify-content: center;">
            <a href="students.php" class="btn btn-secondary" style="flex: 1; text-align: center; justify-content: center;">Cancel</a>
            <button type="submit" class="btn btn-primary" style="flex: 1; background: var(--accent); border-color: var(--accent); justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Yes, Delete Record
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
