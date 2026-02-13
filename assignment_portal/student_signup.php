<?php
// ADD SESSION START AT THE VERY TOP
session_start();

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = trim($_POST['lrn']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($lrn) || empty($name) || empty($password)) {
        $errors[] = "LRN, Name, and Password are required";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($lrn) < 12 || strlen($lrn) > 12) {
        $errors[] = "LRN must be exactly 12 digits";
    }
    
    if (!ctype_digit($lrn)) {
        $errors[] = "LRN must contain only numbers";
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Check if LRN exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE lrn = ?");
        $stmt->bind_param("s", $lrn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "LRN already registered";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO students (lrn, name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $lrn, $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Auto login after signup
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_role'] = 'student';
                $_SESSION['user_name'] = $name;
                $_SESSION['user_lrn'] = $lrn;
                
                // Clear output buffer before redirect
                if (ob_get_length()) {
                    ob_end_clean();
                }
                
                header('Location: student_dashboard.php?welcome=1');
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
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
    <title>Student Signup | EduPortal LMS</title>
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
                <h2>Student Registration</h2>
                <p>Create your account to submit assignments</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="lrn">
                        <i class="fas fa-id-card"></i> Learner's Reference Number (LRN)
                    </label>
                    <input type="text" id="lrn" name="lrn" required 
                           value="<?php echo htmlspecialchars($_POST['lrn'] ?? ''); ?>"
                           placeholder="Enter 12-digit LRN" maxlength="12">
                </div>
                
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" id="name" name="name" required
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                           placeholder="Enter your full name">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address (Optional)
                    </label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="Enter your email (optional)">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Minimum 6 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Re-enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Student Account
                </button>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="student_login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>