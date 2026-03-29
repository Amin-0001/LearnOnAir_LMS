<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check: ONLY Admins Allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}
$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrator';
// --- 1. HANDLE COURSE APPROVAL & SET INITIAL PRICE ---
if (isset($_POST['approve_course'])) {
    $course_id = intval($_POST['course_id']);
    $price = floatval($_POST['price']); // Fetch the price typed by admin
    $sql = "UPDATE courses SET status = 'approved', price = '$price' WHERE id = '$course_id'";
    mysqli_query($conn, $sql);
    echo "<script>alert('Course Approved with a price of ₹$price!'); window.location='admin_dashboard.php';</script>";
}
// --- 2. HANDLE EDITING AN EXISTING COURSE PRICE ---
if (isset($_POST['update_price'])) {
    $course_id = intval($_POST['course_id']);
    $new_price = floatval($_POST['new_price']);
    $sql = "UPDATE courses SET price = '$new_price' WHERE id = '$course_id'";
    mysqli_query($conn, $sql);
    echo "<script>alert('Price successfully updated to ₹$new_price!'); window.location='admin_dashboard.php';</script>";
}
// --- 3. HANDLE COURSE REJECTION (Deletes it) ---
if (isset($_GET['reject'])) {
    $course_id = intval($_GET['reject']);
    $sql = "DELETE FROM courses WHERE id = '$course_id'";
    mysqli_query($conn, $sql);
    echo "<script>alert('Course Rejected and Deleted.'); window.location='admin_dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">Admin Control Panel</div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="user-profile glass">
                    <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($admin_name); ?></span>
                </div>
                <a href="logout.php" class="btn-logout-top glass">⏻ Log Out</a>
            </div>
        </div>
        <div class="stats-grid">
            <?php
            // Quick queries for system stats
            $users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='student'"))['total'];
            $teachers_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='teacher'"))['total'];
            $courses_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE status='approved'"))['total'];
            ?>
            <div class="stat-card glass">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-info">
                    <h3><?php echo $users_count; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-info">
                    <h3><?php echo $teachers_count; ?></h3>
                    <p>Registered Instructors</p>
                </div>
            </div>
            <div class="stat-card glass">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3><?php echo $courses_count; ?></h3>
                    <p>Active Courses</p>
                </div>
            </div>
        </div>
        <div class="table-card glass">
            <h3>Pending Course Approvals</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Title</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Set Price & Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch only pending courses
                    $sql = "SELECT c.*, u.username AS instructor_name 
                            FROM courses c 
                            JOIN users u ON c.instructor_id = u.id 
                            WHERE c.status = 'pending' 
                            ORDER BY c.id DESC";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $display_cat = ucwords(str_replace('_', ' ', $row['category']));
                            echo '<tr>';
                            echo '  <td><strong>' . htmlspecialchars($row['title']) . '</strong></td>';
                            echo '  <td>Prof. ' . htmlspecialchars($row['instructor_name']) . '</td>';
                            echo '  <td><span class="cat-badge">' . htmlspecialchars($display_cat) . '</span></td>';
                            echo '  <td class="action-btns">';
                            // FORM FOR APPROVAL AND PRICE
                            echo '      <form method="POST" style="display:inline-flex; align-items:center; gap:8px; margin:0;">';
                            echo '          <input type="hidden" name="course_id" value="' . $row['id'] . '">';
                            echo '          <div class="price-input-wrapper">';
                            echo '              <span style="color:#007bb5; margin-right:3px;">₹</span>';
                            echo '              <input type="number" name="price" placeholder="0.00" min="0" step="0.01" required>';
                            echo '          </div>';
                            echo '          <button type="submit" name="approve_course" class="btn-approve">✔ Approve</button>';
                            echo '      </form>';
                            echo '      <a href="admin_dashboard.php?reject=' . $row['id'] . '" class="btn-reject" onclick="return confirm(\'Reject and delete this course?\');">✖ Reject</a>';
                            echo '  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center; padding: 30px; opacity:0.7;">No pending courses. You are all caught up! 🎉</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="table-card glass" style="border-top: 4px solid #2ecc71;">
            <h3 style="color: #2ecc71;">Live Courses - Edit Pricing</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Title</th>
                        <th>Instructor</th>
                        <th>Current Price</th>
                        <th>Update Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch only approved courses
                    $sql_approved = "SELECT c.*, u.username AS instructor_name 
                                     FROM courses c 
                                     JOIN users u ON c.instructor_id = u.id 
                                     WHERE c.status = 'approved' 
                                     ORDER BY c.id DESC";
                    $result_approved = mysqli_query($conn, $sql_approved);
                    if (mysqli_num_rows($result_approved) > 0) {
                        while ($row = mysqli_fetch_assoc($result_approved)) {
                            echo '<tr>';
                            echo '  <td><strong>' . htmlspecialchars($row['title']) . '</strong></td>';
                            echo '  <td>Prof. ' . htmlspecialchars($row['instructor_name']) . '</td>';
                            echo '  <td><strong style="color:#007bb5;">₹' . htmlspecialchars($row['price']) . '</strong></td>';
                            echo '  <td class="action-btns">';
                            // FORM FOR UPDATING PRICE
                            echo '      <form method="POST" style="display:inline-flex; align-items:center; gap:8px; margin:0;">';
                            echo '          <input type="hidden" name="course_id" value="' . $row['id'] . '">';
                            echo '          <div class="price-input-wrapper">';
                            echo '              <span style="color:#ffb800; margin-right:3px;">₹</span>';
                            echo '              <input type="number" name="new_price" value="' . $row['price'] . '" min="0" step="0.01" required>';
                            echo '          </div>';
                            echo '          <button type="submit" name="update_price" class="btn-update">Update</button>';
                            echo '      </form>';
                            echo '  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center; padding: 30px; opacity:0.7;">No approved courses on the platform yet.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>