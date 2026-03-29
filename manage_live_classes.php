<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
$instructor_id = $_SESSION['user_id'];
// Schedule a Live Class
if (isset($_POST['schedule_class'])) {
    $course_id = intval($_POST['course_id']);
    $topic = mysqli_real_escape_string($conn, $_POST['topic']);
    $meeting_link = mysqli_real_escape_string($conn, $_POST['meeting_link']);
    $scheduled_time = mysqli_real_escape_string($conn, $_POST['scheduled_time']);
    $sql = "INSERT INTO live_classes (course_id, topic, meeting_link, scheduled_time) VALUES ('$course_id', '$topic', '$meeting_link', '$scheduled_time')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Live Class Scheduled successfully!'); window.location='manage_live_classes.php';</script>";
    }
}
// Delete Live Class
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM live_classes WHERE id='$id'");
    echo "<script>alert('Session deleted.'); window.location='manage_live_classes.php';</script>";
}
// Fetch instructor's courses
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = '$instructor_id'";
$courses_res = mysqli_query($conn, $courses_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Live Classes - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">Live Interactions</div>
            <a href="index.php" style="color: #007bb5; font-weight: 600;">🏠 Home</a>
        </div>
        <div class="dashboard-grid">
            <div class="panel glass">
                <h3>Schedule a Live Class (Zoom/Meet)</h3>
                <form method="POST">
                    <div class="input-group">
                        <label>Select Course</label>
                        <select name="course_id" required>
                            <option value="">-- Choose Course --</option>
                            <?php while ($c = mysqli_fetch_assoc($courses_res)): ?>
                                <option value="<?php echo $c['id']; ?>">
                                    <?php echo htmlspecialchars($c['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Topic / Agenda</label>
                        <input type="text" name="topic" required placeholder="e.g. Q&A Session - Week 2">
                    </div>
                    <div class="input-group">
                        <label>Meeting Link (Zoom, Meet, Teams)</label>
                        <input type="text" name="meeting_link" required placeholder="https://zoom.us/j/123...">
                    </div>
                    <div class="input-group">
                        <label>Scheduled Date & Time</label>
                        <input type="datetime-local" name="scheduled_time" required>
                    </div>
                    <button type="submit" name="schedule_class" class="btn-submit">Schedule Session</button>
                </form>
            </div>
            <div class="panel glass">
                <h3 style="color: #d97706;">My Scheduled Sessions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>DateTime</th>
                            <th>Course / Topic</th>
                            <th>Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch instructor's live classes
                        $live_sql = "SELECT l.*, c.title as c_title 
                                     FROM live_classes l 
                                     JOIN courses c ON l.course_id = c.id 
                                     WHERE c.instructor_id = '$instructor_id' 
                                     ORDER BY l.scheduled_time ASC";
                        $live_res = mysqli_query($conn, $live_sql);
                        if (mysqli_num_rows($live_res) > 0) {
                            while ($row = mysqli_fetch_assoc($live_res)) {
                                $time_display = date("d M, h:i A", strtotime($row['scheduled_time']));
                                echo "<tr>";
                                echo "<td>" . $time_display . "</td>";
                                echo "<td>" . htmlspecialchars($row['topic']) . "<br><small style='opacity:0.6;'>" . htmlspecialchars($row['c_title']) . "</small></td>";
                                echo "<td><a href='" . htmlspecialchars($row['meeting_link']) . "' target='_blank' class='link-text'>Start Meeting</a></td>";
                                echo "<td><a href='manage_live_classes.php?delete=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Cancel this class?\");'>Cancel</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; opacity:0.6;'>No active scheduled live classes.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>