<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
// 1. Security Check: Must be logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// 2. Fetch Course Information & Price
$course_sql = "SELECT c.*, u.username AS instructor_name 
               FROM courses c 
               JOIN users u ON c.instructor_id = u.id 
               WHERE c.id = '$course_id'";
$course_result = mysqli_query($conn, $course_sql);
if (mysqli_num_rows($course_result) == 0) {
    echo "<script>alert('Course not found!'); window.location='student_dashboard.php';</script>";
    exit();
}
$course = mysqli_fetch_assoc($course_result);
// 3. Check if the student has already paid/enrolled
$enroll_check = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id='$user_id' AND course_id='$course_id'");
$is_enrolled = (mysqli_num_rows($enroll_check) > 0);
// --- IF NOT ENROLLED: SHOW PAYMENT PAGE ---
if (!$is_enrolled):
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Enroll in <?php echo htmlspecialchars($course['title']); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    </head>
    <body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
        <div class="blob-1"></div>
        <div class="blob-2"></div>
        <div style="position: absolute; top: 30px; left: 40px; z-index: 100;">
            <a href="student_dashboard.php"
                style="color: #d97706; text-decoration: none; font-weight: bold; font-size: 16px; display: inline-flex; align-items: center; gap: 8px;">
                <span style="background: rgba(255,184,0,0.2); padding: 5px 12px; border-radius: 50px;">←</span> Back to
                Dashboard
            </a>
        </div>
        <div class="glass" style="width: 100%; max-width: 900px; border-radius: 30px; overflow: hidden; display: flex; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
            <div
                style="flex: 1; background-image: url('<?php echo htmlspecialchars($course['image_url']); ?>'); background-size: cover; background-position: center;">
            </div>
            <div style="flex: 1.2; padding: 50px;">
                <h2 style="margin-top: 0; font-size: 32px; font-weight: 700; line-height: 1.2;">
                    <?php echo htmlspecialchars($course['title']); ?>
                </h2>
                <p style="opacity: 0.8; font-size: 14px; margin-bottom: 30px; line-height: 1.6;">
                    <?php echo htmlspecialchars($course['description']); ?>
                </p>
                <div style="display: flex; gap: 15px; margin-bottom: 30px;">
                    <div
                        style="background: rgba(255, 255, 255, 0.85); border-radius: 12px; padding: 15px; text-align: center; flex: 1;">
                        <span style="display: block; font-size: 12px; opacity: 0.7; margin-bottom: 5px;">Instructor</span>
                        <strong style="color: #007bb5;"><?php echo htmlspecialchars($course['instructor_name']); ?></strong>
                    </div>
                    <div
                        style="background: rgba(255, 255, 255, 0.85); border-radius: 12px; padding: 15px; text-align: center; flex: 1;">
                        <span style="display: block; font-size: 12px; opacity: 0.7; margin-bottom: 5px;">Access</span>
                        <strong>Lifetime</strong>
                    </div>
                </div>
                <div style="margin-bottom: 30px;">
                    <span style="display: block; font-size: 14px; opacity: 0.7; margin-bottom: 5px;">Course Fee</span>
                    <strong
                        style="color: #d97706; font-size: 36px; line-height: 1;">₹<?php echo $course['price']; ?></strong>
                </div>
                <form action="process_payment.php" method="POST">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <input type="hidden" name="price" value="<?php echo $course['price']; ?>">
                    <button type="submit"
                        style="background: #eef2f6; color: #2a0845; padding: 18px 40px; border-radius: 50px; font-size: 18px; font-weight: bold; border: none; cursor: pointer; width: 100%; box-shadow: 0 10px 20px rgba(255, 184, 0, 0.3); transition: transform 0.3s;">
                        Proceed to Payment ->
                    </button>
                    <p style="text-align: center; font-size: 12px; opacity: 0.6; margin-top: 15px;">Secure 256-bit payment
                        encryption</p>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    // --- ELSE: SHOW THE VIDEO PLAYER DASHBOARD ---
