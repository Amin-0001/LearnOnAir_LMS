<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
$teacher_id = $_SESSION['user_id'];
$teacher_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Instructor';
$course_edit_mode = false;
$course_to_edit = ['title'=>'', 'description'=>'', 'url'=>'', 'id'=>''];
$lesson_edit_mode = false;
$lesson_to_edit = ['id'=>'', 'course_id'=>'', 'title'=>'', 'url'=>''];
if (isset($_GET['delete_course'])) {
    $del_id = (int)$_GET['delete_course'];
    $check = mysqli_query($conn, "SELECT * FROM courses WHERE id=$del_id AND instructor_id=$teacher_id");
    if(mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM lessons WHERE course_id=$del_id");
        mysqli_query($conn, "DELETE FROM courses WHERE id=$del_id");
        echo "<script>alert('Course Deleted Successfully'); window.location='instructor_courses.php';</script>";
    }
}
if (isset($_GET['delete_lesson'])) {
    $l_id = (int)$_GET['delete_lesson'];
    $check_l = mysqli_query($conn, "SELECT l.id FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id=$l_id AND c.instructor_id=$teacher_id");
    if(mysqli_num_rows($check_l) > 0) {
        mysqli_query($conn, "DELETE FROM lessons WHERE id=$l_id");
        echo "<script>alert('Lesson Deleted Successfully'); window.location='instructor_courses.php';</script>";
    }
}
if (isset($_GET['edit_course'])) {
    $edit_id = (int)$_GET['edit_course'];
    $res = mysqli_query($conn, "SELECT * FROM courses WHERE id=$edit_id AND instructor_id=$teacher_id");
    if($row = mysqli_fetch_assoc($res)) {
        $course_edit_mode = true;
        $course_to_edit = $row;
        echo "<script>window.onload = function() { document.getElementById('course-section').scrollIntoView(); }</script>";
    }
}
if (isset($_GET['edit_lesson'])) {
    $edit_l_id = (int)$_GET['edit_lesson'];
    $res_l = mysqli_query($conn, "SELECT l.* FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id=$edit_l_id AND c.instructor_id=$teacher_id");
    if($row_l = mysqli_fetch_assoc($res_l)) {
        $lesson_edit_mode = true;
        $lesson_to_edit = $row_l;
        echo "<script>window.onload = function() { document.getElementById('lesson-section').scrollIntoView(); }</script>";
    }
}
if (isset($_POST['save_course'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
        $c_id = $_POST['course_id'];
        $sql = "UPDATE courses SET title='$title', description='$desc', url='$url' WHERE id='$c_id' AND instructor_id='$teacher_id'";
        mysqli_query($conn, $sql);
        echo "<script>alert('Course Updated!'); window.location='instructor_courses.php';</script>";
    } else {
        $sql = "INSERT INTO courses (title, description, url, instructor_id, status) VALUES ('$title', '$desc', '$url', '$teacher_id', 'pending')";
        mysqli_query($conn, $sql);
        echo "<script>alert('Course Added!'); window.location='instructor_courses.php';</script>";
    }
}
if (isset($_POST['save_lesson'])) {
    $c_id = $_POST['course_id'];
    $l_title = mysqli_real_escape_string($conn, $_POST['l_title']);
    $l_url = mysqli_real_escape_string($conn, $_POST['l_url']);
    if (isset($_POST['lesson_id']) && !empty($_POST['lesson_id'])) {
        $l_id = $_POST['lesson_id'];
        $check_owner = mysqli_query($conn, "SELECT id FROM courses WHERE id='$c_id' AND instructor_id='$teacher_id'");
        if(mysqli_num_rows($check_owner) > 0) {
            mysqli_query($conn, "UPDATE lessons SET title='$l_title', url='$l_url', course_id='$c_id' WHERE id='$l_id'");
            echo "<script>alert('Lesson Updated!'); window.location='instructor_courses.php';</script>";
        }
    } else {
        mysqli_query($conn, "INSERT INTO lessons (course_id, title, url) VALUES ('$c_id', '$l_title', '$l_url')");
        echo "<script>alert('Lesson Added!'); window.location='instructor_courses.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Content</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="navbar">
        <div class="logo">Learn<span>OnAir</span></div>
        <div class="nav-links">
            <a href="instructor_dashboard.php">Back to Dashboard</a>
        </div>
    </div>
    <div class="main-container">
        <div id="course-section">
            <div class="section-header">
                <h3>Manage Courses</h3>
            </div>
            <div class="content-box">
                <span class="section-title"><?php echo $course_edit_mode ? "Edit Course Details" : "Create New Course"; ?></span>
                <form method="POST" class="form-grid">
                    <input type="hidden" name="course_id" value="<?php echo $course_to_edit['id']; ?>">
                    <div>
                        <label>Course Title</label>
                        <input type="text" name="title" value="<?php echo $course_to_edit['title']; ?>" required>
                    </div>
                    <div>
                        <label>Description</label>
                        <textarea name="desc" rows="2" required><?php echo $course_to_edit['description']; ?></textarea>
                    </div>
                    <div>
                        <label>Intro Video URL</label>
                        <input type="url" name="url" value="<?php echo $course_to_edit['url']; ?>" required>
                    </div>
                    <button type="submit" name="save_course" style="background:<?php echo $course_edit_mode ? '#f39c12' : '#27ae60'; ?>; width: auto; padding: 10px 30px;">
                        <?php echo $course_edit_mode ? "Update Course" : "Create Course"; ?>
                    </button>
                    <?php if($course_edit_mode): ?>
                        <a href="instructor_courses.php" style="color:#e74c3c; margin-left:15px; font-size:14px;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
                <span class="section-title">Your Existing Courses</span>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $list_res = mysqli_query($conn, "SELECT * FROM courses WHERE instructor_id = $teacher_id ORDER BY id DESC");
                        if(mysqli_num_rows($list_res) > 0) {
                            while($row = mysqli_fetch_assoc($list_res)) {
                                $status_color = ($row['status'] == 'approved') ? 'green' : 'orange';
                                echo "<tr>";
                                echo "<td><strong>".$row['title']."</strong></td>";
                                echo "<td style='color:$status_color; font-weight:bold;'>".ucfirst($row['status'])."</td>";
                                echo "<td>
                                        <a href='instructor_courses.php?edit_course=".$row['id']."' class='btn-action btn-edit'>Edit</a>
                                        <a href='instructor_courses.php?delete_course=".$row['id']."' class='btn-action btn-del' onclick='return confirm(\"Delete course and all its lessons?\")'>Del</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align:center; color:#999; padding:20px;'>No courses found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="lesson-section">
            <div class="section-header" style="background: #3498db;">
                <h3>Manage Lessons</h3>
            </div>
            <div class="content-box">
                <span class="section-title"><?php echo $lesson_edit_mode ? "Edit Lesson Details" : "Add New Lesson"; ?></span>
                <form method="POST" class="form-grid">
                    <input type="hidden" name="lesson_id" value="<?php echo $lesson_to_edit['id']; ?>">
                    <div>
                        <label>Select Course</label>
                        <select name="course_id" required>
                            <option value="">Choose a course...</option>
                            <?php
                            $c_res = mysqli_query($conn, "SELECT * FROM courses WHERE instructor_id = $teacher_id");
                            while($row = mysqli_fetch_assoc($c_res)) {
                                $selected = ($row['id'] == $lesson_to_edit['course_id']) ? 'selected' : '';
                                echo "<option value='".$row['id']."' $selected>".$row['title']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label>Lesson Title</label>
                            <input type="text" name="l_title" value="<?php echo $lesson_to_edit['title']; ?>" required>
                        </div>
                        <div>
                            <label>Video URL</label>
                            <input type="url" name="l_url" value="<?php echo $lesson_to_edit['url']; ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="save_lesson" style="background:<?php echo $lesson_edit_mode ? '#f39c12' : '#3498db'; ?>; width: auto; padding: 10px 30px;">
                        <?php echo $lesson_edit_mode ? "Update Lesson" : "Add Lesson"; ?>
                    </button>
                    <?php if($lesson_edit_mode): ?>
                        <a href="instructor_courses.php" style="color:#e74c3c; margin-left:15px; font-size:14px;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
                <span class="section-title">All Uploaded Lessons</span>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Lesson Title</th>
                                <th>Course</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $l_sql = "SELECT lessons.*, courses.title as course_name 
                                      FROM lessons 
                                      JOIN courses ON lessons.course_id = courses.id 
                                      WHERE courses.instructor_id = $teacher_id 
                                      ORDER BY lessons.id DESC";
                            $l_res = mysqli_query($conn, $l_sql);
                            if(mysqli_num_rows($l_res) > 0) {
                                while($l_row = mysqli_fetch_assoc($l_res)) {
                                    echo "<tr>";
                                    echo "<td>" . $l_row['title'] . "</td>";
                                    echo "<td style='color:#7f8c8d; font-size:13px;'>" . $l_row['course_name'] . "</td>";
                                    echo "<td>
                                            <a href='instructor_courses.php?edit_lesson=".$l_row['id']."' class='btn-action btn-edit'>Edit</a>
                                            <a href='instructor_courses.php?delete_lesson=".$l_row['id']."' class='btn-action btn-del' onclick='return confirm(\"Delete this lesson?\")'>Del</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; color:#999; padding:20px;'>No lessons added yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="footer" style="text-align:center; padding: 20px; color: #aaa;">
        <h3>¶ 2026 This Project Developed by Amin Shuhaib & Abdul Azeez</h3> 
    </div>
</body>
</html>