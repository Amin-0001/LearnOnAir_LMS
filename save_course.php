<?php
$conn = mysqli_connect("localhost", "root", "", "lms_db");
$title = $_POST['course_title'];
$desc = $_POST['course_desc'];
$url = $_POST['course_url'];
$filename = $_FILES["course_file"]["name"];
$tempname = $_FILES["course_file"]["tmp_name"];
$folder = "uploads/" . $filename;
if (!empty($filename)) {
    move_uploaded_file($tempname, $folder);
} else {
    $folder = "";
}
$sql = "INSERT INTO courses (title, description, url, material) VALUES ('$title', '$desc', '$url', '$folder')";
if (mysqli_query($conn, $sql)) {
    echo "<center>Course added! <a href='dashboard.php'>Back to Dashboard</a></center>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>