<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
// Determine which dashboard to return to
$role = $_SESSION['role'];
$dashboard_link = "student_dashboard.php";
if ($role == 'teacher')
    $dashboard_link = "instructor_dashboard.php";
if ($role == 'admin')
    $dashboard_link = "admin_dashboard.php";
// Fetch Current User Details
$sql = "SELECT * FROM users WHERE id='$user_id'";
$res = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);
if (isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $study_field = mysqli_real_escape_string($conn, $_POST['study_field']);
    // Check if updating password
    $pass_update_query = "";
    if (!empty($_POST['new_password'])) {
        $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pass_update_query = ", password='$new_pass'";
    }
    $update_sql = "UPDATE users SET username='$username', email='$email', study_field='$study_field' $pass_update_query WHERE id='$user_id'";
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['user_name'] = $username;
        $_SESSION['study_field'] = $study_field;
        echo "<script>alert('Profile updated successfully!'); window.location='settings.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Account Settings - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="settings-container glass">
        <div class="header">
            <div>
                <h1 style="margin: 0; font-size: 28px;">Account Settings</h1>
                <div class="role-badge">
                    <?php echo htmlspecialchars($user['role']); ?> Role
                </div>
            </div>
            <a href="<?php echo $dashboard_link; ?>" class="back-btn">← Dashboard</a>
        </div>
        <form method="POST">
            <div class="input-group">
                <label>Display Name / Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="input-group">
                <label>Primary Field of Interest</label>
                <select name="study_field" required>
                    <option value="general" <?php if ($user['study_field'] == 'general')
                        echo 'selected'; ?>>General
                        Learner</option>
                    <option value="data_science" <?php if ($user['study_field'] == 'data_science')
                        echo 'selected'; ?>>
                        Data Science</option>
                    <option value="data_mining" <?php if ($user['study_field'] == 'data_mining')
                        echo 'selected'; ?>>Data
                        Mining</option>
                    <option value="data_analytics" <?php if ($user['study_field'] == 'data_analytics')
                        echo 'selected'; ?>>Data Analytics</option>
                    <option value="communication" <?php if ($user['study_field'] == 'communication')
                        echo 'selected'; ?>>
                        Communication</option>
                    <option value="interview_prep" <?php if ($user['study_field'] == 'interview_prep')
                        echo 'selected'; ?>>Interview Preparation</option>
                    <option value="web_dev" <?php if ($user['study_field'] == 'web_dev')
                        echo 'selected'; ?>>Web
                        Development</option>
                    <option value="business" <?php if ($user['study_field'] == 'business')
                        echo 'selected'; ?>>Business &
                        Management</option>
                </select>
            </div>
            <div style="border-top: 1px solid rgba(0, 0, 0, 0.1); margin: 30px 0; padding-top: 20px;">
                <h3 style="margin-top:0; font-size:16px;">Change Password</h3>
                <p style="font-size: 12px; opacity: 0.6; margin-top: -10px; margin-bottom: 20px;">Leave blank if you
                    don't want to change your current password.</p>
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="••••••••">
                </div>
            </div>
            <button type="submit" name="update_profile" class="btn-update">Save Profile Changes</button>
        </form>
    </div>
    <?php include 'chatbot_widget.php'; ?>
</body>

</html>