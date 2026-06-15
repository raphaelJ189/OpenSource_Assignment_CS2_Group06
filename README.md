## Project Title
Student Record Management System (SRMS) for Primary and Secondary School

## Project Overview

**MwalimuHub SIMS** is a web-based Student Information Management System designed for primary and secondary schools in Tanzania. Built as part of the Open Source Technologies (CP 222) coursework, the system enables school administrators to digitally manage student records, improving data accuracy, accessibility, and operational efficiency.

This project is a PHP-based Student Record Management System designed to manage student records securely and efficiently.

### Features
The system includes modern and secure features according to the Software Requirements Specification (SRS):
- **User Authentication**: Secure login and logout using PHP `password_hash()`.
- **Role-Based Access Control**: Different access levels for `Admin` and `Teacher` roles.
- **Admin Capabilities**: Create, edit, and deactivate teacher accounts. View total active teachers.
- **Teacher Capabilities**: 
  - Register new students. The system automatically generates a unique registration number (Format: `S+SCHNO+/STNO/YEAR`).
  - View the full directory of registered students.
  - Track "My Registrations" on their personalized dashboard.
- **Search Functionality**: Search for any student using either their exact **Registration Number** or their **Full Name**.
- **Record Management**: Edit and update student information. Delete records (Admin functionality).
- **Modern UI/UX**: 
  - Premium Glassmorphism design aesthetics.
  - Fully responsive layout.
  - Light and Dark mode theme toggle.
  - Global page loaders for smooth transitions between pages and actions.

## Technology Used
- **Backend**: PHP 8+
- **Database**: MySQL (PDO for secure, prepared statements)
- **Frontend**: HTML5, CSS3 (Vanilla CSS with CSS Variables for theming)

## Dependencies
- **PHP**: Version 8.0 or higher.
- **MySQL Database Server**: Any compatible version (e.g., MySQL 5.7+ or MariaDB).
- **PHP PDO Extension**: Required for secure database connections.
- **Web Server**: Apache or Nginx (can also use PHP's built-in development server).

## Installation Steps
1. Clone the repository to your local web server environment (e.g., XAMPP, LAMP).
2. Ensure you have a MySQL server running and create an empty database named `sims_db`.
3. If necessary, configure your database credentials in `db.php` (default is `srms_user` / `srms_pass` or `root` / `""`).
4. Run the `setup_db.php` script from your browser to automatically create the database tables.
5. The default admin account (`admin` / `admin123`) is created automatically during setup.
6. Navigate to `index.php` to log in and start using the system.

## Git Commands Used
Throughout the development of this project, the following Git commands were frequently used to manage version control and collaborate:
- `git clone <repository-url>`: To clone the remote repository to the local machine.
- `git status`: To check the current status of files (modified, untracked, staged).
- `git add .`: To stage all modified and new files for a commit.
- `git commit -m "commit message"`: To commit staged changes with a descriptive message.
- `git push origin main`: To upload local commits to the remote GitHub repository.
- `git pull origin main`: To fetch and integrate remote changes into the local branch.

## Github Repository Link
**Repository:** [https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06](https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06)
