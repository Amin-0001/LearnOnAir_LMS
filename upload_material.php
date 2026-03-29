<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') { header("Location: login.html"); exit(); }
$instructor_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Instructor';
if (isset($_POST['add_material'])) {
    $course_id = intval($_POST['course_id']);
    $material_title = mysqli_real_escape_string($conn, $_POST['material_title']);
    $target_dir = "uploads/materials/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    $clean_file_name = time() . "_doc_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES["material_file"]["name"]));
    $target_file = $target_dir . $clean_file_name;
    if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file)) {
        mysqli_query($conn, "INSERT INTO course_materials (course_id, title, file_path) VALUES ('$course_id', '$material_title', '$target_file')");
        echo "<script>alert('Study Material Uploaded!'); window.location='instructor_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error uploading document.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Material - Learn On Air</title>
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
            <h3>+ Upload Study Material</h3>
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
                    <label>Document Title</label>
                    <input type="text" name="material_title" required>
                </div>
                <div class="input-group">
                    <label>Choose Document (PDF, ZIP)</label>
                    <input type="file" name="material_file" accept=".pdf,.doc,.docx,.zip" required style="width:100%; color: #1a1638;">
                </div>
                <button type="submit" name="add_material" class="btn-submit">Upload Document</button>
            </form>
        </div>
    </main>
</body>
</html>