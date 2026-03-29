<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
// --- HANDLE MARK AS READ ---
if (isset($_GET['read'])) {
    $notif_id = intval($_GET['read']);
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = '$notif_id' AND user_id = '$user_id'");
    header("Location: notifications.php");
    exit();
}
// --- HANDLE MARK ALL AS READ ---
if (isset($_GET['read_all'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");
    header("Location: notifications.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">
                🔔 Your Notifications 
            </div>
            <a href="notifications.php?read_all=true" class="btn-mark-all">✔ Mark All as Read</a>
        </div>
        <div class="notif-container">
            <?php
            $notif_sql = "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 50";
            $notif_res = mysqli_query($conn, $notif_sql);
            if (mysqli_num_rows($notif_res) > 0) {
                while ($row = mysqli_fetch_assoc($notif_res)) {
                    $status_class = ($row['is_read'] == 0) ? 'notif-unread glass' : 'notif-read';
                    $time_ago = date('M j, Y - g:i A', strtotime($row['created_at']));
                    echo '<div class="notif-card ' . $status_class . '">';
                    echo '  <div style="display: flex; align-items: center; flex-grow: 1;">';
                    echo '      <div class="notif-icon">📣</div>';
                    echo '      <div class="notif-content">';
                    echo '          <p>' . htmlspecialchars($row['message']) . '</p>';
                    echo '          <div class="notif-time">' . $time_ago . '</div>';
                    echo '      </div>';
                    echo '  </div>';
                    echo '  <div class="notif-actions">';
                    if ($row['action_link'] != '#') {
                        echo '      <a href="' . htmlspecialchars($row['action_link']) . '" class="btn-view">View Detail</a>';
                    }
                    if ($row['is_read'] == 0) {
                        echo '      <a href="notifications.php?read=' . $row['id'] . '" class="btn-check" title="Mark as Read">✔</a>';
                    }
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="glass" style="padding: 40px; text-align: center; border-radius: 15px; opacity: 0.7;">';
                echo '  <h3>All caught up! 🎉</h3>';
                echo '  <p>You have no new notifications.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </main>
</body>
</html>