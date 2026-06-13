<?php
// students.php - Student Directory listing with filters and pagination

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

// Filters & Pagination
$search        = trim($_GET['search'] ?? '');
$grade_filter  = $_GET['grade'] ?? '';
$gender_filter = $_GET['gender'] ?? '';
$year_filter   = $_GET['year'] ?? '';
$page          = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 10;
$offset        = ($page - 1) * $per_page;

// Build WHERE clause
$where_parts = [];
$params = [];

if (!empty($search)) {
    $where_parts[] = "(full_name LIKE :search1 OR reg_number LIKE :search2)";
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}
if (!empty($grade_filter)) {
    $where_parts[] = "class_grade = :grade";
    $params['grade'] = $grade_filter;
}
if (!empty($gender_filter)) {
    $where_parts[] = "gender = :gender";
    $params['gender'] = $gender_filter;
}
if (!empty($year_filter)) {
    $where_parts[] = "enrolment_year = :year";
    $params['year'] = $year_filter;
}

$where_sql = !empty($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// Total count
try {
    $count_sql = "SELECT COUNT(*) FROM students $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();
    $total_pages = max(1, (int)ceil($total / $per_page));

    // Data query
    $data_sql = "SELECT * FROM students $where_sql ORDER BY student_id DESC LIMIT :limit OFFSET :offset";
    $data_stmt = $pdo->prepare($data_sql);
    foreach ($params as $k => $v) { 
        $data_stmt->bindValue(':' . $k, $v); 
    }
    $data_stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $data_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $data_stmt->execute();
    $students = $data_stmt->fetchAll();

    // Get distinct values for filter dropdowns
    $grades = $pdo->query("SELECT DISTINCT class_grade FROM students ORDER BY class_grade ASC")->fetchAll(PDO::FETCH_COLUMN);
    $years = $pdo->query("SELECT DISTINCT enrolment_year FROM students ORDER BY enrolment_year DESC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}
?>

<!-- Controls Bar -->
<div class="controls-bar">
    <form method="GET" action="students.php" style="display: flex; gap: 12px; flex: 1; flex-wrap: wrap;">
        <div class="search-box" style="min-width: 260px;">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="search-input" type="text" name="search" class="form-input" placeholder="Search name or reg number..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filter-group">
            <?php if (!empty($grades)): ?>
            <select name="grade" class="filter-select">
                <option value="">All Grades</option>
                <?php foreach ($grades as $g): ?>
                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $grade_filter===$g?'selected':''; ?>>
                        <?php echo htmlspecialchars($g); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select name="gender" class="filter-select">
                <option value="">All Genders</option>
                <option value="Male" <?php echo $gender_filter==='Male'?'selected':''; ?>>Male</option>
                <option value="Female" <?php echo $gender_filter==='Female'?'selected':''; ?>>Female</option>
            </select>

            <?php if (!empty($years)): ?>
            <select name="year" class="filter-select">
                <option value="">All Years</option>
                <?php foreach ($years as $yr): ?>
                    <option value="<?php echo htmlspecialchars($yr); ?>" <?php echo $year_filter===(string)$yr?'selected':''; ?>>
                        <?php echo htmlspecialchars($yr); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="padding: 12px 18px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Filter
            </button>
            <?php if (!empty($search) || !empty($grade_filter) || !empty($gender_filter) || !empty($year_filter)): ?>
                <a href="students.php" class="btn btn-secondary" style="padding: 12px 18px;">Clear</a>
            <?php endif; ?>
        </div>
    </form>
    <a href="register_student.php" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Register Student
    </a>
</div>

<!-- Students Table -->
<div class="glass-card" style="overflow: hidden;">
    <?php if (empty($students)): ?>
        <div style="text-align: center; padding: 64px 40px; color: var(--text-muted);">
            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.4;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 style="font-size: 1.1rem; margin-bottom: 8px; color: var(--text-secondary);">No students found</h3>
            <p style="margin-bottom: 24px;">
                <?php if (!empty($search) || !empty($grade_filter) || !empty($gender_filter) || !empty($year_filter)): ?>
                    No students match your current filter criteria.
                <?php else: ?>
                    No student records have been added yet.
                <?php endif; ?>
            </p>
            <a href="register_student.php" class="btn btn-primary">Register First Student</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reg Number</th>
                        <th>Full Name</th>
                        <th>Class/Grade</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Enrolment Year</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; foreach ($students as $s): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $i++; ?></td>
                            <td>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($s['reg_number']); ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($s['full_name']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($s['class_grade']); ?></td>
                            <td>
                                <span class="badge <?php echo $s['gender']==='Male'?'badge-secondary':'badge-primary'; ?>">
                                    <?php echo htmlspecialchars($s['gender']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($s['date_of_birth']))); ?></td>
                            <td><?php echo htmlspecialchars($s['enrolment_year']); ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                                    <a href="search.php?reg_number=<?php echo urlencode($s['reg_number']); ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;" title="View Profile">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    
                                    <a href="edit_student.php?student_id=<?php echo urlencode($s['student_id']); ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;" title="Edit student details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </a>

                                    <?php if ($current_user['role'] === 'admin'): ?>
                                        <a href="delete_student.php?student_id=<?php echo urlencode($s['student_id']); ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; color: var(--accent);" title="Delete Student Record">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" style="padding: 20px 24px; border-top: 1px solid var(--border-color);">
            <div class="pagination-info">
                Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> students
            </div>
            <div class="pagination-buttons">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-secondary" style="padding: 8px 14px;">
                        ← Prev
                    </a>
                <?php endif; ?>
                <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"
                       class="btn <?php echo $p === $page ? 'btn-primary' : 'btn-secondary'; ?>"
                       style="padding: 8px 14px; min-width: 40px;">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-secondary" style="padding: 8px 14px;">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
