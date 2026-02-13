<?php
// ADD THIS AT THE VERY TOP - BEFORE ANYTHING ELSE
session_start();

require_once 'config/database.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: student_login.php');
    exit();
}

// Get student submissions
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM submissions WHERE student_id = ? ORDER BY submission_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | EduPortal LMS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Student Dashboard Header -->
        <header class="dashboard-header">
            <div class="dashboard-info">
                <div class="user-profile">
                    <div class="avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                        <p class="user-role">
                            <i class="fas fa-id-card"></i> LRN: <?php echo htmlspecialchars($_SESSION['user_lrn']); ?>
                            <span class="badge badge-student">Student</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <nav class="dashboard-nav">
                <a href="student_dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </header>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Assignment submitted successfully!
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #4e73df;">
                    <i class="fas fa-file-upload"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($submissions); ?></h3>
                    <p>Assignments Submitted</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #1cc88a;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($submissions, fn($s) => !empty($s['marks']))); ?></h3>
                    <p>Assignments Graded</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #f6c23e;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count(array_filter($submissions, fn($s) => empty($s['marks']))); ?></h3>
                    <p>Pending Assignments</p>
                </div>
            </div>
        </div>

        <!-- Submission Form Card -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-upload"></i> Submit New Assignment</h3>
            </div>
            <div class="card-body">
                <form action="submit.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="subject"><i class="fas fa-book"></i> Subject</label>
                        <input type="text" id="subject" name="subject" required 
                               placeholder="e.g., Mathematics, Physics">
                    </div>
                    
                    <div class="form-group">
                        <label for="assignment"><i class="fas fa-file"></i> Assignment File</label>
                        <div class="file-upload-area" onclick="document.getElementById('assignment').click()">
                            <input type="file" id="assignment" name="assignment" 
                                   class="file-input" accept=".pdf,.doc,.docx" required
                                   onchange="updateFileName(this)">
                            <div class="file-upload-info" id="fileInfo">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <p class="text-muted">PDF, DOC, DOCX up to 10MB</p>
                            </div>
                        </div>
                        <div id="fileName" style="margin-top: 10px; display: none;"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Assignment
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Submission History</h3>
            </div>
            <div class="card-body">
                <?php if (empty($submissions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No submissions yet</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>File</th>
                                <th>Marks</th>
                                <th>Remarks</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>#<?php echo $submission['id']; ?></td>
                                <td><?php echo htmlspecialchars($submission['subject']); ?></td>
                                <td>
                                    <a href="download.php?id=<?php echo $submission['id']; ?>" 
                                       class="btn btn-sm btn-outline">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($submission['marks'])): ?>
                                        <span class="badge badge-reviewed">
                                            <?php echo htmlspecialchars($submission['marks']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($submission['remarks'])): ?>
                                        <div class="teacher-remarks">
                                            <i class="fas fa-comment"></i>
                                            <?php echo htmlspecialchars($submission['remarks']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No remarks yet</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($submission['submission_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add JavaScript for better file upload UX -->
    <script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileInfo').style.display = 'none';
            document.getElementById('fileName').style.display = 'block';
            document.getElementById('fileName').innerHTML = 
                '<i class="fas fa-file"></i> Selected: ' + input.files[0].name;
        }
    }

    function validateForm() {
        const fileInput = document.getElementById('assignment');
        const subjectInput = document.getElementById('subject');
        
        if (!subjectInput.value.trim()) {
            alert('Please enter a subject');
            subjectInput.focus();
            return false;
        }
        
        if (!fileInput.files || !fileInput.files[0]) {
            alert('Please select a file');
            return false;
        }
        
        // File size validation
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (fileInput.files[0].size > maxSize) {
            alert('File size exceeds 10MB limit');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>