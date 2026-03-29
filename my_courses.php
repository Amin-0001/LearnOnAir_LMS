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
$study_field = isset($_SESSION['study_field']) ? $_SESSION['study_field'] : 'General';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Learning - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">My Enrolled Courses</div>
            <div class="user-profile glass">
                <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <span><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
        <div class="course-grid">
            <?php
            // Query ONLY courses the user is enrolled in using a JOIN
            $sql = "SELECT c.*, u.username AS instructor_name 
                    FROM courses c 
                    JOIN enrollments e ON c.id = e.course_id 
                    JOIN users u ON c.instructor_id = u.id 
                    WHERE e.user_id = '$user_id'";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $course_id = $row['id'];
                    echo '<div class="course-card glass">';
                    echo '  <div class="course-img" style="background-image: url(\'' . htmlspecialchars($row['image_url']) . '\');">';
                    echo '      <div class="course-tag">Enrolled</div>';
                    echo '  </div>';
                    echo '  <div class="course-info">';
                    echo '      <h3 class="course-title">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '      <div class="course-instructor">Prof. ' . htmlspecialchars($row['instructor_name']) . '</div>';
                    // Thicker, gradient progress bar for the "My Learning" page
                    echo '      <div class="progress-wrapper">';
                    echo '          <div class="progress-text"><span>Completion</span><span>60%</span></div>';
                    echo '          <div class="progress-bar-bg"><div class="progress-bar-fill"></div></div>';
                    echo '      </div>';
                    // Buttons: One to watch lessons, one to jump to the class chat
                    echo '      <div class="btn-group">';
                    echo '          <a href="course_details.php?id=' . $course_id . '" class="btn-action">▶ Watch Lessons</a>';
                    echo '          <a href="course_chat.php?course_id=' . $course_id . '" class="btn-action btn-chat">💬 Chat</a>';
                    echo '      </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                // Beautiful Empty State if they haven't enrolled in anything yet
                echo '<div class="empty-state glass">';
                echo '  <h3>You haven\'t enrolled in any courses yet!</h3>';
                echo '  <p>Head over to your <a href="student_dashboard.php">Dashboard</a> to explore recommended classes and start learning today.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </main>
</body>
</html>