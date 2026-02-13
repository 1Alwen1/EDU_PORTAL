<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'assignment_portal');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Create database connection
 * @return mysqli Database connection object
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Check if user is logged in
 * @return void Redirects to login if not logged in
 */
function requireLogin() {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Get current user's role
 * @return string User role (admin, teacher, student)
 */
function getUserRole() {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['user_role'] ?? '';
}

/**
 * Sanitize input data
 * @param string $data Input to sanitize
 * @return string Sanitized input
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>