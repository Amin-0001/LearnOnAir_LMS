<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// Security Check: ONLY Instructors Allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
$instructor_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Instructor';
// --- HANDLE ADDING A NEW QUESTION ---
if (isset($_POST['add_question'])) {
    $course_id = intval($_POST['course_id']);
    $question = mysqli_real_escape_string($conn, $_POST['question_text']);
    $opt_a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $opt_b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $opt_c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $opt_d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct_option']);
    $sql = "INSERT INTO quizzes (course_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
            VALUES ('$course_id', '$question', '$opt_a', '$opt_b', '$opt_c', '$opt_d', '$correct')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Question added successfully!'); window.location='manage_quizzes.php';</script>";
    } else {
        echo "<script>alert('Error adding question.');</script>";
    }
}
// --- HANDLE DELETING A QUESTION ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // Security check: Make sure this instructor actually owns the course this question belongs to
    $verify_sql = "SELECT q.id FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = '$del_id' AND c.instructor_id = '$instructor_id'";
    if(mysqli_num_rows(mysqli_query($conn, $verify_sql)) > 0) {
        mysqli_query($conn, "DELETE FROM quizzes WHERE id = '$del_id'");
        echo "<script>alert('Question deleted.'); window.location='manage_quizzes.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">Course Evaluations</div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="index.php" style="color: #007bb5; font-weight: 600;">🏠 Back to Home</a>
                <div class="user-profile glass">
                    <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <span>Prof. <?php echo htmlspecialchars($user_name); ?></span>
                </div>
            </div>
        </div>
        <div class="content-grid">
            <div class="form-card glass">
                <h3>+ Add Multiple Choice Question</h3>
                <form method="POST">
                    <div class="input-group">
                        <label>Select Target Course</label>
                        <select name="course_id" required>
                            <option value="" disabled selected>Choose a course...</option>
                            <?php
                            // Fetch courses owned by this instructor
                            $course_sql = "SELECT id, title FROM courses WHERE instructor_id = '$instructor_id'";
                            $course_res = mysqli_query($conn, $course_sql);
                            while($c_row = mysqli_fetch_assoc($course_res)) {
                                echo '<option value="'.$c_row['id'].'">'.htmlspecialchars($c_row['title']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Question Text</label>
                        <textarea name="question_text" rows="3" placeholder="e.g., What does PHP stand for?" required></textarea>
                    </div>
                    <label style="font-size: 14px; opacity: 0.9; margin-bottom: 5px; display: block;">Answer Options</label>
                    <div class="options-grid">
                        <div class="input-group" style="margin-bottom:0;"><input type="text" name="option_a" placeholder="Option A" required></div>
                        <div class="input-group" style="margin-bottom:0;"><input type="text" name="option_b" placeholder="Option B" required></div>
                        <div class="input-group" style="margin-bottom:0;"><input type="text" name="option_c" placeholder="Option C" required></div>
                        <div class="input-group" style="margin-bottom:0;"><input type="text" name="option_d" placeholder="Option D" required></div>
                    </div>
                    <div class="input-group" style="margin-top: 15px;">
                        <label>Correct Option</label>
                        <select name="correct_option" required>
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>
                    </div>
                    <button type="submit" name="add_question" class="btn-submit">Save Question</button>
                </form>
            </div>
            <div class="table-card glass">
                <h3>My Question Bank</h3>
                <?php
                // Fetch all questions for courses owned by this instructor
                $q_sql = "SELECT q.*, c.title AS course_title 
                          FROM quizzes q 
                          JOIN courses c ON q.course_id = c.id 
                          WHERE c.instructor_id = '$instructor_id' 
                          ORDER BY q.id DESC";
                $q_result = mysqli_query($conn, $q_sql);
                if (mysqli_num_rows($q_result) > 0) {
                    while ($q_row = mysqli_fetch_assoc($q_result)) {
                        echo '<div class="question-item">';
                        echo '  <a href="manage_quizzes.php?delete_id=' . $q_row['id'] . '" class="btn-delete" onclick="return confirm(\'Delete this question?\');">🗑️</a>';
                        echo '  <div style="font-size: 11px; color: #d97706; margin-bottom: 5px; font-weight: bold; text-transform: uppercase;">' . htmlspecialchars($q_row['course_title']) . '</div>';
                        echo '  <h4>' . htmlspecialchars($q_row['question_text']) . '</h4>';
                        echo '  <div class="options-list">';
                        echo '      <div><strong>A:</strong> ' . htmlspecialchars($q_row['option_a']) . '</div>';
                        echo '      <div><strong>B:</strong> ' . htmlspecialchars($q_row['option_b']) . '</div>';
                        echo '      <div><strong>C:</strong> ' . htmlspecialchars($q_row['option_c']) . '</div>';
                        echo '      <div><strong>D:</strong> ' . htmlspecialchars($q_row['option_d']) . '</div>';
                        echo '  </div>';
                        echo '  <div class="correct-ans">Correct Answer: ' . $q_row['correct_option'] . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "<p style='opacity: 0.7; text-align: center; padding: 20px;'>No questions added yet. Select a course and create your first quiz question!</p>";
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>