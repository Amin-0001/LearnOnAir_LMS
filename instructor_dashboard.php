<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
$instructor_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Instructor';
if (isset($_POST['add_course'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $image_path = NULL;
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == 0) {
        $target_dir = "uploads/course_images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $clean_img_name = time() . "_cover_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES["course_image"]["name"]));
        $image_path = $target_dir . $clean_img_name;
        move_uploaded_file($_FILES["course_image"]["tmp_name"], $image_path);
    }
    $sql = "INSERT INTO courses (title, description, category, image_url, instructor_id, status) 
            VALUES ('$title', '$description', '$category', '$image_path', '$instructor_id', 'pending')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Course Created Successfully!'); window.location='instructor_dashboard.php';</script>";
    }
}
if (isset($_GET['delete_course'])) {
    $del_course_id = intval($_GET['delete_course']);
    $course_query = "SELECT image_url FROM courses WHERE id = '$del_course_id'";
    $course_res = mysqli_query($conn, $course_query);
    $course_data = mysqli_fetch_assoc($course_res);
    if ($course_data['image_url'] && file_exists($course_data['image_url'])) {
        unlink($course_data['image_url']);
    }
    $file_query = "SELECT url, thumbnail_url FROM lessons WHERE course_id = '$del_course_id'";
    $file_res = mysqli_query($conn, $file_query);
    while ($file_row = mysqli_fetch_assoc($file_res)) {
        if ($file_row['url'] && file_exists($file_row['url'])) {
            unlink($file_row['url']);
        }
        if ($file_row['thumbnail_url'] && file_exists($file_row['thumbnail_url'])) {
            unlink($file_row['thumbnail_url']);
        }
    }
    $mat_query = "SELECT file_path FROM course_materials WHERE course_id = '$del_course_id'";
    $mat_res = mysqli_query($conn, $mat_query);
    while ($mat_row = mysqli_fetch_assoc($mat_res)) {
        if ($mat_row['file_path'] && file_exists($mat_row['file_path'])) {
            unlink($mat_row['file_path']);
        }
    }
    mysqli_query($conn, "DELETE FROM course_materials WHERE course_id = '$del_course_id'");
    mysqli_query($conn, "DELETE FROM lessons WHERE course_id = '$del_course_id'");
    mysqli_query($conn, "DELETE FROM courses WHERE id = '$del_course_id' AND instructor_id = '$instructor_id'");
    echo "<script>alert('Course completely deleted.'); window.location='instructor_dashboard.php';</script>";
}
if (isset($_GET['delete_lesson'])) {
    $del_lesson_id = intval($_GET['delete_lesson']);
    $verify_sql = "SELECT l.url, l.thumbnail_url FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.id = '$del_lesson_id' AND c.instructor_id = '$instructor_id'";
    $verify_res = mysqli_query($conn, $verify_sql);
    if (mysqli_num_rows($verify_res) > 0) {
        $lesson_data = mysqli_fetch_assoc($verify_res);
        if ($lesson_data['url'] && file_exists($lesson_data['url'])) {
            unlink($lesson_data['url']);
        }
        if ($lesson_data['thumbnail_url'] && file_exists($lesson_data['thumbnail_url'])) {
            unlink($lesson_data['thumbnail_url']);
        }
        mysqli_query($conn, "DELETE FROM lessons WHERE id = '$del_lesson_id'");
        echo "<script>window.location='instructor_dashboard.php';</script>";
    }
}
if (isset($_GET['delete_material'])) {
    $del_mat_id = intval($_GET['delete_material']);
    $verify_sql = "SELECT m.file_path FROM course_materials m JOIN courses c ON m.course_id = c.id WHERE m.id = '$del_mat_id' AND c.instructor_id = '$instructor_id'";
    $verify_res = mysqli_query($conn, $verify_sql);
    if (mysqli_num_rows($verify_res) > 0) {
        $mat_data = mysqli_fetch_assoc($verify_res);
        if ($mat_data['file_path'] && file_exists($mat_data['file_path'])) {
            unlink($mat_data['file_path']);
        }
        mysqli_query($conn, "DELETE FROM course_materials WHERE id = '$del_mat_id'");
        echo "<script>window.location='instructor_dashboard.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">Instructor Workspace</div>
            <div class="header-actions">
                <a href="index.php" class="home-link">🏠 Back to Home</a>
                <div class="user-profile glass">
                    <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <span>Prof. <?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
        <div class="dashboard-grid">
            <div class="left-column">
                <div class="form-card glass">
                    <h3>+ Create New Course</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="input-group"><label>Course Title</label><input type="text" name="title" required>
                        </div>
                        <div class="input-group">
                            <label>Course Category</label>
                            <select name="category" required>
                                <option value="" disabled selected>Select Category</option>
                                <option value="data_science">Data Science</option>
                                <option value="data_mining">Data Mining</option>
                                <option value="data_analytics">Data Analytics</option>
                                <option value="communication">Communication</option>
                                <option value="interview_prep">Interview Preparation</option>
                                <option value="web_dev">Web Development</option>
                                <option value="business">Business & Management</option>
                            </select>
                        </div>
                        <div class="input-group"><label>Description</label><textarea name="description" rows="3"
                                required></textarea></div>
                        <div class="input-group">
                            <label>Course Cover Image (JPG, PNG)</label>
                            <input type="file" name="course_image" accept="image/*" required
                                style="width: 100%; color: #1a1638;">
                        </div>
                        <button type="submit" name="add_course" class="btn-submit">Publish Course Shell</button>
                    </form>
                </div>
            </div>
            <div class="course-list-card glass" style="align-self: start;">
                <h3>My Course Library</h3>
                <?php
                $sql = "SELECT * FROM courses WHERE instructor_id = '$instructor_id' ORDER BY id DESC";
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status_class = ($row['status'] == 'approved') ? 'badge-approved' : 'badge-pending';
                        $current_course_id = $row['id'];
                        $display_image = $row['image_url'] ? $row['image_url'] : 'https://via.placeholder.com/60/2a0845/ffb800?text=No+Img';
                        echo '<div class="course-item">';
                        echo '  <div class="course-header">';
                        echo '      <div class="course-header-left">';
                        echo '          <img src="' . htmlspecialchars($display_image) . '" class="course-thumb-preview" alt="Cover">';
                        echo '          <div>';
                        echo '              <h4>' . htmlspecialchars($row['title']) . '</h4>';
                        echo '              <div style="font-size:12px; opacity:0.8;">Category: ' . ucwords(str_replace('_', ' ', $row['category'])) . '</div>';
                        echo '          </div>';
                        echo '      </div>';
                        echo '      <div style="display: flex; align-items: center;"><div class="badge ' . $status_class . '">' . strtoupper($row['status']) . '</div>';
                        echo '      <a href="instructor_dashboard.php?delete_course=' . $current_course_id . '" class="btn-delete-course" onclick="return confirm(\'Delete course and ALL files?\');">✖ Delete</a></div></div>';
                        $lesson_sql = "SELECT id, title, url FROM lessons WHERE course_id = '$current_course_id' ORDER BY id ASC";
                        $lesson_res = mysqli_query($conn, $lesson_sql);
                        echo '<div class="mini-lesson-list"><div class="section-label" style="color:#007bb5;">Video Lessons</div>';
                        while ($l_row = mysqli_fetch_assoc($lesson_res)) {
                            echo '<div class="mini-item"><span style="color:#007bb5;">▶ ' . htmlspecialchars($l_row['title']) . '</span>';
                            echo '<div><a href="' . htmlspecialchars($l_row['url']) . '" target="_blank" class="file-link">View Video</a><a href="instructor_dashboard.php?delete_lesson=' . $l_row['id'] . '" class="btn-delete-lesson">🗑️</a></div></div>';
                        }
                        echo '</div>';
                        $mat_sql = "SELECT id, title, file_path FROM course_materials WHERE course_id = '$current_course_id' ORDER BY id ASC";
                        $mat_res = mysqli_query($conn, $mat_sql);
                        echo '<div class="mini-lesson-list"><div class="section-label" style="color:#ffb800;">Study Materials</div>';
                        while ($m_row = mysqli_fetch_assoc($mat_res)) {
                            echo '<div class="mini-item"><span style="color:#ffb800;">📄 ' . htmlspecialchars($m_row['title']) . '</span>';
                            echo '<div><a href="' . htmlspecialchars($m_row['file_path']) . '" target="_blank" class="file-link" download>Download</a><a href="instructor_dashboard.php?delete_material=' . $m_row['id'] . '" class="btn-delete-lesson">🗑️</a></div></div>';
                        }
                        echo '</div></div>';
                    }
                } else {
                    echo "<p style='opacity: 0.7;'>You haven't uploaded any courses yet.</p>";
                }
                ?>
            </div>
        </div>
    </main>
</body>

</html>