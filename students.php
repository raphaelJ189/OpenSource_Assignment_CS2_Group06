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
    die("Database query error: " . database_error_message($e));
}
?>

<!-- Controls Bar -->
<div class="controls-bar animate-fade-in-up" style="animation-delay: 50ms;">
    <form method="GET" action="students.php" class="controls-left">
        <div class="search-box">
            <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input id="search-input" type="text" name="search" class="form-input has-icon-left" placeholder="Search name or reg number..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filter-group">
            <?php if (!empty($grades)): ?>
            <select name="grade" class="filter-select" aria-label="Filter by Grade">
                <option value="">All Grades</option>
                <?php foreach ($grades as $g): ?>
                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $grade_filter===$g?'selected':''; ?>>
                        <?php echo htmlspecialchars($g); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select name="gender" class="filter-select" aria-label="Filter by Gender">
                <option value="">All Genders</option>
                <option value="Male" <?php echo $gender_filter==='Male'?'selected':''; ?>>Male</option>
                <option value="Female" <?php echo $gender_filter==='Female'?'selected':''; ?>>Female</option>
            </select>

            <?php if (!empty($years)): ?>
            <select name="year" class="filter-select" aria-label="Filter by Enrolment Year">
                <option value="">All Years</option>
                <?php foreach ($years as $yr): ?>
                    <option value="<?php echo htmlspecialchars($yr); ?>" <?php echo $year_filter===(string)$yr?'selected':''; ?>>
                        <?php echo htmlspecialchars($yr); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="padding: 10px 16px;">
                <i class="fa-solid fa-filter fa-sm"></i>
                Filter
            </button>
            <?php if (!empty($search) || !empty($grade_filter) || !empty($gender_filter) || !empty($year_filter)): ?>
                <a href="students.php" class="btn btn-secondary" style="padding: 10px 16px;">Clear</a>
            <?php endif; ?>
        </div>
    </form>
    <div class="controls-right">
        <a href="register_student.php" class="btn btn-primary" id="btn-register-top">
            <i class="fa-solid fa-user-plus"></i>
            <span>Register Student</span>
        </a>
    </div>
</div>

<!-- Students Directory Card -->
<div class="glass-card animate-fade-in-up" style="overflow: hidden; animation-delay: 100ms;">
    <?php if (empty($students)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fa-solid fa-users-slash"></i>
            </div>
            <h3 class="empty-state-title">No students found</h3>
            <p class="empty-state-desc">
                <?php if (!empty($search) || !empty($grade_filter) || !empty($gender_filter) || !empty($year_filter)): ?>
                    No student records match your current search/filter criteria.
                <?php else: ?>
                    No student records have been registered in the system yet.
                <?php endif; ?>
            </p>
            <a href="register_student.php" class="btn btn-primary" style="margin-top: var(--sp-4);">Register First Student</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <caption>Students list including registration numbers and grades</caption>
                <thead>
                    <tr>
                        <th scope="col" style="width: 60px;">#</th>
                        <th scope="col">Reg Number</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Class/Grade</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Date of Birth</th>
                        <th scope="col">Enrolment Year</th>
                        <th scope="col" style="text-align: right; width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; foreach ($students as $s): ?>
                        <tr>
                            <td class="row-num"><?php echo $i++; ?></td>
                            <td>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($s['reg_number']); ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($s['full_name']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($s['class_grade']); ?></td>
                            <td>
                                <span class="badge <?php echo $s['gender']==='Male'?'badge-secondary':'badge-primary'; ?>">
                                    <i class="fa-solid <?php echo $s['gender']==='Male'?'fa-mars':'fa-venus'; ?> fa-sm" style="margin-right: 2px;"></i>
                                    <?php echo htmlspecialchars($s['gender']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($s['date_of_birth']))); ?></td>
                            <td><?php echo htmlspecialchars($s['enrolment_year']); ?></td>
                            <td style="text-align: right;">
                                <div class="table-actions">
                                    <a href="search.php?reg_number=<?php echo urlencode($s['reg_number']); ?>" class="btn btn-secondary btn-sm" title="View student profile card" aria-label="View profile of <?php echo htmlspecialchars($s['full_name']); ?>">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="btn-text">View</span>
                                    </a>
                                    
                                    <a href="edit_student.php?student_id=<?php echo urlencode($s['student_id']); ?>" class="btn btn-secondary btn-sm" title="Edit student record details" aria-label="Edit details of <?php echo htmlspecialchars($s['full_name']); ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span class="btn-text">Edit</span>
                                    </a>
 
                                    <?php if ($current_user['role'] === 'admin'): ?>
                                        <a href="delete_student.php?student_id=<?php echo urlencode($s['student_id']); ?>" 
                                           class="btn btn-secondary btn-sm js-confirm-delete" 
                                           style="color: var(--color-danger); border-color: var(--color-danger-border); background: var(--color-danger-subtle);" 
                                           title="Delete student record permanently" 
                                           aria-label="Delete record of <?php echo htmlspecialchars($s['full_name']); ?>"
                                           data-student-id="<?php echo htmlspecialchars($s['student_id']); ?>"
                                           data-student-name="<?php echo htmlspecialchars($s['full_name']); ?>">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span class="btn-text">Delete</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
 
        <!-- Pagination Bar -->
        <div class="pagination-bar">
            <div class="pagination-info">
                Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> students
            </div>
            <nav class="pagination-buttons" aria-label="Pagination Navigation">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn" aria-label="Previous Page">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"
                       class="page-btn <?php echo $p === $page ? 'active' : ''; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn" aria-label="Next Page">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>
