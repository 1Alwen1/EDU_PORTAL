<?php
// student_login.php - SIMPLIFIED VERSION
session_start();

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = trim($_POST['lrn']);
    $password = $_POST['password'];
    
    if (empty($lrn) || empty($password)) {
        $error = 'Please enter LRN and password';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, lrn, name, password FROM students WHERE lrn = ?");
        $stmt->bind_param("s", $lrn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            if (password_verify($password, $student['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $student['id'];
                $_SESSION['user_role'] = 'student';
                $_SESSION['user_name'] = $student['name'];
                $_SESSION['user_lrn'] = $student['lrn'];
                
                header('Location: student_dashboard.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Student not found with LRN: ' . htmlspecialchars($lrn);
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | EduPortal LMS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <a href="index.php" class="back-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <h2>Student Login</h2>
                <p>Access your dashboard to submit assignments</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="lrn">
                        <i class="fas fa-id-card"></i> LRN
                    </label>
                    <input type="text" id="lrn" name="lrn" required
                           placeholder="Enter your LRN">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>