## Project Title
### Student Record Management System (SRMS)

## Degree Program
Computer Science (Bsc-CS2)

## Group Number
Group 06

## Project Description
SRMS is a PHP 8+ and MySQL student record management system for primary and secondary schools. It supports admin and teacher logins, teacher account management, student registration, search, editing, deletion, and dashboard summaries. This project is a PHP-based Student Information Management System.

The system is designed to manage student records in primary and secondary schools in Tanzania. It allows users (administrators/teachers) to:
- Register new students into the system  
- Display all registered student records  
- Search for a student using a registration number


## Stack

- PHP 8+
- MySQL
- PDO prepared statements
- HTML, CSS, vanilla JavaScript
- Docker deployment support for Render

  ## Project Structure

```
OpenSource_Assignment_CS2_Group06/
├── admin/                      # Admin-specific pages
│   ├── create_teacher.php      # Create new teacher accounts
│   ├── dashboard.php           # Admin dashboard
│   └── edit_teacher.php        # Edit teacher accounts
├── css/                        # Stylesheets
│   └── style.css               # Main stylesheet
├── includes/                   # Shared PHP components
│   ├── auth.php                # Authentication logic
│   ├── footer.php              # Footer template
│   └── header.php              # Header template
├── db.php                      # Database connection configuration
├── delete_student.php          # Delete student functionality
├── docker-entrypoint.sh        # Docker startup script
├── Dockerfile                  # Docker container configuration
├── edit_student.php            # Edit student records
├── index.php                   # Home/dashboard page
├── keep-alive.php              # Keep-alive script for deployment
├── login.php                   # User login page
├── logout.php                  # User logout functionality
├── Project_Report.md           # Project report documentation
├── README.md                   # This file
├── register_student.php        # Student registration page
├── render.yaml                 # Render deployment configuration
├── search.php                  # Student search functionality
├── setup_db.php                # Database setup and seeding script
└── students.php                # View all students
```

### File Descriptions

| File/Folder | Purpose |
|-------------|---------|
| `admin/` | Contains admin-only pages for managing teachers and system administration |
| `css/` | Contains stylesheet files for styling the application |
| `includes/` | Shared PHP files included across multiple pages (auth, templates) |
| `db.php` | Database connection and configuration |
| `index.php` | Main application entry point and dashboard |
| `login.php` | Authentication page for admin and teacher logins |
| `register_student.php` | Form and logic for registering new students |
| `search.php` | Search functionality for finding students |
| `edit_student.php` | Edit existing student records |
| `delete_student.php` | Delete student records |
| `students.php` | Display list of all students |
| `setup_db.php` | Initialize database tables and seed default admin user |
| `Dockerfile` | Container configuration for Docker deployment |
| `render.yaml` | Configuration for Render.com deployment |


## Local Setup With XAMPP

1. Start Apache and MySQL in XAMPP.
2. Create a MySQL database named `sims_db` in phpMyAdmin.
3. Open a terminal in the project folder.
4. Run the setup script:

```bash
php setup_db.php
```

If `php` is not available in your PATH, use the XAMPP PHP executable, for example:

```bash
C:\xampp\php\php.exe setup_db.php
```

Default login after setup:

```text
username: admin
password: admin123
```

## Environment Variables

The app reads database settings from environment variables in production. Local defaults still work with XAMPP.

```text
APP_ENV=production
APP_DEBUG=false
DB_HOST=your-mysql-host
DB_PORT=3306
DB_NAME=sims_db
DB_USER=your-database-user
DB_PASS=your-database-password
DB_CHARSET=utf8mb4
SCHOOL_CODE=4558
ADMIN_PASSWORD=change-this-before-setup
```

You can also provide a MySQL `DATABASE_URL` instead of separate `DB_*` values.

## Database Setup

`setup_db.php` is safe by default: it creates missing tables and seeds the admin user only if missing.

Run normal setup:

```bash
php setup_db.php
```

Reset all tables and data only when you intentionally want a fresh database:

```bash
php setup_db.php --fresh
```

Do not run `--fresh` on production unless you want to delete existing users and students.

## Deploy To Render

Render does not provide MySQL as its built-in managed database, so use an external cloud MySQL provider such as Aiven, Railway MySQL, Clever Cloud MySQL, or another MySQL host.

1. Push this repository to GitHub.
2. Create a cloud MySQL database.
3. In that database, create or select the database name you will use, for example `sims_db`.
4. In Render, create a new Web Service from the GitHub repo.
5. Choose Docker runtime. The included `Dockerfile` installs PHP Apache with `pdo_mysql`.
6. Add these Render environment variables:

```text
APP_ENV=production
APP_DEBUG=false
DB_HOST=your-cloud-mysql-host
DB_PORT=3306
DB_NAME=sims_db
DB_USER=your-cloud-db-user
DB_PASS=your-cloud-db-password
SCHOOL_CODE=4558
ADMIN_PASSWORD=use-a-strong-password
```

7. Deploy the service.
8. The Docker startup script automatically runs `php setup_db.php`, creating missing tables and seeding the first admin user if needed.
9. Visit the Render URL and log in as `admin` using the `ADMIN_PASSWORD` value from the first successful deploy.

## Production Notes

- Keep `APP_DEBUG=false` in production.
- Do not commit real passwords or database credentials.
- The old local SQLite file is ignored and is not used by this MySQL version.
- Direct browser access to setup and deployment files is blocked by `.htaccess`.