else:
    // Fetch Lessons
    $lesson_sql = "SELECT * FROM lessons WHERE course_id = '$course_id' ORDER BY id ASC";
    $lesson_result = mysqli_query($conn, $lesson_sql);
    $lessons = [];
    while ($row = mysqli_fetch_assoc($lesson_result)) {
        $lessons[] = $row;
    }
    // Fetch Materials
    $material_sql = "SELECT * FROM course_materials WHERE course_id = '$course_id' ORDER BY id ASC";
    $material_result = mysqli_query($conn, $material_sql);
    $materials = [];
    while ($r = mysqli_fetch_assoc($material_result)) {
        $materials[] = $r;
    }
    $first_video = count($lessons) > 0 ? $lessons[0]['url'] : '';
    $first_title = count($lessons) > 0 ? $lessons[0]['title'] : 'No videos available yet';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($course['title']); ?> - Classroom</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    </head>
    <body>
        <!-- Navbar -->
        <div class="course-navbar">
            <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
            <h2 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h2>
            <div class="nav-actions">
                <a href="student_assignments.php?course_id=<?php echo $course_id; ?>" class="btn-action">📝 Assignments</a>
                <a href="take_quiz.php?course_id=<?php echo $course_id; ?>" class="btn-action"
                    style="background: rgba(255, 184, 0, 0.1); border-color: rgba(255, 184, 0, 0.5); color: #d97706;">🎯
                    Final Quiz</a>
                <a href="generate_certificate.php?course_id=<?php echo $course_id; ?>" class="btn-action"
                    style="background: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.5); color: #2ecc71;">🏆
                    Get Certificate</a>
                <a href="course_review.php?course_id=<?php echo $course_id; ?>" class="btn-action"
                    style="background: rgba(231, 76, 60, 0.1); border-color: rgba(231, 76, 60, 0.5); color: #e74c3c;">⭐
                    Rate Course</a>
            </div>
        </div>
        <!-- Main Layout -->
        <div class="player-container">
            <!-- Video Section (70%) -->
            <div class="video-section">
                <div class="video-wrapper">
                    <!-- Added id to video element to manipulate it via JS -->
                    <video id="mainVideoPlayer" controls autoplay muted>
                        <source id="videoSource" src="<?php echo htmlspecialchars($first_video); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="video-details">
                    <h1 id="currentVideoTitle"><?php echo htmlspecialchars($first_title); ?></h1>
                    <div class="instructor-badge">
                        <div
                            style="width: 30px; height: 30px; background: #007bb5; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #1a1638; font-weight: bold; font-size: 14px;">
                            <?php echo strtoupper(substr($course['instructor_name'], 0, 1)); ?>
                        </div>
                        <span>Prof. <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                    </div>
                    <div>
                        <h4 style="margin:0 0 10px 0; color: #d97706; font-size: 14px; text-transform: uppercase;">About
                            this Course</h4>
                        <p style="font-size: 14px; opacity: 0.8; line-height: 1.8; margin:0;">
                            <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Sidebar Playlist (30%) -->
            <div class="playlist-sidebar">
                <div class="playlist-header">
                    <h3>Course Content</h3>
                    <span class="progress-text" id="progressText">0 / <?php echo count($lessons); ?> Lessons
                        Completed</span>
                    <div class="progress-bar-container">
                        <div class="progress-bar" id="progressBar" style="width: 0%;"></div>
                    </div>
                </div>
                <div class="sidebar-tabs">
                    <div class="s-tab active" onclick="switchTab('lessons')" id="tab-lessons">Lessons</div>
                    <div class="s-tab" onclick="switchTab('materials')" id="tab-materials">Resources</div>
                </div>
                <div class="playlist-content" id="content-lessons">
                    <?php
                    $i = 1;
                    foreach ($lessons as $lesson):
                        ?>
                        <div class="lesson-item <?php echo ($i == 1) ? 'active' : ''; ?>" data-id="<?php echo $lesson['id']; ?>"
                            data-src="<?php echo htmlspecialchars($lesson['url']); ?>"
                            data-title="<?php echo htmlspecialchars($lesson['title']); ?>" onclick="playVideo(this)">
                            <div class="lesson-index"><?php echo $i; ?></div>
                            <div class="lesson-details">
                                <h4 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h4>
                                <span class="lesson-duration">▶ Video Lesson</span>
                            </div>
                            <?php if ($i == 1): ?><span class="playing-icon" style="color: #007bb5;">🔊</span><?php endif; ?>
                        </div>
                        <?php
                        $i++;
                    endforeach;
                    if (count($lessons) == 0)
                        echo "<p style='padding: 20px; text-align: center; opacity: 0.5;'>No lessons uploaded yet.</p>";
                    ?>
                </div>
                <div class="playlist-content" id="content-materials" style="display: none;">
                    <?php
                    foreach ($materials as $mat):
                        ?>
                        <div class="material-item">
                            <span>📄 <?php echo htmlspecialchars($mat['title']); ?></span>
                            <a href="<?php echo htmlspecialchars($mat['file_path']); ?>" download
                                class="btn-download">Download</a>
                        </div>
                        <?php
                    endforeach;
                    if (count($materials) == 0)
                        echo "<p style='padding: 20px; text-align: center; opacity: 0.5;'>No resources available.</p>";
                    ?>
                </div>
            </div>
        </div>
        <!-- Custom Player Logic -->
        <script>
            let currentPlayingIndex = 0;
            const totalLessons = <?php echo count($lessons); ?>;
                <?php
                // Fetch student's progress from database
                $prog_sql = "SELECT lesson_id FROM lesson_progress WHERE user_id = '$user_id' AND course_id = '$course_id'";
                $prog_res = mysqli_query($conn, $prog_sql);
                $completed_ids = [];
                while ($p = mysqli_fetch_assoc($prog_res)) {
                    $completed_ids[] = $p['lesson_id'];
                }
                $completed_indices = [];
                foreach ($lessons as $idx => $l) {
                    if (in_array($l['id'], $completed_ids))
                        $completed_indices[] = $idx;
                }
                ?>
                let lessonsCompleted = new Set([<?php echo implode(',', $completed_indices); ?>]); // Filled from Database!
            function playVideo(element) {
                // 1. Update Video Source
                const videoSrc = element.getAttribute('data-src');
                const videoTitle = element.getAttribute('data-title');
                const player = document.getElementById('mainVideoPlayer');
                player.src = videoSrc;
                player.play();
                // 2. Update Title
                document.getElementById('currentVideoTitle').innerText = videoTitle;
                // 3. UI Updates for active states
                document.querySelectorAll('.lesson-item').forEach((item, index) => {
                    item.classList.remove('active');
                    const icon = item.querySelector('.playing-icon');
                    if (icon) icon.remove(); // Remove playing icon from all
                    if (item === element) {
                        currentPlayingIndex = index;
                    }
                });
                element.classList.add('active');
                const playingIcon = document.createElement('span');
                playingIcon.classList.add('playing-icon');
                playingIcon.innerHTML = '🔊';
                playingIcon.style.color = '#007bb5';
                element.appendChild(playingIcon);
                // Add to completed once opened
                lessonsCompleted.add(currentPlayingIndex);
                updateProgress();
                // 4. Save to Database instantly
                const lessonDbId = element.getAttribute('data-id');
                fetch('save_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ course_id: <?php echo $course_id; ?>, lesson_id: lessonDbId })
            });
                }
            function switchTab(tab) {
                // Hide all
                document.getElementById('content-lessons').style.display = 'none';
                document.getElementById('content-materials').style.display = 'none';
                document.getElementById('tab-lessons').classList.remove('active');
                document.getElementById('tab-materials').classList.remove('active');
                // Show requested
                document.getElementById('content-' + tab).style.display = 'block';
                document.getElementById('tab-' + tab).classList.add('active');
            }
            function updateProgress() {
                const completed = lessonsCompleted.size;
                document.getElementById('progressText').innerText = `${completed} / ${totalLessons} Lessons Completed`;
                const percentage = totalLessons > 0 ? (completed / totalLessons) * 100 : 0;
                document.getElementById('progressBar').style.width = percentage + '%';
            }
            // Detect when video ends to auto-mark and proceed to next
            document.getElementById('mainVideoPlayer').addEventListener('ended', function () {
                lessonsCompleted.add(currentPlayingIndex);
                updateProgress();
                // Save end of video to DB
                const activeElement = document.querySelectorAll('.lesson-item')[currentPlayingIndex];
                if (activeElement) {
                    const lDbId = activeElement.getAttribute('data-id');
                    fetch('save_progress.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ course_id: <?php echo $course_id; ?>, lesson_id: lDbId })
                });
                    }
            // Play next if available
            const nextItem = document.querySelectorAll('.lesson-item')[currentPlayingIndex + 1];
            if (nextItem) {
                playVideo(nextItem);
            }
                });
            // Init progress bar visually on load
            updateProgress();
            // Unmute the player after auto-play initializes
            setTimeout(() => {
                document.getElementById('mainVideoPlayer').muted = false;
            }, 1000);
        </script>
        <?php include 'chatbot_widget.php'; ?>
    </body>
    </html>
<?php endif; ?>