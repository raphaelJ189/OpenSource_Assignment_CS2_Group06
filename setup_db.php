<?php
// setup_db.php - Database Initialization and Seeding for MySQL

require_once __DIR__ . '/db.php';

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

$fresh = in_array('--fresh', $argv, true);
$admin_password = env_value('ADMIN_PASSWORD', 'admin123');

echo "Initializing database tables...\n";

try {
    if ($fresh) {
        // Use --fresh only for first-time setup or intentional resets.
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("DROP TABLE IF EXISTS students");
        $pdo->exec("DROP TABLE IF EXISTS users");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "Existing tables dropped successfully.\n";
    }

    // 1. Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        username VARCHAR(80) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher') NOT NULL,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table 'users' created or verified successfully.\n";

    // 2. Create students table
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        student_id INT AUTO_INCREMENT PRIMARY KEY,
        reg_number VARCHAR(30) UNIQUE NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('Male', 'Female') NOT NULL,
        class_grade VARCHAR(20) NOT NULL,
        enrolment_year YEAR NOT NULL,
        registered_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (registered_by) REFERENCES users(user_id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table 'students' created or verified successfully.\n";

    // 3. Seed default admin user if missing
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => 'admin']);

    if ((int) $stmt->fetchColumn() === 0) {
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $insert_stmt = $pdo->prepare("INSERT INTO users (full_name, username, password_hash, role, is_active) VALUES (:full_name, :username, :password_hash, :role, 1)");
        $insert_stmt->execute([
            'full_name' => 'System Administrator',
            'username' => 'admin',
            'password_hash' => $hashed_password,
            'role' => 'admin'
        ]);
        echo "Default admin user seeded (username: admin).\n";
    } else {
        echo "Admin user already exists.\n";
    }

    echo "Database setup completed successfully.\n";
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}
