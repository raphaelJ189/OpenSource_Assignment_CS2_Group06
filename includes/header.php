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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRMS - Student Record Management System</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <script>
        // Apply saved theme early to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <!-- Global Page Loader -->
    <div id="global-loader" class="global-loader">
        <div class="spinner"></div>
    </div>

    <div class="layout-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div class="logo-text">
                    <span style="font-weight: 700;">SRMS</span>
                    <span style="display: block; font-size: 0.7rem; font-weight: 500; color: var(--text-muted);">Record Management</span>
                </div>
            </div>

            <nav>
                <ul class="sidebar-menu">
                    <li class="menu-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_path; ?>index.php">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page === 'register_student.php' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_path; ?>register_student.php">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>Register Student</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo ($current_page === 'students.php' || $current_page === 'edit_student.php' || $current_page === 'delete_student.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $base_path; ?>students.php">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span>Student Directory</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page === 'search.php' ? 'active' : ''; ?>">
                        <a href="<?php echo $base_path; ?>search.php">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Search Student</span>
                        </a>
                    </li>
                    
                    <?php if ($current_user['role'] === 'admin'): ?>
                        <li class="menu-item <?php echo $is_admin_dir ? 'active' : ''; ?>">
                            <a href="<?php echo $base_path; ?>admin/dashboard.php">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Teacher Accounts</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($current_user['username'], 0, 2)); ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name" title="<?php echo htmlspecialchars($current_user['full_name']); ?>"><?php echo htmlspecialchars($current_user['username']); ?></span>
                        <span class="user-role" style="text-transform: capitalize;"><?php echo htmlspecialchars($current_user['role']); ?></span>
                    </div>
                </div>
                <a href="<?php echo $base_path; ?>logout.php" class="btn btn-secondary" style="width: 100%; font-size: 0.85rem; padding: 8px 12px; gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Panel -->
        <main class="main-content">
            <!-- Top Action Header -->
            <div class="top-header">
                <div class="page-title">
                    <h1>
                        <?php
                        switch($current_page) {
                            case 'index.php': echo 'Analytics Dashboard'; break;
                            case 'register_student.php': echo 'Student Registration'; break;
                            case 'students.php': echo 'Student Directory Records'; break;
                            case 'edit_student.php': echo 'Edit Student Record'; break;
                            case 'delete_student.php': echo 'Delete Student Record'; break;
                            case 'search.php': echo 'Student Search'; break;
                            case 'dashboard.php': echo 'Teacher Accounts'; break;
                            case 'create_teacher.php': echo 'Create Teacher Account'; break;
                            case 'edit_teacher.php': echo 'Edit Teacher Account'; break;
                            default: echo 'Student Record Management System'; break;
                        }
                        ?>
                    </h1>
                    <p>
                        <?php
                        switch($current_page) {
                            case 'index.php': echo 'Overview of school registrations, metrics, and analytics.'; break;
                            case 'register_student.php': echo 'Enter details to register a new student record.'; break;
                            case 'students.php': echo 'View, edit, and manage all student registrations.'; break;
                            case 'edit_student.php': echo 'Update student record information.'; break;
                            case 'delete_student.php': echo 'Confirm student record removal.'; break;
                            case 'search.php': echo 'Search for a student details profile by registration number.'; break;
                            case 'dashboard.php': echo 'Manage teacher access and status.'; break;
                            case 'create_teacher.php': echo 'Provision a new teacher account access.'; break;
                            case 'edit_teacher.php': echo 'Update teacher profile details or status.'; break;
                            default: echo 'Welcome to SRMS.'; break;
                        }
                        ?>
                    </p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle-btn" title="Toggle Theme">
                        <svg id="theme-sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m2.828 9.9a5 5 0 117.072 0l-.707-.707M6.343 6.343l-.707-.707m12.728 12.728l-.707-.707" />
                        </svg>
                        <svg id="theme-moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="animate-fade-in">
