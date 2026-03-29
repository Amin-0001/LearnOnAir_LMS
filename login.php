<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$email = mysqli_real_escape_string($conn, $_POST['login_email']);
$password_input = trim($_POST['login_pass']);
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $db_password = $row['password'];
    $auth_success = false;
    // Check if new hash match, else see if it's an old plain-text database entry and auto-upgrade it!
    if (password_verify($password_input, $db_password)) {
        $auth_success = true;
    } elseif ($password_input === $db_password) {
        $auth_success = true;
        // Auto Upgrade DB record to secure hash silently!!
        $new_hash = password_hash($password_input, PASSWORD_DEFAULT);
        $update_id = $row['id'];
        mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id='$update_id'");
    }
    if ($auth_success) {
        // Set Core Session Variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = isset($row['name']) ? $row['name'] : $row['username'];
        $_SESSION['role'] = $row['role'];
        // Capture the Field of Interest from the database!
        $_SESSION['study_field'] = isset($row['study_field']) ? $row['study_field'] : 'general';
        // Route the user based on their role
        // ... inside your successful login check ...
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        // Route EVERYONE to the master index page
        header("Location: index.php");
        exit();
    } // End of auth_success
} // End of num_rows > 0
// If it reaches here, auth failed or user not found
echo "<h3 style='color:darkred; text-align:center; margin-top:50px;'>Wrong Email or Password!</h3>";
echo "<p style='text-align:center;'><a href='login.html' style='display:inline-block; background:#ffb800; color:#2a0845; padding:10px 20px; text-decoration:none; border-radius:50px; font-weight:bold;'> Try Again</a></p>";
?>