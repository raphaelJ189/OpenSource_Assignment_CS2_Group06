<?php
// db.php - Database connection using PDO and MySQL

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function app_debug_enabled(): bool
{
    $app_env = env_value('APP_ENV', 'local');
    return filter_var(env_value('APP_DEBUG', $app_env === 'local' ? 'true' : 'false'), FILTER_VALIDATE_BOOLEAN);
}

function database_error_message(Throwable $e, string $public_message = 'A database error occurred. Please try again later.'): string
{
    error_log($e->getMessage());
    return app_debug_enabled() ? $e->getMessage() : $public_message;
}

$app_env = env_value('APP_ENV', 'local');
$app_debug = app_debug_enabled();

$charset = env_value('DB_CHARSET', 'utf8mb4');
$database_url = env_value('DATABASE_URL');

if ($database_url) {
    $parts = parse_url($database_url);
    if ($parts === false || !isset($parts['host'], $parts['user'], $parts['path'])) {
        die('Database connection failed: invalid DATABASE_URL.');
    }

    $host = $parts['host'];
    $port = $parts['port'] ?? 3306;
    $db = ltrim($parts['path'], '/');
    $user = rawurldecode($parts['user']);
    $pass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
} else {
    $host = env_value('DB_HOST', '127.0.0.1');
    $port = (int) env_value('DB_PORT', '3306');
    $db = env_value('DB_NAME', 'sims_db');
    $user = env_value('DB_USER', 'root');
    $pass = env_value('DB_PASS', '');
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    $message = database_error_message($e, 'Unable to connect to the database.');
    die('Database connection failed: ' . $message);
}

// School code configuration for registration numbers (S+SCHNO+/STNO/YEAR)
define('SCHOOL_CODE', env_value('SCHOOL_CODE', '4558'));
