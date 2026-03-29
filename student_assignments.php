<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$enroll_check = mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id='$user_id' AND course_id='$course_id'");
if (mysqli_num_rows($enroll_check) == 0 && $course_id > 0) {
    echo "<script>alert('You must enroll first!'); window.location='student_dashboard.php';</script>";
    exit();
}
$course_name = "All Assignments";
if ($course_id > 0) {
    $c_q = mysqli_query($conn, "SELECT title FROM courses WHERE id='$course_id'");
    $course_name = mysqli_fetch_assoc($c_q)['title'];
}
// Handle Student Submission Upload
if (isset($_POST['submit_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $target_dir = "uploads/assignments/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $clean_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES["assignment_file"]["name"]));
        $file_path = $target_dir . $clean_file_name;
        move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $file_path);
        // Let's see if student already submitted (allow resubmission by updating)
        $sub_check = mysqli_query($conn, "SELECT id FROM assignment_submissions WHERE assignment_id='$assignment_id' AND student_id='$user_id'");
        if (mysqli_num_rows($sub_check) > 0) {
            $sql = "UPDATE assignment_submissions SET file_path='$file_path', submitted_at=CURRENT_TIMESTAMP WHERE assignment_id='$assignment_id' AND student_id='$user_id'";
        } else {
            $sql = "INSERT INTO assignment_submissions (assignment_id, student_id, file_path) VALUES ('$assignment_id', '$user_id', '$file_path')";
        }
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('File successfully submitted for review!'); window.location='student_assignments.php?course_id=$course_id';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Assignments - Learn On Air</title>
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
            <h2 style="margin:0;">My Assignments</h2>
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
            <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 20px;">
                <div>
                    <h1 style="margin: 0; font-size: 24px;">
                        <?php echo htmlspecialchars($course_name); ?> - Actions
                    </h1>
                    <p style="margin: 5px 0 0 0; opacity: 0.7;">Submit your practical work and projects here for grading.
                    </p>
                </div>
                <?php if($course_id > 0): ?>
                    <a href="course_details.php?id=<?php echo $course_id; ?>" class="back-link">← Course</a>
                <?php endif; ?>
            </div>
            <div class="assignments-list">
            <?php
            // Fetch assignments for this course or all enrolled courses
            if ($course_id > 0) {
                $sql = "SELECT * FROM assignments WHERE course_id='$course_id' ORDER BY due_date ASC";
            } else {
                $sql = "SELECT a.*, c.title as c_title FROM assignments a JOIN enrollments e ON a.course_id = e.course_id JOIN courses c ON a.course_id = c.id WHERE e.user_id='$user_id' ORDER BY a.due_date ASC";
            }
            $res = mysqli_query($conn, $sql);
            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $assignment_id = $row['id'];
                    $due_date = date("M d, Y", strtotime($row['due_date']));
                    // Check if current user submitted this
                    $sub_sql = "SELECT * FROM assignment_submissions WHERE assignment_id='$assignment_id' AND student_id='$user_id'";
                    $sub_res = mysqli_query($conn, $sub_sql);
                    $has_submitted = mysqli_num_rows($sub_res) > 0;
                    $sub_data = $has_submitted ? mysqli_fetch_assoc($sub_res) : null;
                    $grade_display = "";
                    if ($has_submitted) {
                        if (!empty($sub_data['grade'])) {
                            $grade_display = "<span class='status-badge status-graded'>Graded: " . htmlspecialchars($sub_data['grade']) . "</span>";
                        } else {
                            $grade_display = "<span class='status-badge status-submitted'>Submitted - Waiting for Review</span>";
                        }
                    }
                    $course_label = ($course_id == 0) ? "<small style='color:#007bb5; display:block; margin-bottom:5px;'>" . htmlspecialchars($row['c_title']) . "</small>" : "";
                    echo '<div class="assignment-card">';
                    echo '  <span class="due-date">Due: ' . $due_date . '</span>';
                    echo $course_label;
                    echo '  <h3 style="margin:0 0 10px 0; color: #1a1638;">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '  <p style="opacity:0.8; font-size:14px; margin:0 0 15px 0;">' . nl2br(htmlspecialchars($row['description'])) . '</p>';
                    if ($has_submitted) {
                        echo $grade_display;
                        echo ' <span style="font-size:12px; margin-left:10px; opacity:0.6;"><a href="' . htmlspecialchars($sub_data['file_path']) . '" target="_blank" style="color:#ffb800; text-decoration:underline;">View Submission</a> (' . date("M d, H:i", strtotime($sub_data['submitted_at'])) . ')</span>';
                    }
                    // Upload Form
                    echo '  <form method="POST" enctype="multipart/form-data" class="upload-section">';
                    echo '      <input type="hidden" name="assignment_id" value="' . $assignment_id . '">';
                    echo '      <div>';
                    echo '          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px; color:#007bb5;">' . ($has_submitted ? 'Resubmit File (PDF/ZIP)' : 'Upload Work (PDF/ZIP)') . '</label>';
                    echo '          <input type="file" name="assignment_file" accept=".pdf,.zip,.rar,.docx" required style="outline:none;">';
                    echo '      </div>';
                    echo '      <button type="submit" name="submit_assignment" class="btn-submit">' . ($has_submitted ? 'Re-Submit' : 'Submit Assignment') . '</button>';
                    echo '  </form>';
                    echo '</div>';
                }
            } else {
                echo "<p style='text-align:center; padding: 40px; opacity:0.6; font-size:18px;'>No assignments are scheduled for this course yet. Relax! ☕</p>";
            }
            ?>
        </div>
    </main>
</body>
</html>