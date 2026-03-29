<?php
// save_progress.php
session_start();
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "lms_db");
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($_SESSION['user_id']) || !isset($data['course_id']) || !isset($data['lesson_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data or session']);
    exit();
}
$user_id = intval($_SESSION['user_id']);
$course_id = intval($data['course_id']);
$lesson_id = intval($data['lesson_id']);
$sql = "INSERT INTO lesson_progress (user_id, course_id, lesson_id) 
        VALUES ('$user_id', '$course_id', '$lesson_id')
        ON DUPLICATE KEY UPDATE completed_at=CURRENT_TIMESTAMP";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}
?>