# Assignment Portal - Learning Management System (LMS)

## Overview

EduPortal LMS is a complete Assignment Portal system built with PHP and MySQL. It provides a seamless platform for teachers to manage assignments and for students to submit their work online.

## Features

### For Administrators
- Admin dashboard with overview of all submissions
- Manage system settings
- View all teachers and students
- Secure admin login

### For Teachers
- Teacher signup and login system
- View and manage student submissions
- Grade assignments with marks and remarks
- Download individual or bulk submissions
- Teacher profile management

### For Students
- Student signup and login system
- Submit assignments in PDF, DOC, DOCX formats
- View submission history and grades
- Receive feedback from teachers

### System Features
- Easy file upload and submission
- Auto-grading support
- Progress tracking
- Secure file storage
- Session management
- Input sanitization for security

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Styling**: Custom CSS with Font Awesome icons

## Project Structure

```
assignment_portal/
├── admin_dashboard.php      # Admin dashboard
├── admin_login.php          # Admin login page
├── admin_logout.php         # Admin logout
├── check_session.php       # Session verification
├── delete.php               # Delete submissions
├── download_all.php         # Bulk download
├── download.php             # Single file download
├── index.php                # Landing page
├── logout.php               # User logout
├── setup_portal.php         # System setup
├── student_dashboard.php    # Student dashboard
├── student_login.php        # Student login
├── student_signup.php       # Student registration
├── submit.php               # Assignment submission
├── teacher_dashboard.php    # Teacher dashboard
├── teacher_login.php        # Teacher login
├── teacher_profile.php      # Teacher profile
├── teacher_signup.php       # Teacher registration
├── assets/
│   └── style.css            # Stylesheet
├── config/
│   └── database.php         # Database configuration
├── data/
│   └── Ils_db.sql           # Database schema
└── uploads/                 # Uploaded files directory
```

## Installation

### Prerequisites
- XAMPP or WAMP (Apache, MySQL, PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Start Apache and MySQL** in XAMPP Control Panel

2. **Import Database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `assignment_portal`
   - Import the `data/Ils_db.sql` file

3. **Configure Database**:
   - Edit `config/database.php` if needed
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: assignment_portal

4. **Access the Portal**:
   - Open http://localhost/assignment_portal in your browser

## Default Login Credentials

### Admin
- Username: admin
- Password: admin123

### Teacher
- Email: john@example.com / sarah@example.com
- Password: teacher123

### Student
- LRN: 123456789012
- Password: student123

## Usage

### For Teachers
1. Login to teacher portal
2. View submitted assignments in dashboard
3. Download submissions for review
4. Add marks and remarks
5. Download graded assignments

### For Students
1. Register a new account or login
2. Navigate to student dashboard
3. Submit assignments with subject name
4. View submission history and grades

### For Admins
1. Login to admin portal
2. View all submissions across teachers
3. Manage system overview

## File Upload

- Supported formats: PDF, DOC, DOCX
- Maximum file size: Depends on PHP configuration (default 2MB)
- Files are stored in `uploads/` directory with unique naming

## Security Features

- Password hashing (BCrypt)
- Session management
- SQL injection prevention
- Input sanitization
- File type validation

## Configuration

### PHP Settings (php.ini)
For larger file uploads, adjust:
```
ini
upload_max_filesize = 10M
post_max_size = 10M
```

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL service is running
   - Verify database credentials in config/database.php

2. **File Upload Failed**
   - Check uploads directory permissions
   - Verify PHP upload settings

3. **Session Issues**
   - Clear browser cache
   - Check PHP session configuration

## License

This project is for educational purposes.

## Support

For issues or questions, please contact the system administrator.

---

© <?php echo date('Y'); ?> EduPortal LMS. All rights reserved.
