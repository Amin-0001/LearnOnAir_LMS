<?php
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$username = mysqli_real_escape_string($conn, $_POST['user_name']);
$email = mysqli_real_escape_string($conn, $_POST['user_email']);
$password_plain = $_POST['user_pass'];
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
$study_field = mysqli_real_escape_string($conn, $_POST['study_field']);
$role = mysqli_real_escape_string($conn, $_POST['role']);
$sql = "INSERT INTO users (username, email, password, role, study_field) 
        VALUES ('$username', '$email', '$password_hashed', '$role', '$study_field')";
if (mysqli_query($conn, $sql)) {
    header("Location: login.html");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
mysqli_close($conn);
?>