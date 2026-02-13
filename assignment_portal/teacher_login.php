<?php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'teacher') {
    header('Location: teacher_dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, name, email, subject, password FROM teachers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $teacher = $result->fetch_assoc();
            
            if (password_verify($password, $teacher['password'])) {
                $_SESSION['user_id'] = $teacher['id'];
                $_SESSION['user_role'] = 'teacher';
                $_SESSION['user_name'] = $teacher['name'];
                $_SESSION['user_email'] = $teacher['email'];
                $_SESSION['user_subject'] = $teacher['subject'];
                
                header('Location: teacher_dashboard.php');
                exit();
            }
        }
        
        $error = 'Invalid email or password';
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
    <title>Teacher Login | EduPortal LMS</title>
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
                <h2>Teacher Login</h2>
                <p>Access your dashboard to manage assignments</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="Enter your registered email">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="teacher_signup.php">Sign up here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>