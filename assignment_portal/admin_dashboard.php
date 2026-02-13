<?php
require_once 'config/database.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE submissions SET marks = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $_POST['marks'], $_POST['remarks'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    header('Location: admin_dashboard.php?updated=1');
    exit();
}

// Get all submissions
$conn = getDBConnection();
$result = $conn->query("SELECT * FROM submissions ORDER BY submitted_at DESC");
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>📋 Assignment Submissions Dashboard</h1>
            <div class="admin-actions">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                <a href="download_all.php" class="btn-download-all">📥 Download All as ZIP</a>
                <a href="admin_logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert success">✅ Marks updated successfully!</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert success">🗑️ Submission deleted successfully!</div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Submissions</h3>
                <p><?php echo count($submissions); ?></p>
            </div>
            <div class="stat-card">
                <h3>Graded</h3>
                <p><?php echo count(array_filter($submissions, fn($s) => !empty($s['marks']))); ?></p>
            </div>
        </div>
        
        <div class="submissions-table-container">
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Subject</th>
                        <th>File</th>
                        <th>Marks</th>
                        <th>Remarks</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No submissions yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo $submission['id']; ?></td>
                            <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($submission['subject']); ?></td>
                            <td>
                                <a href="download.php?id=<?php echo $submission['id']; ?>" 
                                   class="btn-download">
                                    📄 Download
                                </a>
                            </td>
                            <td>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                                    <input type="text" name="marks" 
                                           value="<?php echo htmlspecialchars($submission['marks'] ?? ''); ?>"
                                           placeholder="e.g., 85/100" class="marks-input">
                            </td>
                            <td>
                                    <input type="text" name="remarks"
                                           value="<?php echo htmlspecialchars($submission['remarks'] ?? ''); ?>"
                                           placeholder="Enter remarks" class="remarks-input">
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($submission['submitted_at'])); ?></td>
                            <td>
                                    <button type="submit" name="update" class="btn-update">Update</button>
                                </form>
                                <a href="delete.php?id=<?php echo $submission['id']; ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this submission?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>