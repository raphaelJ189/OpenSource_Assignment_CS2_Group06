# MwalimuHub SIMS – Student Information Management System

## Project Overview

**MwalimuHub SIMS** is a web-based Student Information Management System designed for primary and secondary schools in Tanzania. Built as part of the Open Source Technologies (CP 222) coursework, the system enables school administrators to digitally manage student records, improving data accuracy, accessibility, and operational efficiency.

The system was developed using PHP with a SQLite database backend, following open-source development practices with Git for version control and GitHub for team collaboration and public hosting.

---

## Project Details

| Field          | Detail                        |
|----------------|-------------------------------|
| **Project Title**  | Student Information Management System – Tanzania Case |
| **Degree Program** | Computer Science (Bsc-CS2)    |
| **Group Number**   | Group 06                      |
| **Course**         | Open Source Technologies – CP 222 |
| **Deadline**       | 18th June 2026                |

---

## System Features

The system covers all three mandatory core features for CS students:

### 1. 🎓 Register Students
- Enrolls new students with complete details: Registration Number (REG/YYYY/XXXX), Full Name, School Level (Primary/Secondary), School Name, Grade/Form (Standard 1–7 or Form 1–6), Gender, Date of Birth, Tanzanian Region and District, and Guardian contact details.
- Validates for duplicate Registration Numbers and invalid formats.

### 2. 📋 Display Student Records
- A full paginated **Student Directory** with search by name, registration number, or school.
- Filters for School Level (Primary / Secondary) and Tanzanian Region.
- Responsive table with quick "View" links to individual profiles.

### 3. 🔍 Search by Registration Number
- Dedicated search page returning a complete detailed student profile card.
- Shows: personal details, school information, location, guardian contacts, and registration status.
- Clear error messaging when a record is not found.

### 4. 🔐 User Management (Mandatory)
- Secure login system with password hashing using PHP's `password_hash()` / `password_verify()`.
- Session management with session regeneration on login (prevents session fixation attacks).
- Only authenticated users can access any part of the system.
- Default administrator account seeded on setup.

---

## Installation Steps

### Requirements
- Linux/macOS/Windows system
- PHP 8.0+ (or use the included static binary instructions below)
- SQLite3 support (bundled with PHP)

### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06.git
   cd OpenSource_Assignment_CS2_Group06
   ```

2. **Run database initialization** (creates tables and seeds default admin):
   ```bash
   php setup_db.php
   ```

3. **Start the PHP built-in development server:**
   ```bash
   php -S 127.0.0.1:8000
   ```

4. **Access the system** in your browser at:
   ```
   http://127.0.0.1:8000/login.php
   ```

5. **Default credentials:**
   | Username | Password  | Role          |
   |----------|-----------|---------------|
   | `admin`  | `admin123`| Administrator |

> **Note:** If PHP is not installed on your system, download the precompiled static binary from [swoole/build-static-php releases](https://github.com/swoole/build-static-php/releases) and use `./bin/php` instead of `php`.

---

## Technologies Used

| Technology     | Purpose                                   |
|----------------|-------------------------------------------|
| **PHP 8.4**    | Server-side scripting language            |
| **SQLite 3**   | Embedded relational database (via PDO)    |
| **HTML5**      | Semantic page structure                   |
| **Vanilla CSS**| Modern glassmorphism design system        |
| **Git**        | Version control system                    |
| **GitHub**     | Remote repository hosting and collaboration |
| **Google Fonts (Outfit)** | Modern web typography          |

---

## Git Commands Used

### Initial Setup
```bash
git init
git remote add origin https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06.git
```

### Daily Development Workflow
```bash
git status                          # Check working tree status
git add <file>                      # Stage a specific file
git add .                           # Stage all changes
git commit -m "Meaningful message"  # Commit with descriptive message
git log --oneline                   # View compact commit history
```

### Branch Operations
```bash
git checkout -b development         # Create and switch to development branch
git checkout main                   # Switch back to main branch
git merge development --no-ff       # Merge with a merge commit (preserves branch history)
git branch -a                       # List all branches (local + remote)
```

### Remote / Publishing
```bash
git push origin main                # Push main branch to GitHub
git push origin development         # Push development branch to GitHub
git pull origin main                # Pull latest changes from remote
```

### Commit History
```bash
git log --oneline --graph --all     # View full branching commit graph
```

---

## Commit History Summary

| # | Branch | Commit Message |
|---|--------|---------------|
| 1 | main | `docs: add README with project title and description` |
| 2 | main | `Initialize database connection and layout style sheet` |
| 3 | main | `Add secure user authentication and login system` |
| 4 | main | `Implement Dashboard home page with student analytics` |
| 5 | development | `Implement student registration form with validation` |
| 6 | development | `Implement student list display and search by registration number` |
| 7 | main | `Merge development branch: add student registration, directory, and search features` |
| 8 | main | `Update README documentation and merge development branch` |

---

## Project Structure

```
OpenSource_Assignment_CS2_Group06/
├── css/
│   └── style.css              # Global stylesheet (glassmorphism, variables, animations)
├── includes/
│   ├── auth.php               # Session management and authentication helper
│   ├── header.php             # Sidebar navigation and layout header
│   └── footer.php             # Layout footer and theme JavaScript
├── db.php                     # PDO SQLite database connection
├── setup_db.php               # Database initialization and seeder script
├── login.php                  # Secure user login page
├── logout.php                 # Secure logout handler
├── index.php                  # Main analytics dashboard
├── register_student.php       # Student registration form
├── students.php               # Student directory with filtering and pagination
├── search.php                 # Search student by registration number
├── .gitignore                 # Git ignore rules
└── README.md                  # Project documentation
```

---

## GitHub Repository

🔗 **[https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06](https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06)**

---

## Group Members

| # | Name | Role |
|---|------|------|
| 1 | *(Member 1)* | *(Role)* |
| 2 | *(Member 2)* | *(Role)* |
| 3 | *(Member 3)* | *(Role)* |
| 4 | *(Member 4)* | *(Role)* |

---

*Tanzania School Information Management System — MwalimuHub SIMS © 2026*
