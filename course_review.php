<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id == 0) {
    echo "<script>alert('Invalid course!'); window.location='student_dashboard.php';</script>";
    exit();
}
// Fetch Course info
$course_req = mysqli_query($conn, "SELECT title FROM courses WHERE id='$course_id'");
if (mysqli_num_rows($course_req) == 0) {
    die("Course not found.");
}
$course_title = mysqli_fetch_assoc($course_req)['title'];
// Did they already review?
$check_sql = "SELECT * FROM course_reviews WHERE user_id='$user_id' AND course_id='$course_id'";
$check_res = mysqli_query($conn, $check_sql);
$existing_review = mysqli_fetch_assoc($check_res);
if (isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    if ($existing_review) {
        // Update review
        $sql = "UPDATE course_reviews SET rating='$rating', review_text='$review_text', created_at=CURRENT_TIMESTAMP WHERE user_id='$user_id' AND course_id='$course_id'";
    } else {
        // Insert review
        $sql = "INSERT INTO course_reviews (course_id, user_id, rating, review_text) VALUES ('$course_id', '$user_id', '$rating', '$review_text')";
    }
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Thank you for reviewing! Your feedback helps other students.'); window.location='course_details.php?id=$course_id';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Course -
        <?php echo htmlspecialchars($course_title); ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">

    <style> body { display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; } </style>
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="review-card glass">
        <h2>Rate your Experience</h2>
        <div class="course-title">
            <?php echo htmlspecialchars($course_title); ?>
        </div>
        <form method="POST">
            <div class="stars">
                <!-- 5 to 1 order because of row-reverse CSS logic for selecting previous siblings on hover -->
                <input type="radio" id="star5" name="rating" value="5" <?php if ($existing_review && $existing_review['rating'] == 5)
                    echo 'checked'; ?> required>
                <label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4" <?php if ($existing_review && $existing_review['rating'] == 4)
                    echo 'checked'; ?>>
                <label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3" <?php if ($existing_review && $existing_review['rating'] == 3)
                    echo 'checked'; ?>>
                <label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2" <?php if ($existing_review && $existing_review['rating'] == 2)
                    echo 'checked'; ?>>
                <label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1" <?php if ($existing_review && $existing_review['rating'] == 1)
                    echo 'checked'; ?>>
                <label for="star1">★</label>
            </div>
            <textarea name="review_text"
                placeholder="What did you think of the instructor and the learning materials? Leave a review!"><?php if ($existing_review)
                    echo htmlspecialchars($existing_review['review_text']); ?></textarea>
            <button type="submit" name="submit_review" class="btn-submit">
                <?php echo $existing_review ? 'Update My Review' : 'Post Review'; ?>
            </button>
        </form>
        <a href="course_details.php?id=<?php echo $course_id; ?>" class="back-link">← Cancel & Return to Course</a>
    </div>
    <?php include 'chatbot_widget.php'; ?>
</body>
</html>