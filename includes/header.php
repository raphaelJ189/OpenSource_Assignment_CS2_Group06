<?php
// includes/header.php - Global Header layout with Sidebar

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/auth.php';
check_auth();

$current_user = get_logged_in_user();
$current_page = basename($_SERVER['PHP_SELF']);

// Determine the base path depending on whether the file is in admin/ directory
$is_admin_dir = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base_path = $is_admin_dir ? '../' : './';

// Dynamic SEO metadata
$page_titles = [
    'index.php' => 'Analytics Dashboard | SRMS',
    'register_student.php' => 'Student Registration | SRMS',
    'students.php' => 'Student Directory Records | SRMS',
    'edit_student.php' => 'Edit Student Record | SRMS',
    'delete_student.php' => 'Delete Student Record | SRMS',
    'search.php' => 'Student Search Portal | SRMS',
    'dashboard.php' => 'Teacher Accounts Management | SRMS',
    'create_teacher.php' => 'Create Teacher Account | SRMS',
    'edit_teacher.php' => 'Edit Teacher Account | SRMS',
];
$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Student Record Management System';

$page_descriptions = [
    'index.php' => 'Overview of student records metrics, demographics, and registration statistics.',
    'register_student.php' => 'Register a new student record into the system.',
    'students.php' => 'Browse, search, edit, and manage all student registrations.',
    'edit_student.php' => 'Modify existing student profile information and class assignment.',
    'delete_student.php' => 'Confirm and execute deletion of student records.',
    'search.php' => 'Look up a student profile by their registration number or name.',
    'dashboard.php' => 'Manage institutional teacher accounts, status, and permissions.',
    'create_teacher.php' => 'Create and configure new teacher profiles with secure authentication.',
    'edit_teacher.php' => 'Update teacher profile details, role, and active status.',
];
$page_desc = isset($page_descriptions[$current_page]) ? $page_descriptions[$current_page] : 'Manage student records, demographics, and user access roles.';

// Breadcrumbs generation
$breadcrumbs = [];
$breadcrumbs[] = ['label' => 'Dashboard', 'url' => $base_path . 'index.php', 'icon' => 'fa-gauge-high'];

