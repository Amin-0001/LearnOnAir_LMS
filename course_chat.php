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
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
// --- HANDLE NEW MESSAGE & REPLY SUBMISSION ---
if (isset($_POST['send_message']) && $course_id > 0) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $reply_to = isset($_POST['reply_to_id']) && $_POST['reply_to_id'] != '' ? intval($_POST['reply_to_id']) : 'NULL';
    if (!empty(trim($message))) {
        $sql = "INSERT INTO course_chats (course_id, user_id, reply_to_id, message) VALUES ('$course_id', '$user_id', $reply_to, '$message')";
        mysqli_query($conn, $sql);
        header("Location: course_chat.php?course_id=$course_id");
        exit();
    }
}
// --- VERIFY ACCESS & FETCH COURSE INFO ---
$course_title = "";
$course_category = "";
if ($course_id > 0) {
    if ($user_role == 'teacher') {
        $check_access = mysqli_query($conn, "SELECT title, category FROM courses WHERE id='$course_id' AND instructor_id='$user_id'");
    } else {
        $check_access = mysqli_query($conn, "SELECT c.title, c.category FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE c.id='$course_id' AND e.user_id='$user_id'");
    }
    if (mysqli_num_rows($check_access) > 0) {
        $course_data = mysqli_fetch_assoc($check_access);
        $course_title = $course_data['title'];
        $course_category = $course_data['category'];
    } else {
        echo "<script>alert('You do not have access to this course chat.'); window.location='course_chat.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Discussion - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <?php if ($course_id == 0): ?>
            <div class="top-header">
                <div class="page-title">Class Discussion Rooms</div>
            </div>
            <p style="opacity: 0.8; margin-bottom: 30px;">Select a course below to join the live discussion.</p>
            <div class="select-grid">
                <?php
                if ($user_role == 'teacher') {
                    $list_sql = "SELECT id, title, category FROM courses WHERE instructor_id = '$user_id'";
                } else {
                    $list_sql = "SELECT c.id, c.title, c.category FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = '$user_id'";
                }
                $list_res = mysqli_query($conn, $list_sql);
                if (mysqli_num_rows($list_res) > 0) {
                    while ($row = mysqli_fetch_assoc($list_res)) {
                        echo '<div class="select-card glass" onclick="window.location=\'course_chat.php?course_id='.$row['id'].'\'">';
                        echo '  <h4>' . htmlspecialchars($row['title']) . '</h4>';
                        echo '  <p style="font-size: 12px; opacity: 0.7; margin: 0 0 15px 0;">Category: ' . ucwords(str_replace('_', ' ', $row['category'])) . '</p>';
                        echo '  <div class="btn-enter-chat">Join Chat Room</div>';
                        echo '</div>';
                    }
                } else {
                    echo "<p style='opacity: 0.6;'>You don't have any active courses yet.</p>";
                }
                ?>
            </div>
        <?php else: ?>
            <div class="chat-container glass">
                <div class="chat-header">
                    <div>
                        <h2>💬 <?php echo htmlspecialchars($course_title); ?></h2>
                        <div style="font-size: 13px; color: #007bb5; margin-top: 5px; font-weight: 500;">
                            Category: <?php echo ucwords(str_replace('_', ' ', $course_category)); ?>
                        </div>
                    </div>
                    <a href="course_chat.php" class="btn-leave">Leave Room</a>
                </div>
                <div class="chat-messages" id="chatBox">
                    <?php
                    $chat_sql = "SELECT m.*, u.username, u.role, u.name, 
                                        r.message AS reply_msg, ru.name AS reply_name, ru.username AS reply_username
                                 FROM course_chats m 
                                 JOIN users u ON m.user_id = u.id 
                                 LEFT JOIN course_chats r ON m.reply_to_id = r.id
                                 LEFT JOIN users ru ON r.user_id = ru.id
                                 WHERE m.course_id = '$course_id' 
                                 ORDER BY m.created_at ASC";
                    $chat_res = mysqli_query($conn, $chat_sql);
                    if (mysqli_num_rows($chat_res) > 0) {
                        while ($msg = mysqli_fetch_assoc($chat_res)) {
                            $is_self = ($msg['user_id'] == $user_id);
                            $wrapper_class = $is_self ? 'msg-self' : 'msg-other';
                            $display_name = !empty($msg['name']) ? $msg['name'] : $msg['username'];
                            $safe_display_name = addslashes($display_name); 
                            if ($is_self) $display_name = "You";
                            $time = date('M j, g:i a', strtotime($msg['created_at']));
                            $short_msg = substr($msg['message'], 0, 40) . (strlen($msg['message']) > 40 ? '...' : '');
                            $safe_short_msg = addslashes(htmlspecialchars($short_msg));
                            echo '<div class="msg-wrapper ' . $wrapper_class . '">';
                            echo '  <div class="msg-info">';
                            echo '      <strong>' . htmlspecialchars($display_name) . '</strong>';
                            if ($msg['role'] == 'teacher') {
                                echo '      <span class="badge-instructor">Instructor</span>';
                            } else {
                                echo '      <span class="badge-student">Student</span>';
                            }
                            echo '      <span style="font-size:10px; opacity: 0.6;">' . $time . '</span>';
                            echo '      <span class="btn-reply" onclick="startReply('.$msg['id'].', \''.$safe_display_name.'\', \''.$safe_short_msg.'\')">↩ Reply</span>';
                            echo '  </div>';
                            echo '  <div class="msg-bubble">';
                            if (!empty($msg['reply_to_id'])) {
                                $original_author = !empty($msg['reply_name']) ? $msg['reply_name'] : $msg['reply_username'];
                                echo '  <div class="quoted-msg">';
                                echo '      <strong>' . htmlspecialchars($original_author) . '</strong><br>';
                                echo '      ' . htmlspecialchars(substr($msg['reply_msg'], 0, 60)) . (strlen($msg['reply_msg']) > 60 ? '...' : '');
                                echo '  </div>';
                            }
                            echo nl2br(htmlspecialchars($msg['message']));
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div style="text-align: center; margin-top: 50px; opacity: 0.5;">No messages yet. Be the first to start the discussion!</div>';
                    }
                    ?>
                </div>
                <div class="input-wrapper">
                    <div class="reply-indicator" id="replyIndicator">
                        <span>Replying to <strong id="replyUserName"></strong>: <span id="replyTextPreview" style="opacity: 0.8; font-style: italic; margin-left: 5px;"></span></span>
                        <button type="button" onclick="cancelReply()" title="Cancel Reply">✖</button>
                    </div>
                    <form class="chat-input-area" method="POST" action="course_chat.php?course_id=<?php echo $course_id; ?>">
                        <input type="hidden" name="reply_to_id" id="replyToId" value="">
                        <input type="text" name="message" id="chatInputBox" placeholder="Type your question or message here..." required autocomplete="off">
                        <button type="submit" name="send_message" class="btn-send">➤</button>
                    </form>
                </div>
            </div>
            <script>
                var chatBox = document.getElementById("chatBox");
                chatBox.scrollTop = chatBox.scrollHeight;
                function startReply(msgId, userName, msgSnippet) {
                    document.getElementById('replyToId').value = msgId;
                    document.getElementById('replyUserName').innerText = userName;
                    document.getElementById('replyTextPreview').innerText = '"' + msgSnippet + '"';
                    document.getElementById('replyIndicator').style.display = 'flex';
                    document.getElementById('chatInputBox').focus();
                }
                function cancelReply() {
                    document.getElementById('replyToId').value = '';
                    document.getElementById('replyIndicator').style.display = 'none';
                }
            </script>
        <?php endif; ?>
    </main>
</body>
</html>