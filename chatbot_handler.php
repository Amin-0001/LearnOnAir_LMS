<?php
// chatbot_handler.php
session_start();
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($data['message'] ?? ''));
$response = "I'm still learning, but I'm here to help! Try asking about **courses**, **certificates**, **assignments**, or **live classes**.";
if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
    $response = "Hi there! I'm your LearnOnAir AI Assistant 🤖. How can I boost your learning journey today?";
} elseif (strpos($message, 'course') !== false) {
    $response = "📚 You can browse all your active courses on your **My Learning** dashboard, or find new ones on the homepage!";
} elseif (strpos($message, 'certificate') !== false) {
    $response = "🏆 To get certified, make sure to complete all video lessons and score well on the final quiz for that course.";
} elseif (strpos($message, 'assignment') !== false) {
    $response = "📝 Assignments are practical tasks from your professor. Check the **My Assignments** tab to upload your work.";
} elseif (strpos($message, 'live class') !== false || strpos($message, 'zoom') !== false) {
    $response = "🎥 Live classes let you interact with mentors in real-time. Check the **Live Classes** tab for any upcoming sessions!";
} elseif (strpos($message, 'grade') !== false || strpos($message, 'result') !== false || strpos($message, 'score') !== false) {
    $response = "📊 You can monitor your academic performance under the **Grades & Results** section on your dashboard.";
} elseif (strpos($message, 'thank') !== false) {
    $response = "You're very welcome! Keep learning and stay curious! ✨";
} elseif (strpos($message, 'payment') !== false || strpos($message, 'fee') !== false) {
    $response = "💳 All payments on LearnOnAir are securely processed. Once paid, courses grant you lifetime access.";
} elseif (strpos($message, 'help') !== false) {
    $response = "I can tell you about: \n- Enrolling in Courses\n- Taking Quizzes\n- Joining Live Classes\n- Downloading Certificates\nWhat do you need?";
}
// Small delay to make it feel like "typing"
usleep(800000);
echo json_encode(['reply' => $response]);
?>