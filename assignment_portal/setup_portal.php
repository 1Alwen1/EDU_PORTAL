<?php
// setup_portal.php - Complete setup script
session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <title>EduPortal LMS Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .step { margin-bottom: 20px; padding: 15px; border-left: 4px solid #4e73df; }
    </style>
</head>
<body>
    <h1>📚 EduPortal LMS Setup Wizard</h1>
    <p>This script will help you set up the assignment portal.</p>
    <hr>
";

// Step 1: Check PHP version
echo "<div class='step'>";
echo "<h3>Step 1: PHP Version Check</h3>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "<p class='success'>✅ PHP " . PHP_VERSION . " is compatible</p>";
} else {
    echo "<p class='error'>❌ PHP " . PHP_VERSION . " is too old. Please upgrade to PHP 7.4 or higher.</p>";
}
echo "</div>";

// Step 2: Check required functions
echo "<div class='step'>";
echo "<h3>Step 2: Required PHP Functions</h3>";
$required_functions = ['mysqli_connect', 'session_start', 'password_hash'];
$all_ok = true;
foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<p>✅ $func is available</p>";
    } else {
        echo "<p class='error'>❌ $func is NOT available</p>";
        $all_ok = false;
    }
}
echo "</div>";

// Step 3: Create database connection
echo "<div class='step'>";
echo "<h3>Step 3: Database Connection</h3>";
try {
    $conn = new mysqli('localhost', 'root', '');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<p class='success'>✅ Connected to MySQL server</p>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS assignment_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<p class='success'>✅ Database 'assignment_portal' created or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select database
    $conn->select_db('assignment_portal');
    
    // Create tables
    $tables_sql = [
        "CREATE TABLE IF NOT EXISTS admin (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS teachers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            subject VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS students (
            id INT PRIMARY KEY AUTO_INCREMENT,
            lrn VARCHAR(20) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS submissions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            student_id INT,
            teacher_id INT,
            student_name VARCHAR(100) NOT NULL,
            subject VARCHAR(100) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            marks VARCHAR(10) DEFAULT NULL,
            remarks TEXT,
            submission_date DATE NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
            FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
        )"
    ];
    
    foreach ($tables_sql as $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "<p>✅ Table created successfully</p>";
        } else {
            echo "<p class='error'>❌ Error creating table: " . $conn->error . "</p>";
        }
    }
    
    // Create default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_sql = "INSERT INTO admin (username, password) VALUES ('admin', ?)
                  ON DUPLICATE KEY UPDATE password = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("ss", $admin_password, $admin_password);
    if ($stmt->execute()) {
        echo "<p class='success'>✅ Admin user created/updated (username: admin, password: admin123)</p>";
    }
    $stmt->close();
    
    // Create sample teacher
    $teacher_password = password_hash('teacher123', PASSWORD_DEFAULT);
    $teacher_sql = "INSERT INTO teachers (name, email, subject, password) VALUES 
                   ('John Smith', 'john@example.com', 'Mathematics', ?),
                   ('Sarah Johnson', 'sarah@example.com', 'Physics', ?)
                   ON DUPLICATE KEY UPDATE password = ?";
    $stmt = $conn->prepare($teacher_sql);
    $stmt->bind_param("sss", $teacher_password, $teacher_password, $teacher_password);
    if ($stmt->execute()) {
        echo "<p class='success'>✅ Sample teachers created (password: teacher123)</p>";
    }
    $stmt->close();
    
    // Create sample student
    $student_password = password_hash('student123', PASSWORD_DEFAULT);
    $student_sql = "INSERT INTO students (lrn, name, email, password) VALUES 
                   ('123456789012', 'Alex Johnson', 'alex@example.com', ?)
                   ON DUPLICATE KEY UPDATE password = ?";
    $stmt = $conn->prepare($student_sql);
    $stmt->bind_param("ss", $student_password, $student_password);
    if ($stmt->execute()) {
        echo "<p class='success'>✅ Sample student created (LRN: 123456789012, password: student123)</p>";
    }
    $stmt->close();
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Step 4: Create uploads directory
echo "<div class='step'>";
echo "<h3>Step 4: File System Setup</h3>";
if (!is_dir('uploads')) {
    if (mkdir('uploads', 0755, true)) {
        echo "<p class='success'>✅ Uploads directory created</p>";
    } else {
        echo "<p class='error'>❌ Failed to create uploads directory</p>";
    }
} else {
    echo "<p class='success'>✅ Uploads directory already exists</p>";
}

if (!is_dir('config')) {
    if (mkdir('config', 0755, true)) {
        echo "<p class='success'>✅ Config directory created</p>";
    } else {
        echo "<p class='error'>❌ Failed to create config directory</p>";
    }
} else {
    echo "<p class='success'>✅ Config directory already exists</p>";
}

if (!is_dir('assets')) {
    if (mkdir('assets', 0755, true)) {
        echo "<p class='success'>✅ Assets directory created</p>";
    } else {
        echo "<p class='error'>❌ Failed to create assets directory</p>";
    }
} else {
    echo "<p class='success'>✅ Assets directory already exists</p>";
}
echo "</div>";

// Step 5: Create config file
echo "<div class='step'>";
echo "<h3>Step 5: Configuration File</h3>";
$config_content = '<?php
// Database configuration
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "assignment_portal");

// Create connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in (new system)
function isLoggedIn() {
    return isset($_SESSION[\'user_id\']);
}

// Check if admin is logged in (old system)
function isAdminLoggedIn() {
    return isset($_SESSION[\'admin_logged_in\']) && $_SESSION[\'admin_logged_in\'] === true;
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn() && !isAdminLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// Get user role
function getUserRole() {
    if (isAdminLoggedIn()) {
        return "admin";
    }
    return $_SESSION[\'user_role\'] ?? null;
}
?>';

if (file_put_contents('config/database.php', $config_content)) {
    echo "<p class='success'>✅ Configuration file created successfully</p>";
} else {
    echo "<p class='error'>❌ Failed to create configuration file</p>";
}
echo "</div>";

// Final message
echo "<hr>";
echo "<h2 class='success'>🎉 Setup Complete!</h2>";
echo "<p><strong>Login Credentials:</strong></p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username: admin, password: admin123</li>";
echo "<li><strong>Teacher:</strong> email: john@example.com, password: teacher123</li>";
echo "<li><strong>Student:</strong> LRN: 123456789012, password: student123</li>";
echo "</ul>";
echo "<p><strong>Important:</strong> Delete this setup file after installation for security.</p>";
echo "<p><a href='index.php' style='background:#4e73df;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Portal Homepage</a></p>";

echo "</body></html>";
?>