<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Classes - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <div>
                <h1 style="margin: 0; font-size: 28px;">Live Mentorship & Classes</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.7;">Join real-time interacting sessions hosted by your
                    professors.</p>
            </div>
            <a href="student_dashboard.php" class="back-btn">← Dashboard</a>
        </div>
        <div class="live-grid">
            <?php
            // Fetch live classes ONLY for courses the student is enrolled in
            $sql = "SELECT l.*, c.title as c_title, u.username as instructor_name 
                    FROM live_classes l 
                    JOIN courses c ON l.course_id = c.id
                    JOIN enrollments e ON c.id = e.course_id
                    JOIN users u ON c.instructor_id = u.id
                    WHERE e.user_id = '$user_id' 
                    ORDER BY l.scheduled_time ASC";
            $res = mysqli_query($conn, $sql);
            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $ts = strtotime($row['scheduled_time']);
                    $date_str = date("D, d M Y", $ts);
                    $time_str = date("h:i A", $ts);
                    // Determine if it's currently roughly Time to Join
                    $now = time();
                    $diff = $ts - $now;
                    // if it's past starting time, or within exactly 15 mins
                    $is_live = ($diff <= 900 && $diff > -7200); // Live between 15 mins before to 2 hrs after
                    echo '<div class="live-card glass">';
                    if ($is_live) {
                        echo '<span class="live-badge">🔴 LIVE NOW</span>';
                    } else if ($diff > 0) {
                        echo '<span class="upcoming-badge">⏱ UPCOMING</span>';
                    } else {
                        echo '<span class="upcoming-badge" style="color: #ccc; border-color: #ccc;">Past Session</span>';
                    }
                    echo '  <div class="c-title">' . htmlspecialchars($row['c_title']) . '</div>';
                    echo '  <h3 class="topic">' . htmlspecialchars($row['topic']) . '</h3>';
                    echo '  <div class="instructor-tag">';
                    echo '      <div class="instructor-ico">' . strtoupper(substr($row['instructor_name'], 0, 1)) . '</div>';
                    echo '      <span>Prof. ' . htmlspecialchars($row['instructor_name']) . '</span>';
                    echo '  </div>';
                    echo '  <div class="time-box">';
                    echo '      <span class="date">' . $date_str . '</span>';
                    echo '      <span class="time">' . $time_str . '</span>';
                    echo '  </div>';
                    if ($diff < -7200) {
                        echo '  <button class="btn-join" style="pointer-events: none; background: rgba(255, 255, 255, 0.9); color: #ccc;">Session Ended</button>';
                    } else {
                        echo '  <a href="' . htmlspecialchars($row['meeting_link']) . '" target="_blank" class="btn-join">Join Meeting ⮞</a>';
                    }
                    echo '</div>';
                }
            } else {
                echo "<div style='grid-column: 1 / -1; text-align:center; padding: 40px; background: rgba(255,255,255,0.6); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);'>";
                echo "  <div style='font-size: 40px; margin-bottom: 10px;'>🎥</div>";
                echo "  <h3 style='margin:0 0 10px 0;'>No Upcoming Live Classes</h3>";
                echo "  <p style='margin: 0; opacity:0.6; font-size:14px;'>Your enrolled professors haven't scheduled any Zoom or Meet sessions yet.</p>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <?php include 'chatbot_widget.php'; ?>
</body>
</html>