if ($current_page === 'register_student.php') {
    $breadcrumbs[] = ['label' => 'Register Student', 'active' => true];
} elseif ($current_page === 'students.php') {
    $breadcrumbs[] = ['label' => 'Student Directory', 'active' => true];
} elseif ($current_page === 'edit_student.php') {
    $breadcrumbs[] = ['label' => 'Student Directory', 'url' => $base_path . 'students.php'];
    $breadcrumbs[] = ['label' => 'Edit Student', 'active' => true];
} elseif ($current_page === 'delete_student.php') {
    $breadcrumbs[] = ['label' => 'Student Directory', 'url' => $base_path . 'students.php'];
    $breadcrumbs[] = ['label' => 'Delete Student', 'active' => true];
} elseif ($current_page === 'search.php') {
    $breadcrumbs[] = ['label' => 'Search Student', 'active' => true];
} elseif ($is_admin_dir) {
    if ($current_page === 'dashboard.php') {
        $breadcrumbs[] = ['label' => 'Teacher Accounts', 'active' => true];
    } elseif ($current_page === 'create_teacher.php') {
        $breadcrumbs[] = ['label' => 'Teacher Accounts', 'url' => $base_path . 'admin/dashboard.php'];
        $breadcrumbs[] = ['label' => 'Create Teacher', 'active' => true];
    } elseif ($current_page === 'edit_teacher.php') {
        $breadcrumbs[] = ['label' => 'Teacher Accounts', 'url' => $base_path . 'admin/dashboard.php'];
        $breadcrumbs[] = ['label' => 'Edit Teacher', 'active' => true];
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">
    
    <!-- Font Awesome Free 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <script>
        // Apply saved theme early to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        window.SRMS = window.SRMS || {};
        window.SRMS.basePath = <?php echo json_encode($base_path); ?>;
    </script>
</head>
<body>
    <!-- Global Page Loader -->
    <div id="global-loader" class="page-loader">
        <div class="loader-brand">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>SRMS</span>
        </div>
        <div class="loader-spinner"></div>
    </div>

    <div class="layout-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="sidebar-logo-text">
                    <strong>SRMS</strong>
                    <span>Record Management</span>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-label">Core Modules</span>
                    <ul class="nav-menu">
                        <li class="nav-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                            <a href="<?php echo $base_path; ?>index.php" <?php echo $current_page === 'index.php' ? 'aria-current="page"' : ''; ?>>
                                <i class="fa-solid fa-gauge-high fa-fw"></i>
                                <span class="nav-label">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_page === 'register_student.php' ? 'active' : ''; ?>">
                            <a href="<?php echo $base_path; ?>register_student.php" <?php echo $current_page === 'register_student.php' ? 'aria-current="page"' : ''; ?>>
                                <i class="fa-solid fa-user-plus fa-fw"></i>
                                <span class="nav-label">Register Student</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo ($current_page === 'students.php' || $current_page === 'edit_student.php' || $current_page === 'delete_student.php') ? 'active' : ''; ?>">
                            <a href="<?php echo $base_path; ?>students.php" <?php echo ($current_page === 'students.php' || $current_page === 'edit_student.php' || $current_page === 'delete_student.php') ? 'aria-current="page"' : ''; ?>>
                                <i class="fa-solid fa-users fa-fw"></i>
                                <span class="nav-label">Student Directory</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo $current_page === 'search.php' ? 'active' : ''; ?>">
                            <a href="<?php echo $base_path; ?>search.php" <?php echo $current_page === 'search.php' ? 'aria-current="page"' : ''; ?>>
                                <i class="fa-solid fa-magnifying-glass fa-fw"></i>
                                <span class="nav-label">Search Student</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <?php if ($current_user['role'] === 'admin'): ?>
                    <div class="nav-section">
                        <span class="nav-section-label">Administration</span>
                        <ul class="nav-menu">
                            <li class="nav-item <?php echo $is_admin_dir ? 'active' : ''; ?>">
                                <a href="<?php echo $base_path; ?>admin/dashboard.php" <?php echo $is_admin_dir ? 'aria-current="page"' : ''; ?>>
                                    <i class="fa-solid fa-chalkboard-user fa-fw"></i>
                                    <span class="nav-label">Teacher Accounts</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="user-avatar" id="user-avatar-initials">
                        <?php echo strtoupper(substr($current_user['username'], 0, 2)); ?>
                    </div>
                    <div class="user-info">
                        <span class="user-display-name" title="<?php echo htmlspecialchars($current_user['full_name']); ?>"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                        <span class="user-role-badge"><?php echo htmlspecialchars($current_user['role']); ?></span>
                    </div>
                </div>
                <a href="<?php echo $base_path; ?>logout.php" class="sidebar-logout" aria-label="Sign Out">
                    <i class="fa-solid fa-right-from-bracket fa-fw"></i>
                    <span class="nav-label">Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Sidebar mobile overlay -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Main Content Panel -->
        <main class="main-content" role="main">
            <!-- Top Action Header -->
            <div class="top-header" role="banner">
                <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle navigation sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-title-group">
                    <h1>
                        <?php
                        switch($current_page) {
                            case 'index.php': echo 'Analytics Dashboard'; break;
                            case 'register_student.php': echo 'Student Registration'; break;
                            case 'students.php': echo 'Student Directory Records'; break;
                            case 'edit_student.php': echo 'Edit Student Record'; break;
                            case 'delete_student.php': echo 'Delete Student Record'; break;
                            case 'search.php': echo 'Student Search Portal'; break;
                            case 'dashboard.php': echo 'Teacher Accounts'; break;
                            case 'create_teacher.php': echo 'Create Teacher Account'; break;
                            case 'edit_teacher.php': echo 'Edit Teacher Account'; break;
                            default: echo 'Student Record Management System'; break;
                        }
                        ?>
                    </h1>
                    <p class="header-subtitle">
                        <?php
                        switch($current_page) {
                            case 'index.php': echo 'Overview of school registrations, metrics, and analytics.'; break;
                            case 'register_student.php': echo 'Enter details to register a new student record.'; break;
                            case 'students.php': echo 'View, edit, and manage all student registrations.'; break;
                            case 'edit_student.php': echo 'Update student record information.'; break;
                            case 'delete_student.php': echo 'Confirm student record removal.'; break;
                            case 'search.php': echo 'Search for a student profile by name or registration number.'; break;
                            case 'dashboard.php': echo 'Manage teacher access, registration permissions, and status.'; break;
                            case 'create_teacher.php': echo 'Provision a new teacher account access.'; break;
                            case 'edit_teacher.php': echo 'Update teacher profile details or status.'; break;
                            default: echo 'Welcome to SRMS.'; break;
                        }
                        ?>
                    </p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle-btn" id="theme-toggle-btn" title="Toggle Theme" aria-label="Toggle Dark/Light Theme">
                        <i class="fa-solid fa-moon" id="theme-moon-icon"></i>
                        <i class="fa-solid fa-sun" id="theme-sun-icon" style="display:none;"></i>
                    </button>
                </div>
            </div>

            <!-- Breadcrumbs Navigation -->
            <div class="breadcrumb-bar">
                <nav aria-label="Breadcrumb">
                    <ul class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <li class="breadcrumb-item <?php echo !empty($crumb['active']) ? 'active' : ''; ?>">
                                <?php if (!empty($crumb['active'])): ?>
                                    <?php echo htmlspecialchars($crumb['label']); ?>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($crumb['url']); ?>">
                                        <?php if (!empty($crumb['icon'])): ?>
                                            <i class="fa-solid <?php echo htmlspecialchars($crumb['icon']); ?> fa-fw fa-sm" style="margin-right: 4px;"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($crumb['label']); ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
            
            <div class="content-wrapper">
