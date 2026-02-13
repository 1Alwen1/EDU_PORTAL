<?php
session_start();
require_once 'config/database.php';
requireLogin();

if (getUserRole() !== 'teacher') {
    header('Location: index.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Get teacher's current information
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, name, email, subject, created_at FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($name) || empty($email) || empty($subject)) {
        $errors[] = "Name, email, and subject are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email is already taken by another teacher
    if ($email !== $teacher['email']) {
        $stmt = $conn->prepare("SELECT id FROM teachers WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email is already registered by another teacher";
        }
        $stmt->close();
    }
    
    // Handle password change if provided
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM teachers WHERE id = ?");
            $stmt->bind_param("i", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $teacher_data = $result->fetch_assoc();
            
            if (!password_verify($current_password, $teacher_data['password'])) {
                $errors[] = "Current password is incorrect";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match";
            } elseif (strlen($new_password) < 6) {
                $errors[] = "New password must be at least 6 characters";
            }
            $stmt->close();
        }
    }
    
    if (empty($errors)) {
        // Update teacher information
        if (!empty($new_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE teachers SET name = ?, email = ?, subject = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $subject, $hashed_password, $teacher_id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE teachers SET name = ?, email = ?, subject = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $subject, $teacher_id);
        }
        
        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_subject'] = $subject;
            
            // Refresh teacher data
            $stmt = $conn->prepare("SELECT id, name, email, subject, created_at FROM teachers WHERE id = ?");
            $stmt->bind_param("i", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $teacher = $result->fetch_assoc();
            
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    } else {
        $error_msg = implode("<br>", $errors);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile | EduPortal LMS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Teacher Dashboard Header -->
        <header class="dashboard-header">
            <div class="dashboard-info">
                <div class="user-profile">
                    <div class="avatar">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h3>Teacher Profile</h3>
                        <p class="user-role">
                            <i class="fas fa-user"></i> Manage your account information
                        </p>
                    </div>
                </div>
            </div>
            
            <nav class="dashboard-nav">
                <a href="teacher_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="teacher_profile.php" class="active">
                    <i class="fas fa-user-cog"></i> Profile
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </header>

        <!-- Success/Error Messages -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            </div>
        <?php elseif ($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Information Card -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-circle"></i> Profile Information</h3>
            </div>
            <div class="card-body">
                <form method="POST" class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Full Name *
                            </label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($teacher['name']); ?>"
                                   class="form-control" placeholder="Your full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($teacher['email']); ?>"
                                   class="form-control" placeholder="Your email address">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">
                            <i class="fas fa-book"></i> Teaching Subject *
                        </label>
                        <input type="text" id="subject" name="subject" required
                               value="<?php echo htmlspecialchars($teacher['subject']); ?>"
                               class="form-control" placeholder="e.g., Mathematics, Physics">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="created_at">
                                <i class="fas fa-calendar"></i> Account Created
                            </label>
                            <input type="text" id="created_at" 
                                   value="<?php echo date('F d, Y', strtotime($teacher['created_at'])); ?>"
                                   class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="teacher_id">
                                <i class="fas fa-id-badge"></i> Teacher ID
                            </label>
                            <input type="text" id="teacher_id" 
                                   value="TCH-<?php echo str_pad($teacher['id'], 4, '0', STR_PAD_LEFT); ?>"
                                   class="form-control" readonly>
                        </div>
                    </div>
                    
                    <div class="section-divider">
                        <h4><i class="fas fa-lock"></i> Change Password</h4>
                        <p class="form-text">Leave blank to keep current password</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-key"></i> Current Password
                            </label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control" placeholder="Enter current password">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-key"></i> New Password
                            </label>
                            <input type="password" id="new_password" name="new_password"
                                   class="form-control" placeholder="Enter new password (min 6 chars)">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-key"></i> Confirm New Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="form-control" placeholder="Confirm new password">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                        <a href="teacher_dashboard.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Account Statistics</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <?php
                    // Get statistics
                    $conn = getDBConnection();
                    
                    // Get total submissions
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM submissions WHERE teacher_id = ?");
                    $stmt->bind_param("i", $teacher_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $total_subs = $result->fetch_assoc()['total'];
                    $stmt->close();
                    
                    // Get reviewed submissions
                    $stmt = $conn->prepare("SELECT COUNT(*) as reviewed FROM submissions WHERE teacher_id = ? AND remarks IS NOT NULL AND remarks != ''");
                    $stmt->bind_param("i", $teacher_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $reviewed_subs = $result->fetch_assoc()['reviewed'];
                    $stmt->close();
                    
                    // Get pending submissions
                    $pending_subs = $total_subs - $reviewed_subs;
                    
                    $conn->close();
                    ?>
                    
                    <div class="stat-item">
                        <div class="stat-icon" style="background: #4e73df;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $total_subs; ?></h3>
                            <p>Total Submissions</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon" style="background: #1cc88a;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $reviewed_subs; ?></h3>
                            <p>Reviewed</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon" style="background: #f6c23e;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $pending_subs; ?></h3>
                            <p>Pending Review</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .profile-form {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .section-divider {
            margin: 2rem 0 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .section-divider h4 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .stat-item {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .stat-details h3 {
            font-size: 1.8rem;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }
        
        .stat-details p {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
    </style>
</body>
</html>