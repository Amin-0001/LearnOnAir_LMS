<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// 1. Security Check: Only Students Allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';
if (!isset($_GET['course_id'])) {
    echo "<script>alert('No course selected.'); window.location='student_dashboard.php';</script>";
    exit();
}
$course_id = intval($_GET['course_id']);
// 2. Security Check: Verify Enrollment
$enroll_check = mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id='$user_id' AND course_id='$course_id'");
if(mysqli_num_rows($enroll_check) == 0) {
    echo "<script>alert('You must enroll in this course to take the quiz.'); window.location='student_dashboard.php';</script>";
    exit();
}
// 3. Check if they already took this quiz
$attempt_check = mysqli_query($conn, "SELECT * FROM quiz_results WHERE user_id='$user_id' AND course_id='$course_id'");
if(mysqli_num_rows($attempt_check) > 0) {
    $result_data = mysqli_fetch_assoc($attempt_check);
    // Calculate previous percentage
    $percentage = ($result_data['total_questions'] > 0) ? round(($result_data['score'] / $result_data['total_questions']) * 100) : 0;
    
    if ($percentage >= 50) {
        // They already passed
        echo "<script>alert('You have already passed this quiz! Your score: " . $result_data['score'] . "/" . $result_data['total_questions'] . "'); window.location='student_results.php';</script>";
        exit();
    } else {
        // They failed, automatically purge the old failed attempt to allow a fresh retake
        mysqli_query($conn, "DELETE FROM quiz_results WHERE user_id='$user_id' AND course_id='$course_id'");
    }
}
// 4. Fetch Course Title
$course_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT title FROM courses WHERE id='$course_id'"));
$course_title = $course_data['title'];
// 5. Handle Quiz Submission & Grading
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quiz'])) {
    $score = 0;
    $total_questions = 0;
    // Fetch correct answers from DB to compare
    $q_sql = "SELECT id, correct_option FROM quizzes WHERE course_id='$course_id'";
    $q_res = mysqli_query($conn, $q_sql);
    while($row = mysqli_fetch_assoc($q_res)) {
        $total_questions++;
        $q_id = $row['id'];
        // If student answered this question, check if it matches the correct option
        if(isset($_POST['question_'.$q_id]) && $_POST['question_'.$q_id] == $row['correct_option']) {
            $score++;
        }
    }
    // Save Score to Database
    $insert_score = "INSERT INTO quiz_results (user_id, course_id, score, total_questions) VALUES ('$user_id', '$course_id', '$score', '$total_questions')";
    if(mysqli_query($conn, $insert_score)) {
        echo "<script>alert('Quiz Submitted! You scored $score out of $total_questions.'); window.location='student_results.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Quiz - <?php echo htmlspecialchars($course_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="top-bar">
        <a href="student_dashboard.php?course_id=<?php echo $course_id; ?>" class="back-link">← Back to Course</a>
        <div style="font-weight: 600;">Student: <?php echo htmlspecialchars($user_name); ?></div>
    </div>
    <div class="quiz-container glass">
        <div class="quiz-header">
            <h1>Final Assessment</h1>
            <p style="opacity: 0.8; margin: 0;">Course: <?php echo htmlspecialchars($course_title); ?></p>
        </div>
        <form method="POST" action="">
            <?php
            // Fetch all questions for this course
            $q_sql = "SELECT * FROM quizzes WHERE course_id='$course_id' ORDER BY id ASC";
            $q_res = mysqli_query($conn, $q_sql);
            if(mysqli_num_rows($q_res) > 0) {
                $q_num = 1;
                while($question = mysqli_fetch_assoc($q_res)) {
                    $q_id = $question['id'];
                    echo '<div class="question-card">';
                    echo '  <div class="question-text">' . $q_num . '. ' . htmlspecialchars($question['question_text']) . '</div>';
                    // Options
                    echo '  <label class="option-label"><input type="radio" name="question_'.$q_id.'" value="A" required> ' . htmlspecialchars($question['option_a']) . '</label>';
                    echo '  <label class="option-label"><input type="radio" name="question_'.$q_id.'" value="B"> ' . htmlspecialchars($question['option_b']) . '</label>';
                    echo '  <label class="option-label"><input type="radio" name="question_'.$q_id.'" value="C"> ' . htmlspecialchars($question['option_c']) . '</label>';
                    echo '  <label class="option-label"><input type="radio" name="question_'.$q_id.'" value="D"> ' . htmlspecialchars($question['option_d']) . '</label>';
                    echo '</div>';
                    $q_num++;
                }
                echo '<button type="submit" name="submit_quiz" class="btn-submit" onclick="return confirm(\'Are you sure you want to submit your final answers?\');">Submit Exam</button>';
            } else {
                echo '<div class="empty-state">Your instructor has not added any quiz questions for this course yet.</div>';
            }
            ?>
        </form>
    </div>
</body>
</html>