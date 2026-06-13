<?php
// setup_db.php - Database Initialization and Seeding

require_once __DIR__ . '/db.php';

echo "Initializing database tables...\n";

try {
    // 1. Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'users' created or verified successfully.\n";

    // 2. Create students table
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        reg_no TEXT UNIQUE NOT NULL,
        full_name TEXT NOT NULL,
        school_level TEXT NOT NULL,
        school_name TEXT NOT NULL,
        grade_level TEXT NOT NULL,
        gender TEXT NOT NULL,
        date_of_birth TEXT NOT NULL,
        region TEXT NOT NULL,
        district TEXT NOT NULL,
        guardian_name TEXT NOT NULL,
        guardian_phone TEXT NOT NULL,
        status TEXT DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'students' created or verified successfully.\n";

    // 3. Seed default admin user if it does not exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => 'admin']);
    if ($stmt->fetchColumn() == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $insert_stmt->execute([
            'username' => 'admin',
            'password' => $hashed_password,
            'role' => 'Administrator'
        ]);
        echo "Default admin user seeded successfully (username: admin, password: admin123).\n";
    } else {
        echo "Admin user already exists.\n";
    }

    echo "Database setup completed successfully.\n";
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}
