<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') { 
    header("Location: login.html"); 
    exit(); 
}
$instructor_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Instructor';
if (isset($_POST['add_lesson'])) {
    $course_id = intval($_POST['course_id']);
    $lesson_title = mysqli_real_escape_string($conn, $_POST['lesson_title']);
    $video_dir = "uploads/videos/";
    if (!is_dir($video_dir)) { mkdir($video_dir, 0777, true); }
    $clean_vid_name = time() . "_vid_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES["video_file"]["name"]));
    $target_video = $video_dir . $clean_vid_name;
    $thumb_dir = "uploads/lesson_thumbnails/";
    if (!is_dir($thumb_dir)) { mkdir($thumb_dir, 0777, true); }
    $target_thumb = NULL;
    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] == 0) {
        $clean_thumb_name = time() . "_thumb_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES["thumbnail_file"]["name"]));
        $target_thumb = $thumb_dir . $clean_thumb_name;
        move_uploaded_file($_FILES["thumbnail_file"]["tmp_name"], $target_thumb);
    }
    if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_video)) {
        $sql = "INSERT INTO lessons (course_id, title, url, thumbnail_url) VALUES ('$course_id', '$lesson_title', '$target_video', '$target_thumb')";
        if (mysqli_query($conn, $sql)) {
            $course_query = mysqli_query($conn, "SELECT title FROM courses WHERE id='$course_id'");
            $course_name = mysqli_fetch_assoc($course_query)['title'];
            $alert_message = "New Lesson Added: '" . $lesson_title . "' is now available in " . $course_name;
            $alert_link = "course_details.php?id=" . $course_id;
            $students_sql = "SELECT user_id FROM enrollments WHERE course_id='$course_id'";
            $students_res = mysqli_query($conn, $students_sql);
            while($student = mysqli_fetch_assoc($students_res)) {
                $student_id = $student['user_id'];
                mysqli_query($conn, "INSERT INTO notifications (user_id, message, action_link) VALUES ('$student_id', '$alert_message', '$alert_link')");
            }
            echo "<script>alert('Video Uploaded & Students Notified!'); window.location='instructor_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Error uploading video. File might be too large. Check your XAMPP settings!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Video - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="user-profile glass">
                <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <span>Prof. <?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
        <div class="form-card glass">
            <h3>+ Upload Video Lesson</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label>Select Your Course</label>
                    <select name="course_id" required>
                        <option value="" disabled selected>Choose a course...</option>
                        <?php
                        $course_sql = "SELECT id, title FROM courses WHERE instructor_id = '$instructor_id'";
                        $course_res = mysqli_query($conn, $course_sql);
                        while($row = mysqli_fetch_assoc($course_res)) {
                            echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['title']).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Lesson Title</label>
                    <input type="text" name="lesson_title" placeholder="e.g. Chapter 1: Introduction" required>
                </div>
                <div class="input-group">
                    <label>Choose Video File (MP4, WEBM)</label>
                    <input type="file" name="video_file" accept="video/mp4,video/webm" required style="width:100%; color: #1a1638;">
                </div>
                <div class="input-group">
                    <label>Video Thumbnail Image (Optional)</label>
                    <input type="file" name="thumbnail_file" accept="image/*" style="width:100%; color: #1a1638;">
                </div>
                <button type="submit" name="add_lesson" class="btn-submit">Upload Video & Notify Students</button>
            </form>
        </div>
    </main>
</body>
</html>