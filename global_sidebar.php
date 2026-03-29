<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

function is_active($page, $current) {
    return ($page == $current) ? 'active' : '';
}
?>
<nav class="sidebar glass">
    <div class="sidebar-logo">Learn<span>OnAir</span></div>
    <ul class="nav-links">
        <?php if ($role == 'teacher'): ?>
            <li class="<?php echo is_active('instructor_dashboard.php', $current_page); ?>"><a href="instructor_dashboard.php">Dashboard & Courses</a></li>
            <li class="<?php echo is_active('upload_lesson.php', $current_page); ?>"><a href="upload_lesson.php">Upload Lessons (Video)</a></li>
            <li class="<?php echo is_active('upload_material.php', $current_page); ?>"><a href="upload_material.php">Upload Materials (PDF)</a></li>
            <li class="<?php echo is_active('manage_quizzes.php', $current_page); ?>"><a href="manage_quizzes.php">Manage Quizzes</a></li>
            <li class="<?php echo is_active('manage_assignments.php', $current_page); ?>"><a href="manage_assignments.php">Manage Assignments</a></li>
            <li class="<?php echo is_active('manage_elibrary.php', $current_page); ?>"><a href="manage_elibrary.php">E-Library Resources</a></li>
            <li class="<?php echo is_active('manage_live_classes.php', $current_page); ?>"><a href="manage_live_classes.php">Live Classes</a></li>
            <li class="<?php echo is_active('course_chat.php', $current_page); ?>"><a href="course_chat.php">Student Questions</a></li>
            <li class="<?php echo is_active('settings.php', $current_page); ?>"><a href="settings.php">⚙️ Account Settings</a></li>
            <li class="<?php echo is_active('notifications.php', $current_page); ?>"><a href="notifications.php">Notifications</a></li>
        <?php elseif ($role == 'admin'): ?>
            <li class="<?php echo is_active('admin_dashboard.php', $current_page); ?>"><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <li class="<?php echo is_active('manage_users.php', $current_page); ?>"><a href="manage_users.php">Manage Users</a></li>
            <li class="<?php echo is_active('manage_courses.php', $current_page); ?>"><a href="manage_courses.php">Manage Courses</a></li>
            <li class="<?php echo is_active('settings.php', $current_page); ?>"><a href="settings.php">⚙️ Account Settings</a></li>
        <?php else: // student ?>
            <li class="<?php echo is_active('student_dashboard.php', $current_page); ?>"><a href="student_dashboard.php">Dashboard</a></li>
            <li class="<?php echo is_active('my_courses.php', $current_page); ?>"><a href="my_courses.php">My Learning</a></li>
            <li class="<?php echo is_active('student_results.php', $current_page); ?>"><a href="student_results.php">Grades & Results</a></li>
            <li class="<?php echo is_active('student_assignments.php', $current_page); ?>"><a href="student_assignments.php">My Assignments</a></li>
            <li class="<?php echo is_active('live_classes.php', $current_page); ?>"><a href="live_classes.php">Live Classes</a></li>
            <li class="<?php echo is_active('course_chat.php', $current_page); ?>"><a href="course_chat.php">Class Discussion</a></li>
            <li class="<?php echo is_active('settings.php', $current_page); ?>"><a href="settings.php">⚙️ Account Settings</a></li>
            <li class="<?php echo is_active('notifications.php', $current_page); ?>"><a href="notifications.php">Notifications</a></li>
        <?php endif; ?>
    </ul>
    <div class="logout-btn"><a href="logout.php">⏻ Log Out</a></div>
</nav>
