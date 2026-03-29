<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
$instructor_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Instructor';
// Handle New Assignment Creation
if (isset($_POST['create_assignment'])) {
    $course_id = intval($_POST['course_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $sql = "INSERT INTO assignments (course_id, title, description, due_date) VALUES ('$course_id', '$title', '$description', '$due_date')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Assignment created successfully!'); window.location='manage_assignments.php';</script>";
    }
}
// Handle Grading
if (isset($_POST['grade_submission'])) {
    $submission_id = intval($_POST['submission_id']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $sql = "UPDATE assignment_submissions SET grade = '$grade' WHERE id = '$submission_id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Grade Saved!'); window.location='manage_assignments.php';</script>";
    }
}
// Fetch instructor's courses
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = '$instructor_id'";
$courses_res = mysqli_query($conn, $courses_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assignments - Learn On Air</title>
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
            <div class="page-title">Assignment Workspace</div>
            <a href="index.php" style="color: #007bb5; font-weight: 600;">🏠 Home</a>
        </div>
        <div class="dashboard-grid">
            <!-- Create Assignment Form -->
            <div class="panel glass">
                <h3>Create New Assignment</h3>
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
                        <label>Assignment Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="input-group">
                        <label>Instructions & Rubric</label>
                        <textarea name="description" rows="4" required
                            placeholder="What should the student submit?"></textarea>
                    </div>
                    <div class="input-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" required>
                    </div>
                    <button type="submit" name="create_assignment" class="btn-submit">Publish Assignment</button>
                </form>
            </div>
            <!-- Review Submissions -->
            <div class="panel glass">
                <h3 style="color: #007bb5;">Student Submissions Needed Grading</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Submission</th>
                            <th>Grade Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch submissions for courses taught by this instructor
                        $sub_sql = "SELECT s.*, u.username, a.title as a_title, c.title as c_title 
                                    FROM assignment_submissions s 
                                    JOIN assignments a ON s.assignment_id = a.id
                                    JOIN courses c ON a.course_id = c.id
                                    JOIN users u ON s.student_id = u.id
                                    WHERE c.instructor_id = '$instructor_id'
                                    ORDER BY s.submitted_at DESC";
                        $sub_res = mysqli_query($conn, $sub_sql);
                        if (mysqli_num_rows($sub_res) > 0) {
                            while ($row = mysqli_fetch_assoc($sub_res)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['a_title']) . "<br><small style='opacity:0.6;'>" . htmlspecialchars($row['c_title']) . "</small></td>";
                                echo "<td><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank' class='file-link'>Download Work</a><br><small style='opacity:0.6;'>" . $row['submitted_at'] . "</small></td>";
                                echo "<td>";
                                echo "<form method='POST' style='display:flex; gap:5px;'>";
                                echo "<input type='hidden' name='submission_id' value='" . $row['id'] . "'>";
                                echo "<input type='text' name='grade' value='" . htmlspecialchars($row['grade']) . "' class='grade-input' placeholder='A+ / 95%'>";
                                echo "<button type='submit' name='grade_submission' class='btn-grade'>Save</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; opacity:0.6;'>No submissions to grade right now. 🎉</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>