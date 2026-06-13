## Project Title
Student Record Management System (SRMS) for Primary and Secondary School

## Degree Program
Computer Science (Bsc-CS2)

## Group Number
Group 06

## Project Description
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

### Technical Stack
- **Backend**: PHP 8+
- **Database**: MySQL (PDO for secure, prepared statements)
- **Frontend**: HTML5, CSS3 (Vanilla CSS with CSS Variables for theming)

### Installation
1. Clone the repository to your local web server environment (e.g., XAMPP, LAMP).
2. Run the `setup_db.php` script from your browser to automatically create the database and tables.
3. The default admin account (`admin` / `admin123`) is created automatically.
4. Navigate to `index.php` to log in and start using the system.
