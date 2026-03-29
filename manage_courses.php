<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
$instructor_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Verify this instructor actually owns this course
$check_sql = "SELECT * FROM courses WHERE id = '$course_id' AND instructor_id = '$instructor_id'";
$check_result = mysqli_query($conn, $check_sql);
if (mysqli_num_rows($check_result) == 0) {
    echo "<script>alert('Invalid Course!'); window.location='instructor_dashboard.php';</script>";
    exit();
}
$course = mysqli_fetch_assoc($check_result);
// --- HANDLE NEW LESSON SUBMISSION ---
if (isset($_POST['add_lesson'])) {
    // UPDATED: Now capturing 'title' and 'url' to match your database
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    // UPDATED: Insert query now uses your exact column names: course_id, title, url
    $sql = "INSERT INTO lessons (course_id, title, url) VALUES ('$course_id', '$title', '$url')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Lesson Added Successfully!'); window.location='manage_course.php?id=$course_id';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons - <?php echo htmlspecialchars($course['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="container">
        <div class="header-box glass">
            <div>
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p>Add and manage video lessons for this course.</p>
            </div>
            <a href="instructor_dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
        <div class="layout-grid">
            <div class="form-card glass">
                <h3>+ Upload Video Lesson</h3>
                <form method="POST">
                    <div class="input-group">
                        <label>Lesson Title</label>
                        <input type="text" name="title" placeholder="e.g. Introduction to PHP Variables" required>
                    </div>
                    <div class="input-group">
                        <label>Video URL (YouTube, Drive, etc.)</label>
                        <input type="url" name="url" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>
                    <button type="submit" name="add_lesson" class="btn-submit">Add Lesson</button>
                </form>
            </div>
            <div class="lessons-card glass">
                <h3>Current Course Content</h3>
                <?php
                // UPDATED: Select query fetches from your table
                $lesson_sql = "SELECT * FROM lessons WHERE course_id = '$course_id' ORDER BY id ASC";
                $lesson_result = mysqli_query($conn, $lesson_sql);
                if (mysqli_num_rows($lesson_result) > 0) {
                    $count = 1;
                    while ($l_row = mysqli_fetch_assoc($lesson_result)) {
                        echo '<div class="lesson-item">';
                        echo '  <div class="lesson-icon">▶</div>';
                        echo '  <div>';
                        // UPDATED: Now displaying 'title' and 'url' from your database
                        echo '      <h4>Lesson ' . $count . ': ' . htmlspecialchars($l_row['title']) . '</h4>';
                        echo '      <a href="' . htmlspecialchars($l_row['url']) . '" target="_blank">Test Video Link</a>';
                        echo '  </div>';
                        echo '</div>';
                        $count++;
                    }
                } else {
                    echo "<p style='opacity: 0.7;'>This course is empty! Add your first video lesson on the left.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>