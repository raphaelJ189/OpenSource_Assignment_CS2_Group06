# Project Title: Student Record Management System (SRMS)

**Course:** Open Source Technologies (CP 222)  
**Group:** GROUP NO 6  

### Group Members:
| No. | Name | Registration No. |
|---|---|---|
| 1 | Khadija Seif Khatib | T24-03-20735 |
| 2 | Eva Eliud Kasabundi | T24-03-15679 |
| 3 | Denis Daniel Mfallah | T24-03-15737 |
| 4 | James Joseph Kambona | T24-03-20736 |
| 5 | Amos Jackson Hosea | T24-03-15313 |
| 6 | Amon Aristedes Toroto | T24-03-10331 |
| 7 | Brian January Ntisi | T24-03-18251 |
| 8 | Raphael Jonas Mwaka | T24-03-14150 |
| 9 | Chanila Joseph Mugumya | T24-03-26045 |
| 10 | Allan Marcelo Jada | T24-03-10324 |

---

## 1. Introduction
The **MwalimuHub SIMS** is a web-based Student Information Management System designed specifically for primary and secondary schools in Tanzania. Built as a collaborative project for the Open Source Technologies coursework, this system aims to transition school administrators and teachers from traditional paper-based record-keeping to a digital, secure, and highly efficient platform. The system supports distinct roles for Administrators and Teachers, enabling seamless management of student registrations, records updates, and user access control.

## 2. Project Dependencies
To successfully run and deploy the MwalimuHub SIMS, the following software components and extensions are required:
- **Programming Language:** PHP (Version 8.0 or higher)
- **Database Management System:** MySQL or MariaDB
- **Web Server:** Apache HTTP Server, Nginx, or PHP's built-in development server
- **Required PHP Extensions:** `pdo`, `pdo_mysql` (for secure database interaction using prepared statements)
- **Client-Side:** Modern web browser with HTML5 and CSS3 support (no external CSS/JS frameworks are required, as the project utilizes vanilla technologies).

## 3. GitHub Repository Link
The complete source code and version history of the project can be accessed via our GitHub repository:
[https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06](https://github.com/raphaelJ189/OpenSource_Assignment_CS2_Group06)

## 4. Screenshots for Key Functionalities

### 1. Login Page
*(Please insert screenshot of the login page here)*
![Login Page Screenshot](insert_login_screenshot_here.png)

### 2. Admin Dashboard
*(Please insert screenshot of the admin dashboard here)*
![Admin Dashboard Screenshot](insert_dashboard_screenshot_here.png)

### 3. Student Registration Form
*(Please insert screenshot of the student registration page here)*
![Student Registration Screenshot](insert_registration_screenshot_here.png)

## 5. Source Code Summary
The project is structured into modular components to separate logic, database connections, and user interface elements:
- **`db.php` & `setup_db.php`**: Handles the PDO database connection and automatic table creation.
- **`index.php` & `login.php`**: Manages the entry point and secure user authentication (using `password_hash` and `password_verify`).
- **`admin/` directory**: Contains scripts for the Administrator role, including `dashboard.php`, `create_teacher.php`, and `edit_teacher.php`.
- **`register_student.php`, `edit_student.php`, `delete_student.php`**: Core CRUD (Create, Read, Update, Delete) operations for managing student records.
- **`search.php`**: Implements a robust search functionality allowing teachers to query students by registration number or full name.
- **`css/style.css`**: Centralized stylesheet containing all UI designs, including responsive layouts, theming (light/dark mode), and glassmorphism aesthetics.

## 6. Challenges Encountered
During the development lifecycle, the team encountered and overcame several technical challenges:
1. **Database Connection Errors**: Initially faced issues establishing a secure PDO connection with the MySQL database, which was resolved by correctly configuring the host (`127.0.0.1`), user (`srms_user`), and database name (`sims_db`).
2. **Role-Based Access Control (RBAC)**: Implementing strict session management to ensure that Teachers could not access Admin-only pages required careful session variable validation.
3. **Responsive Design**: Ensuring that the complex tables and forms displayed correctly on both desktop and mobile devices required extensive use of CSS Flexbox and media queries.
4. **Unique Registration Number Generation**: Designing an algorithm to automatically generate unique registration numbers in the format `S+SCHNO+/STNO/YEAR` while avoiding database collisions.

## 7. Conclusion
In conclusion, the MwalimuHub SIMS successfully fulfills the requirements of the Open Source Technologies assignment. It demonstrates the practical application of PHP, MySQL, HTML, and CSS in building a secure, role-based web application. The project not only improved our technical skills in backend development and database management but also highlighted the importance of collaborative development using version control tools like Git and GitHub.
