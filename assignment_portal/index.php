<?php
require_once 'config/database.php';

// If user is already logged in (new system), redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'teacher') {
        header('Location: teacher_dashboard.php');
    } elseif ($_SESSION['user_role'] === 'student') {
        header('Location: student_dashboard.php'); // Changed from student_login.php
    }
    exit();
}

// If admin is already logged in (old system), redirect to admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Portal | Learning Management System</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="main-header">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <h1>EduPortal<span>LMS</span></h1>
            </div>
            <nav class="main-nav">
                <a href="#features"><i class="fas fa-star"></i> Features</a>
                <a href="#about"><i class="fas fa-info-circle"></i> About</a>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h2>Welcome to Assignment Portal</h2>
                <p class="hero-subtitle">A complete learning management system for seamless assignment submission and grading</p>
                
                <div class="role-selection">
                    <div class="role-card teacher-card">
                        <div class="role-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3>Teacher Portal</h3>
                        <p>Grade assignments, provide feedback, and manage submissions</p>
                        <div class="role-actions">
                            <a href="teacher_signup.php" class="btn btn-outline">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </a>
                            <a href="teacher_login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                    </div>
                    
                    <div class="role-card student-card">
                        <div class="role-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3>Student Portal</h3>
                        <p>Submit assignments, track grades, and receive feedback</p>
                        <div class="role-actions">
                            <a href="student_signup.php" class="btn btn-outline">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </a>
                            <a href="student_login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3>500+</h3>
                        <p>Active Students</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-book-open"></i>
                    <div>
                        <h3>50+</h3>
                        <p>Courses</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-file-upload"></i>
                    <div>
                        <h3>10,000+</h3>
                        <p>Submissions</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <h2 class="section-title">System Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-upload"></i>
                    <h3>Easy Submission</h3>
                    <p>Upload assignments in PDF, DOC, DOCX formats with just a few clicks</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Auto Grading</h3>
                    <p>Teachers can grade assignments and provide instant feedback</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Progress Tracking</h3>
                    <p>Students can track their submission history and grades</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Secure Portal</h3>
                    <p>Protected file storage and encrypted data transmission</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="main-footer">
            <p>&copy; <?php echo date('Y'); ?> EduPortal LMS. All rights reserved.</p>
            <p class="footer-links">
                <a href="#">Privacy Policy</a> | 
                <a href="#">Terms of Service</a> | 
                <a href="#">Contact Support</a>
            </p>
        </footer>
    </div>
</body>
</html>