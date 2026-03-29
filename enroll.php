<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check: Only logged-in students can enroll
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($course_id > 0) {
    // 1. Check if the course actually exists and is approved
    $check_course = mysqli_query($conn, "SELECT id FROM courses WHERE id='$course_id' AND status='approved'");
    if (mysqli_num_rows($check_course) > 0) {
        // 2. Check if the student is ALREADY enrolled to prevent duplicates
        $check_enrollment = mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id='$user_id' AND course_id='$course_id'");
        if (mysqli_num_rows($check_enrollment) == 0) {
            // 3. Insert the new enrollment!
            $sql = "INSERT INTO enrollments (user_id, course_id) VALUES ('$user_id', '$course_id')";
            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Successfully Enrolled!'); window.location='course_details.php?id=$course_id';</script>";
            } else {
                echo "<script>alert('Database Error. Try again.'); window.location='student_dashboard.php';</script>";
            }
        } else {
            // If already enrolled, just take them to the course page
            header("Location: course_details.php?id=$course_id");
            exit();
        }
    } else {
        echo "<script>alert('Invalid or unavailable course.'); window.location='student_dashboard.php';</script>";
    }
} else {
    header("Location: student_dashboard.php");
    exit();
}
?>