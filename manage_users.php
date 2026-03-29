<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check: ONLY Admins Allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}
$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrator';
// --- HANDLE USER DELETION ---
if (isset($_GET['delete_user'])) {
    $del_id = intval($_GET['delete_user']);
    // For a college project, a direct delete is fine. In a real SaaS, you would "suspend" 
    // the account to preserve financial records in the payments table.
    $sql = "DELETE FROM users WHERE id = '$del_id' AND role != 'admin'"; 
    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('User successfully removed from the platform.'); window.location='manage_users.php';</script>";
    } else {
        echo "<script>alert('Cannot delete user. They have associated financial records.'); window.location='manage_users.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users & Finances - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">Network Administration</div>
            <div class="user-profile glass">
                <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                <span><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>
        <div class="table-card glass" style="border-top: 4px solid #ffb800;">
            <h3 style="color: #d97706;">Recent Revenue & Enrollments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Student Name</th>
                        <th>Course Purchased</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // COMPLEX QUERY: Joining Payments with Users and Courses
                    $txn_sql = "SELECT p.*, u.username, c.title 
                                FROM payments p 
                                JOIN users u ON p.user_id = u.id 
                                JOIN courses c ON p.course_id = c.id 
                                ORDER BY p.id DESC LIMIT 10";
                    $txn_result = mysqli_query($conn, $txn_sql);
                    if (mysqli_num_rows($txn_result) > 0) {
                        while ($txn = mysqli_fetch_assoc($txn_result)) {
                            $date = date("M d, Y h:i A", strtotime($txn['created_at']));
                            echo '<tr>';
                            echo '  <td style="font-family: monospace; color: rgba(0,0,0,0.6);">' . htmlspecialchars($txn['transaction_id']) . '</td>';
                            echo '  <td><strong>' . htmlspecialchars($txn['username']) . '</strong></td>';
                            echo '  <td>' . htmlspecialchars($txn['title']) . '</td>';
                            echo '  <td><strong style="color: #d97706;">₹' . htmlspecialchars($txn['amount']) . '</strong></td>';
                            echo '  <td><span class="badge-success">Paid</span></td>';
                            echo '  <td style="font-size: 12px; opacity: 0.8;">' . $date . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align:center; padding: 20px; opacity:0.7;">No financial transactions recorded yet.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="table-card glass" style="border-top: 4px solid #007bb5;">
            <h3 style="color: #007bb5;">Registered Network Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email Address</th>
                        <th>Account Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all users except the admin
                    $user_sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC";
                    $user_result = mysqli_query($conn, $user_sql);
                    if (mysqli_num_rows($user_result) > 0) {
                        while ($user = mysqli_fetch_assoc($user_result)) {
                            $role_badge = ($user['role'] == 'student') ? '<span class="badge-student">Student</span>' : '<span class="badge-teacher">Instructor</span>';
                            echo '<tr>';
                            echo '  <td style="opacity: 0.6;">#' . $user['id'] . '</td>';
                            echo '  <td><strong>' . htmlspecialchars($user['username']) . '</strong></td>';
                            echo '  <td>' . htmlspecialchars($user['email']) . '</td>';
                            echo '  <td>' . $role_badge . '</td>';
                            echo '  <td>';
                            echo '      <a href="manage_users.php?delete_user=' . $user['id'] . '" class="btn-delete" onclick="return confirm(\'Are you sure you want to remove this user from the platform?\');">Remove Account</a>';
                            echo '  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding: 30px; opacity:0.7;">No users registered yet.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>