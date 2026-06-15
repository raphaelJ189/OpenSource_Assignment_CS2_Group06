<?php
// keep-alive.php - Refresh the authenticated session's last activity timestamp

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

check_auth();
update_session_activity();

header('Content-Type: application/json; charset=utf-8');
http_response_code(204);
exit();