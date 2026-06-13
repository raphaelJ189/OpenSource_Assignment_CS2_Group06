<?php
// students.php - Student Directory listing with filters and pagination

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

// Filters & Pagination
$search       = trim($_GET['search'] ?? '');
$level_filter = $_GET['level'] ?? '';
$region_filter= $_GET['region'] ?? '';
$page         = max(1, (int)($_GET['page'] ?? 1));
$per_page     = 10;
$offset       = ($page - 1) * $per_page;

// Build WHERE clause
$where_parts = [];
$params = [];

if (!empty($search)) {
    $where_parts[] = "(full_name LIKE :search OR reg_no LIKE :search OR school_name LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
if (!empty($level_filter)) {
    $where_parts[] = "school_level = :level";
    $params['level'] = $level_filter;
}
if (!empty($region_filter)) {
    $where_parts[] = "region = :region";
    $params['region'] = $region_filter;
}

$where_sql = !empty($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// Total count
$count_sql = "SELECT COUNT(*) FROM students $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));

// Data query
$data_sql = "SELECT * FROM students $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$data_stmt = $pdo->prepare($data_sql);
foreach ($params as $k => $v) { $data_stmt->bindValue(':' . $k, $v); }
$data_stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$data_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$data_stmt->execute();
$students = $data_stmt->fetchAll();

// Get distinct regions for the filter dropdown
$regions = $pdo->query("SELECT DISTINCT region FROM students ORDER BY region ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Controls Bar -->
<div class="controls-bar">
    <form method="GET" action="students.php" style="display: flex; gap: 12px; flex: 1; flex-wrap: wrap;">
        <div class="search-box" style="min-width: 260px;">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="search-input" type="text" name="search" class="form-input" placeholder="Search by name, reg no, or school..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-group">
            <select name="level" class="filter-select">
                <option value="">All Levels</option>
                <option value="Primary" <?php echo $level_filter==='Primary'?'selected':''; ?>>Primary</option>
                <option value="Secondary" <?php echo $level_filter==='Secondary'?'selected':''; ?>>Secondary</option>
            </select>
            <?php if (!empty($regions)): ?>
            <select name="region" class="filter-select">
                <option value="">All Regions</option>
                <?php foreach ($regions as $reg): ?>
                    <option value="<?php echo htmlspecialchars($reg); ?>" <?php echo $region_filter===$reg?'selected':''; ?>>
                        <?php echo htmlspecialchars($reg); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" style="padding: 12px 18px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Filter
            </button>
            <?php if (!empty($search) || !empty($level_filter) || !empty($region_filter)): ?>
                <a href="students.php" class="btn btn-secondary" style="padding: 12px 18px;">Clear</a>
            <?php endif; ?>
        </div>
    </form>
    <a href="register_student.php" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Register New
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
                <?php if (!empty($search) || !empty($level_filter) || !empty($region_filter)): ?>
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
                        <th>Level</th>
                        <th>Grade</th>
                        <th>Gender</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; foreach ($students as $s): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $i++; ?></td>
                            <td>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($s['reg_no']); ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($s['full_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($s['school_name']); ?></div>
                            </td>
                            <td>
                                <span class="badge <?php echo $s['school_level']==='Primary'?'badge-secondary':'badge-primary'; ?>">
                                    <?php echo htmlspecialchars($s['school_level']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($s['grade_level']); ?></td>
                            <td><?php echo htmlspecialchars($s['gender']); ?></td>
                            <td>
                                <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($s['region']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($s['district']); ?></div>
                            </td>
                            <td><span class="badge badge-active"><?php echo htmlspecialchars($s['status']); ?></span></td>
                            <td>
                                <a href="search.php?reg_no=<?php echo urlencode($s['reg_no']); ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;" title="View full profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
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
