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
$study_field = isset($_SESSION['study_field']) && $_SESSION['study_field'] != '' ? $_SESSION['study_field'] : 'general';
// Format the study field to look nice
$display_field = ucwords(str_replace('_', ' ', $study_field));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <h2 style="margin:0;">Dashboard</h2>
            <div class="header-actions">
                <a href="index.php" class="home-link">🏠 Back to Home</a>
                <div class="user-profile glass">
                    <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
        <div class="stats-grid">
            <?php
            // Real count of enrolled courses
            $enroll_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE user_id = '$user_id'");
            $enroll_count = mysqli_fetch_assoc($enroll_q)['total'];
            // Real count of video lessons available
            $lessons_q = mysqli_query($conn, "SELECT COUNT(l.id) as total FROM lessons l 
                                             JOIN enrollments e ON l.course_id = e.course_id 
                                             WHERE e.user_id = '$user_id'");
            $lessons_count = mysqli_fetch_assoc($lessons_q)['total'];
            // Real count of study materials available
            $mats_q = mysqli_query($conn, "SELECT COUNT(m.id) as total FROM course_materials m 
                                         JOIN enrollments e ON m.course_id = e.course_id 
                                         WHERE e.user_id = '$user_id'");
            $mats_count = mysqli_fetch_assoc($mats_q)['total'];
            ?>
            <div class="stat-card glass">
                <div class="stat-icon" style="font-size:35px;">📚</div>
                <div class="stat-info">
                    <h3><?php echo $enroll_count; ?></h3>
                    <p>Active Courses</p>
                </div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon" style="font-size:35px;">▶️</div>
                <div class="stat-info">
                    <h3><?php echo $lessons_count; ?></h3>
                    <p>Lessons Available</p>
                </div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon" style="font-size:35px;">📄</div>
                <div class="stat-info">
                    <h3><?php echo $mats_count; ?></h3>
                    <p>Study Resources</p>
                </div>
            </div>
        </div>
        <h3 style="margin-bottom:20px; color:#ffb800;">Recommended for <?php echo $display_field; ?></h3>
        <div class="course-grid">
            <?php
            // Fetch courses based on student field
            $sql = "SELECT c.*, u.username AS instructor_name 
                    FROM courses c 
                    JOIN users u ON c.instructor_id = u.id 
                    WHERE c.status = 'approved' AND c.category = '$study_field' LIMIT 6";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $course_id = $row['id'];
                    $enrolled = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id='$user_id' AND course_id='$course_id'")) > 0;
                    $img = !empty($row['image_url']) ? $row['image_url'] : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600';
                    echo '<div class="course-card glass">';
                    echo '  <div class="course-img" style="background-image: url(\'' . $img . '\');"></div>';
                    echo '  <div style="padding:20px; flex-grow:1; display:flex; flex-direction:column;">';
                    echo '      <h4 style="margin:0 0 10px 0;">' . htmlspecialchars($row['title']) . '</h4>';
                    echo '      <p style="font-size:12px; opacity:0.7;">Prof. ' . htmlspecialchars($row['instructor_name']) . '</p>';
                    echo '      <a href="enroll.php?id=' . $course_id . '" class="btn-action">' . ($enrolled ? 'View Course' : 'Enroll Now') . '</a>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo "<p style='opacity:0.6;'>No courses found in your field yet.</p>";
            }
            ?>
        </div>
    </main>
    <?php include 'chatbot_widget.php'; ?>
</body>
</html>