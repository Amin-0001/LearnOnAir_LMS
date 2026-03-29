<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check: Only Students Allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <h2 style="margin:0;">Academic Record</h2>
            <div class="header-actions">
                <a href="index.php" class="home-link">🏠 Back to Home</a>
                <div class="user-profile glass">
                    <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
        <div class="results-card glass">
            <h3>Final Assessments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Module</th>
                        <th>Completion Date</th>
                        <th>Score Given</th>
                        <th>Performance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch results joined with course titles
                    $sql = "SELECT qr.*, c.title 
                            FROM quiz_results qr 
                            JOIN courses c ON qr.course_id = c.id 
                            WHERE qr.user_id = '$user_id' 
                            ORDER BY qr.attempt_date DESC";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Math for the visual percentage bar
                            $percentage = 0;
                            if ($row['total_questions'] > 0) {
                                $percentage = round(($row['score'] / $row['total_questions']) * 100);
                            }
                            // Determine Pass/Fail (Assuming 50% is passing)
                            $is_pass = ($percentage >= 50);
                            $badge = $is_pass ? '<span class="badge-pass">PASSED</span>' : '<span class="badge-fail">FAILED</span>';
                            $bar_color = $is_pass ? '#2ecc71' : '#e74c3c';
                            $date = date("M d, Y", strtotime($row['attempt_date']));
                            echo '<tr>';
                            echo '  <td><strong>' . htmlspecialchars($row['title']) . '</strong></td>';
                            echo '  <td style="font-size: 13px; opacity: 0.8;">' . $date . '</td>';
                            echo '  <td><strong style="font-size: 18px;">' . $row['score'] . ' / ' . $row['total_questions'] . '</strong></td>';
                            echo '  <td style="width: 200px;">';
                            echo '      <div style="font-size: 12px; font-weight: bold; color: '.$bar_color.';">' . $percentage . '%</div>';
                            echo '      <div class="score-bar-bg"><div class="score-bar-fill" style="width: '.$percentage.'%; background: '.$bar_color.';"></div></div>';
                            echo '  </td>';
                            echo '  <td>' . $badge . '</td>';
                            echo '  <td>';
                            if ($is_pass) {
                                // Real-world LMS gives certificates for passing
                                echo '      <a href="generate_certificate.php?course_id=' . $row['course_id'] . '" target="_blank" class="btn-cert">📥 Certificate</a>';
                            } else {
                                echo '      <span style="font-size: 12px; opacity: 0.5;">Retake Required</span>';
                            }
                            echo '  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align:center; padding: 40px; opacity:0.7; font-size: 16px;">You have not completed any final assessments yet. Navigate to "My Learning" to take a quiz.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>