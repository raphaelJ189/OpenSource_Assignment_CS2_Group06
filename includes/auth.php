<?php
// includes/auth.php - Session validation and authentication helper

if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $https ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

if (!defined('SRMS_SESSION_WARNING_SECONDS')) {
    define('SRMS_SESSION_WARNING_SECONDS', 15 * 60);
}

if (!defined('SRMS_SESSION_TIMEOUT_SECONDS')) {
    define('SRMS_SESSION_TIMEOUT_SECONDS', 20 * 60);
}

function update_session_activity() {
    $_SESSION['last_activity'] = time();
}

function redirect_to_login_with_error($message) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['login_error'] = $message;
    header("Location: login.php");
    exit();
}

/**
 * Checks if user is authenticated and active, otherwise redirects to login page.
 */
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_SESSION['last_activity'])) {
        update_session_activity();
    } elseif ((time() - (int) $_SESSION['last_activity']) >= SRMS_SESSION_TIMEOUT_SECONDS) {
        redirect_to_login_with_error('Your session has expired. Please sign in again.');
    }

    // BR-05: Verify the account is still active
    global $pdo;
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || (int)$user['is_active'] !== 1) {
                // Deactivated or deleted while logged in. Log them out.
                redirect_to_login_with_error('Your account has been deactivated or does not exist.');
            }
        } catch (PDOException $e) {
            // If DB check fails, let the request continue but don't crash
        }
    }

    update_session_activity();
}

/**
 * Checks if a user is logged in.
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Requires a specific role. If the user doesn't have it, redirect them to the home page.
 * @param string $role ('admin' or 'teacher')
 */
function require_role($role) {
    check_auth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: index.php");
        exit();
    }
}

/**
 * Returns current logged-in user details.
 * @return array|null
 */
function get_logged_in_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'full_name' => $_SESSION['full_name'] ?? ''
        ];
    }
    return null;
}
