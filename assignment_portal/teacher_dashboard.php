<?php
session_start();  // <-- FIX 1: Start session
require_once 'config/database.php';
requireLogin();

if (getUserRole() !== 'teacher') {
    header('Location: index.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_subject = $_SESSION['user_subject'];

// Handle updates – now with marks + remarks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grading'])) {
    $submission_id = intval($_POST['submission_id']);
    $marks = trim($_POST['marks'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE submissions SET marks = ?, remarks = ? WHERE id = ? AND subject = ?");
    $stmt->bind_param("ssis", $marks, $remarks, $submission_id, $teacher_subject);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location: teacher_dashboard.php?updated=1');
    exit();
}

// Handle deletion (unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_submission'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM submissions WHERE id = ? AND subject = ?");
    $stmt->bind_param("is", $_POST['submission_id'], $teacher_subject);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location: teacher_dashboard.php?deleted=1');
    exit();
}

// Get submissions for this teacher's subject
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT s.*, st.name as student_name 
                       FROM submissions s 
                       LEFT JOIN students st ON s.student_id = st.id 
                       WHERE s.subject = ? 
                       ORDER BY s.submission_date DESC");
$stmt->bind_param("s", $teacher_subject);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Statistics
$total_submissions = count($submissions);
$reviewed_count = count(array_filter($submissions, fn($s) => !empty($s['marks'])));
$pending_count = $total_submissions - $reviewed_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | EduPortal LMS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Dashboard Header (unchanged) -->
        <header class="dashboard-header">
            <div class="dashboard-info">
                <div class="user-profile">
                    <div class="avatar">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                        <p class="user-role">
                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($_SESSION['user_subject']); ?>
                            <span class="badge badge-teacher">Teacher</span>
                        </p>
                    </div>
                </div>
            </div>
            <nav class="dashboard-nav">
                <a href="teacher_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="teacher_profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </header>

        <!-- Status messages -->
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Marks & remarks updated successfully!</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-warning"><i class="fas fa-trash"></i> Submission deleted successfully!</div>
        <?php endif; ?>

        <!-- Dashboard Stats (unchanged) -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #4e73df;"><i class="fas fa-file-alt"></i></div>
                <div class="stat-info"><h3><?php echo $total_submissions; ?></h3><p>Total Submissions</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #1cc88a;"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><h3><?php echo $reviewed_count; ?></h3><p>Graded</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #f6c23e;"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><h3><?php echo $pending_count; ?></h3><p>Pending</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e74a3b;"><i class="fas fa-download"></i></div>
                <div class="stat-info">
                    <a href="download_all.php?teacher=<?php echo $teacher_id; ?>" class="stat-link">
                        <h3>Download All</h3><p>As ZIP</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Assignment Submissions – <?php echo htmlspecialchars($teacher_subject); ?></h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>File</th>
                            <th>Submitted</th>
                            <th>Marks</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submissions)): ?>
                            <tr><td colspan="7" class="text-center"><div class="empty-state"><i class="fas fa-inbox"></i><p>No submissions yet</p></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>#<?php echo $submission['id']; ?></td>
                                <td>
                                    <div class="student-info">
                                        <i class="fas fa-user-graduate"></i>
                                        <?php echo htmlspecialchars($submission['student_name'] ?? 'Unknown'); ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="download.php?id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($submission['submission_date'])); ?></td>
                                
                                <!-- FIX 2: Marks & Remarks input + update button -->
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <input type="text" name="marks" 
                                               value="<?php echo htmlspecialchars($submission['marks'] ?? ''); ?>" 
                                               placeholder="e.g., 85/100" 
                                               class="marks-input" style="width:90px;">
                                </td>
                                <td>
                                        <textarea name="remarks" class="remarks-input" 
                                                  placeholder="Add remarks..." 
                                                  rows="2"><?php echo htmlspecialchars($submission['remarks'] ?? ''); ?></textarea>
                                </td>
                                <td>
                                        <button type="submit" name="update_grading" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Update
                                        </button>
                                    </form>
                                    <form method="POST" class="inline-form" style="margin-top:5px;">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <button type="submit" name="delete_submission" 
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this submission?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>