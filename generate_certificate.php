<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    die("Unauthorized access.");
}
if (!isset($_GET['course_id'])) {
    die("Course ID missing.");
}

$user_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id']);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student';

// 2. Database Verification: Did they actually pass?
$sql = "SELECT qr.score, qr.total_questions, c.title 
        FROM quiz_results qr 
        JOIN courses c ON qr.course_id = c.id 
        WHERE qr.user_id = '$user_id' AND qr.course_id = '$course_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h2>No quiz record found!</h2><p>You must complete and pass the final quiz before generating a certificate.</p><a href='course_details.php?id=$course_id'>Go back</a></div>");
}

$row = mysqli_fetch_assoc($result);
$percentage = ($row['total_questions'] > 0) ? round(($row['score'] / $row['total_questions']) * 100) : 0;

if ($percentage < 50) {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h2>Not Passed Yet</h2><p>You have not achieved a passing grade (50%+) for this course yet. Your current score is $percentage%.</p><a href='course_details.php?id=$course_id'>Go back</a></div>");
}

$course_title = $row['title'];
$date = date("F j, Y");

// HTML Certificate Generation
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Certificate - <?php echo htmlspecialchars($course_title); ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Playfair+Display:ital,wght@0,600;1,700&display=swap"
        rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #eef2f6;
            /* Matching the main theme body color */
            font-family: 'Poppins', sans-serif;
            flex-direction: column;
        }

        /* Certificate Container */
        .certificate-wrapper {
            position: relative;
            width: 900px;
            height: 650px;
            background: #fff;
            padding: 40px;
            box-sizing: border-box;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            /* Creating the rich border effect */
            border: 15px solid #2a0845;
            outline: 5px solid #ffb800;
            outline-offset: -20px;
            text-align: center;
            overflow: hidden;
            background-image: radial-gradient(circle at center, rgba(0, 242, 254, 0.05) 0%, transparent 70%);
        }

        /* Subtle Background Elements */
        .cert-bg-blob {
            position: absolute;
            width: 300px;
            height: 300px;
            background: #007bb5;
            filter: blur(100px);
            opacity: 0.15;
            top: -100px;
            right: -100px;
            border-radius: 50%;
            z-index: 0;
        }

        .cert-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        h1.cert-title {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: #ffb800;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        h2.cert-subtitle {
            font-size: 18px;
            font-weight: 400;
            color: #1a1638;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        p.cert-text {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }

        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 50px;
            color: #007bb5;
            font-weight: 700;
            font-style: italic;
            margin: 20px 0;
            border-bottom: 2px solid #eef2f6;
            padding-bottom: 10px;
            width: 80%;
        }

        .course-name {
            font-size: 26px;
            font-weight: 700;
            color: #1a1638;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .grade-text {
            font-size: 18px;
            color: #d97706;
            font-weight: 600;
            margin-bottom: 50px;
        }

        .footer-details {
            display: flex;
            justify-content: space-between;
            width: 80%;
            margin-top: auto;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 20px;
            font-size: 14px;
            font-weight: 600;
            color: #1a1638;
        }

        .signature {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-style: italic;
            color: #2a0845;
        }

        /* Action Buttons */
        .action-container {
            margin-top: 30px;
            display: flex;
            gap: 20px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #007bb5;
            color: white;
        }

        .btn-primary:hover {
            background: #005f8c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 181, 0.3);
        }

        .btn-secondary {
            background: #fff;
            color: #1a1638;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        @media print {
            body {
                background: white;
            }

            .action-container {
                display: none !important;
            }

            .certificate-wrapper {
                box-shadow: none;
                border: 10px solid #2a0845;
            }
        }
    </style>
</head>

<body>

    <div class="certificate-wrapper" id="certificate">
        <div class="cert-bg-blob"></div>
        <div class="cert-content">
            <h1 class="cert-title">Certificate of Completion</h1>
            <h2 class="cert-subtitle">LearnOnAir LMS</h2>

            <p class="cert-text">This is to proudly certify that</p>
            <div class="student-name"><?php echo htmlspecialchars(strtoupper($user_name)); ?></div>

            <p class="cert-text">has successfully completed the masterclass:</p>
            <div class="course-name"><?php echo htmlspecialchars($course_title); ?></div>

            <div class="grade-text">Achieving a passing grade of <?php echo $percentage; ?>%</div>

            <div class="footer-details">
                <div style="text-align: left;">
                    <span class="cert-text" style="display:block; font-size:12px;">Awarded on</span>
                    <?php echo $date; ?>
                </div>

                <div style="text-align: center;">
                    <div class="signature">LearnOnAir</div>
                    <span class="cert-text" style="font-size:12px;">Authorized Signature</span>
                </div>

                <div style="text-align: right;">
                    <span class="cert-text" style="display:block; font-size:12px;">Course ID</span>
                    #<?php echo str_pad($course_id, 5, "0", STR_PAD_LEFT); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="action-container">
        <a href="course_details.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back to Course</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Save as PDF</button>
    </div>

</body>

</html>