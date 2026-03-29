<?php
session_start(); // This must be the very first line!
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "lms_db");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn On Air - Fall in Love with Learning</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <nav class="navbar glass">
        <div class="logo">Learn<span>OnAir</span></div>
        <div class="nav-links">
            <a href="index.php" style="color:#000000;">Home</a>
            <a href="#courses" style="color:#000000;">Courses</a>
            <a href="#features" style="color:#000000;">Benefits</a>
            <a href="elibrary_store.php" style="color:#000000; font-weight: 600;">E-Library 📚</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                if ($_SESSION['role'] == 'admin') {
                    $dashboard_link = 'admin_dashboard.php';
                } elseif ($_SESSION['role'] == 'teacher') {
                    $dashboard_link = 'instructor_dashboard.php';
                } else {
                    $dashboard_link = 'student_dashboard.php';
                }
                ?>
                <a href="<?php echo $dashboard_link; ?>" style="color:#000000; font-weight: 700;">My Dashboard</a>
                <a href="logout.php" style="color:#000000; font-weight: 600;">Logout</a>
            <?php else: ?>
                <a href="login.html" style="color:#000000;">Login</a>
                <a href="register.html" class="btn-register" style="color:#ffffff;">Sign Up Free</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="top-discovery" style="margin-top: 100px; padding: 0 8%;">
        <div class="search-container glass"
            style="display: flex; align-items: center; padding: 5px 20px; border-radius: 50px; max-width: 700px; margin: 0 auto 40px; border: 1px solid rgba(255, 255, 255, 1);">
            <span style="opacity: 0.6;">🔍</span>
            <input type="text" id="courseSearch" placeholder="What do you want to learn today?"
                style="flex-grow: 1; background: transparent; border: none; padding: 15px; color: #1a1638; outline: none; font-size: 16px;">
            <button class="btn-primary"
                style="padding: 10px 25px; border-radius: 50px; display: inline-block;">Search</button>
        </div>
        <div class="trust-bar" style="text-align: center; margin-bottom: 60px;">
            <p style="opacity: 0.6; font-size: 14px; margin-bottom: 20px;">Learn from leading industrial tools &
                frameworks</p>
            <div
                style="display: flex; justify-content: center; gap: 40px; filter: grayscale(1) brightness(2); opacity: 0.7; flex-wrap: wrap;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg" height="30" alt="PHP">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c3/Python-logo-notext.svg" height="30"
                    alt="Python">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/af/Adobe_Photoshop_CC_icon.svg" height="30"
                    alt="Photoshop">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/93/Amazon_Web_Services_Logo.svg" height="30"
                    alt="AWS">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Scikit_learn_logo_small.svg" height="30"
                    alt="Scikit">
            </div>
        </div>
    </div>
    <section class="hero-carousel"
        style="padding: 0 8% 60px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px;">
        <div class="glass"
            style="padding: 40px; border-radius: 25px; background: #eef2f6; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden;">
            <span
                style="background: #ff4b2b; color: #1a1638; padding: 5px 15px; border-radius: 5px; font-size: 12px; font-weight: 700; width: fit-content; margin-bottom: 20px;">LIMITED
                OFFER</span>
            <h1 style="font-size: 42px; margin: 0 0 15px 0; line-height: 1.1;">Master <span
                    style="color: #d97706;">Generative AI</span><br>with Expert Mentors</h1>
            <p style="opacity: 0.8; max-width: 450px; margin-bottom: 25px;">Gain industry-recognized certificates in
                Python and Data Science. Start your journey for free today.</p>
            <a href="register.html" class="btn-primary" style="width: fit-content; display: inline-block;">Enroll Now
                →</a>
            <img src="https://cdn-icons-png.flaticon.com/512/2103/2103633.png"
                style="position: absolute; right: -20px; bottom: -20px; width: 250px; opacity: 0.2;" alt="AI">
        </div>
        <div class="glass"
            style="padding: 30px; border-radius: 25px; border-left: 5px solid #007bb5; display: flex; flex-direction: column; justify-content: center; background: rgba(0, 242, 254, 0.05);">
            <h3 style="color: #007bb5; margin-top: 0;">Launch a New Career</h3>
            <p style="font-size: 14px; opacity: 0.8;">Explore professional certificates in Web Development and Database
                Management.</p>
            <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
                <div
                    style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.9); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    🎓</div>
                <span style="font-size: 13px; font-weight: 600;">Get Certified</span>
            </div>
        </div>
    </section>
    <section class="discovery-tiles" style="padding: 0 8% 80px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="glass tile"
                style="padding: 25px; border-radius: 20px; display: flex; align-items: center; gap: 20px; transition: 0.3s; cursor: pointer;">
                <div style="font-size: 30px;">💻</div>
                <div>
                    <h4 style="margin:0;">Web Development</h4>
                    <p style="margin:5px 0 0; font-size:12px; opacity:0.6;">PHP, HTML, CSS</p>
                </div>
            </div>
            <div class="glass tile"
                style="padding: 25px; border-radius: 20px; display: flex; align-items: center; gap: 20px; transition: 0.3s; cursor: pointer;">
                <div style="font-size: 30px;">📊</div>
                <div>
                    <h4 style="margin:0;">Data Analytics</h4>
                    <p style="margin:5px 0 0; font-size:12px; opacity:0.6;">Python, KNN, SQL</p>
                </div>
            </div>
            <div class="glass tile"
                style="padding: 25px; border-radius: 20px; display: flex; align-items: center; gap: 20px; transition: 0.3s; cursor: pointer;">
                <div style="font-size: 30px;">🎨</div>
                <div>
                    <h4 style="margin:0;">UI/UX Design</h4>
                    <p style="margin:5px 0 0; font-size:12px; opacity:0.6;">Figma, Glassmorphism</p>
                </div>
            </div>
        </div>
    </section>
    <header class="hero">
        <div class="hero-content glass">
            <h1>Engage with some <span>Learning</span></h1>
            <p>Empower your future with presidency-standard education. Join thousands of students mastering technology
                and business through interactive video lessons and expert mentorship.</p>
            <div class="hero-buttons">
                <a href="register.html" class="btn-primary" style="display: inline-block;">Start Learning</a>
                <a href="#courses" class="btn-outline">Explore Courses</a>
            </div>
        </div>
        <div class="hero-graphic">
            <div class="glass"
                style="position: absolute; bottom: -20px; left: -30px; padding: 15px 25px; border-radius: 15px; font-weight: 600;">
                <?php
                $rating_res = mysqli_query($conn, "SELECT AVG(rating) as avg_rating FROM course_reviews");
                $avg_r = mysqli_fetch_assoc($rating_res)['avg_rating'];
                $display_rating = $avg_r ? number_format($avg_r, 1) : "5.0";
                ?>
                ⭐ <?php echo $display_rating; ?>/5 Student Rating
            </div>
        </div>
    </header>
    <section id="courses" style="padding: 0 8% 100px;">
        <h2 style="text-align: center; margin-bottom: 40px;">Explore Masterclasses</h2>
        <div id="course-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php
            // Fetch top 6 courses from your existing database table
            $sql = "SELECT c.*, u.username FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.status='approved' LIMIT 6";
            $res = mysqli_query($conn, $sql);
            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    // Determine image (fallback if none exists)
                    $img = !empty($row['image_url']) ? $row['image_url'] : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600';
                    // Format the price beautifully
                    $price_display = ($row['price'] > 0) ? '₹' . htmlspecialchars($row['price']) : 'Free';
                    // Format category name for the label
                    $cat_display = str_replace('_', ' ', htmlspecialchars($row['category']));
                    echo "<div class='glass course-card' style='border-radius:20px; text-align:left; overflow:hidden; display:flex; flex-direction:column; transition:0.3s;'>";
                    // Course Image Header + Category Label + Price Tag
                    echo "  <div style='height: 160px; background-image: url(\"" . htmlspecialchars($img) . "\"); background-size: cover; background-position: center; position:relative;'>";
                    echo "      <div class='course-label'>" . $cat_display . "</div>";
                    echo "      <div style='position:absolute; top:15px; right:15px; background:rgba(0,0,0,0.6); backdrop-filter:blur(5px); color:#007bb5; padding:6px 12px; border-radius:50px; font-weight:700; border:1px solid rgba(0,242,254,0.3); font-size:13px;'>" . $price_display . "</div>";
                    echo "  </div>";
                    // Course Details
                    echo "  <div style='padding: 20px; flex-grow: 1; display: flex; flex-direction: column;'>";
                    echo "      <h3 style='margin:0 0 10px 0; font-size:18px;'>" . htmlspecialchars($row['title']) . "</h3>";
                    echo "      <p style='font-size:13px; opacity:0.7; margin:0 0 20px 0; flex-grow:1; line-height:1.5;'>" . htmlspecialchars(substr($row['description'], 0, 85)) . "...</p>";
                    // Footer (Instructor & Button)
                    echo "      <div style='display:flex; justify-content:space-between; align-items:center; margin-top:auto; border-top: 1px solid rgba(0, 0, 0, 0.1); padding-top:15px;'>";
                    echo "          <div style='display:flex; align-items:center; gap:8px;'>";
                    echo "              <div style='width:25px; height:25px; background:#ffb800; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#2a0845; font-weight:bold; font-size:10px;'>" . strtoupper(substr($row['username'], 0, 1)) . "</div>";
                    echo "              <span style='font-size:12px; color:#ffb800; font-weight:500;'>Prof. " . htmlspecialchars($row['username']) . "</span>";
                    echo "          </div>";
                    // LINK FIXED: Directs them to the payment/preview page we built
                    echo "          <a href='course_details.php?id=" . $row['id'] . "' class='btn-primary' style='padding:8px 18px; font-size:12px; display:inline-block;'>View Info</a>";
                    echo "      </div>";
                    echo "  </div>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align:center; grid-column: 1 / -1; opacity: 0.6;'>No courses are currently available. Check back soon!</p>";
            }
            ?>
        </div>
    </section>
    <section id="features" class="features">
        <span class="section-tag">Features</span>
        <h2>Why Gaining from Learn<span>OnAir</span></h2>
        <div class="feature-grid">
            <div class="feature-card glass">
                <div class="feature-icon">🚀</div>
                <h3>Industry-Ready Expertise</h3>
                <p>Gaining these courses equips you with practical skills in Web Dev and Data Science that are currently
                    in high demand.</p>
            </div>
            <div class="feature-card glass">
                <div class="feature-icon">🎓</div>
                <h3>Academic Edge</h3>
                <p>Designed to complement the Presidency Autonomous College curriculum. Mastering these modules ensures
                    higher performance in exams.</p>
            </div>
            <div class="feature-card glass">
                <div class="feature-icon">🛠️</div>
                <h3>Project-Based Growth</h3>
                <p>Every course concludes with a real-world project. You'll have a fully functional application to
                    showcase your talent.</p>
            </div>
            <div class="feature-card glass">
                <div class="feature-icon">⚡</div>
                <h3>Instant Skill Validation</h3>
                <p>With our automated MCQ assessments, you receive immediate results and feedback to identify your weak
                    areas.</p>
            </div>
            <div class="feature-card glass">
                <div class="feature-icon">🤝</div>
                <h3>Direct Mentor Access</h3>
                <p>Engage directly with mentors! Use our smart class discussion system to ask questions directly to
                    experts.</p>
            </div>
            <div class="feature-card glass">
                <div class="feature-icon">📚</div>
                <h3>Structured Learning Path</h3>
                <p>Follow a curated roadmap designed by professionals to take you from a beginner to an advanced level.
                </p>
            </div>
        </div>
    </section>
    <script>
        document.getElementById('courseSearch').addEventListener('keyup', function () {
            // 1. Get what the user typed and make it lowercase
            let filter = this.value.toLowerCase();
            // 2. Select all the course cards that were generated by PHP
            let cards = document.querySelectorAll('.course-card');
            cards.forEach(card => {
                // 3. Get the Title (h3) and the Category (course-label)
                let title = card.querySelector('h3').innerText.toLowerCase();
                let category = card.querySelector('.course-label').innerText.toLowerCase();
                // 4. If the search term is found in either, show the card; otherwise, hide it
                if (title.includes(filter) || category.includes(filter)) {
                    card.style.display = "flex"; // Use flex to maintain your alignment fix
                    card.style.opacity = "1";
                } else {
                    card.style.display = "none";
                    card.style.opacity = "0";
                }
            });
        });
    </script>
    <footer class="footer glass">
        <h3>&copy; 2026</h3>
        <p>Final Year BCA Project | Presidency Autonomous College</p>
    </footer>
    <?php include 'chatbot_widget.php'; ?>
</body>
</html>