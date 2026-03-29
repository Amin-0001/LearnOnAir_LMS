<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
$is_logged_in = isset($_SESSION['user_id']);
$dashboard_link = "login.html";
if ($is_logged_in) {
    if ($_SESSION['role'] == 'admin')
        $dashboard_link = 'admin_dashboard.php';
    elseif ($_SESSION['role'] == 'teacher')
        $dashboard_link = 'instructor_dashboard.php';
    else
        $dashboard_link = 'student_dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Library | Academic Bookstore & Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .filter-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 123, 181, 0.3);
            color: #1a1638;
            padding: 10px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        .filter-btn.active, .filter-btn:hover {
            background: linear-gradient(135deg, #007bb5, #005f8c);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(0, 123, 181, 0.3);
            transform: translateY(-2px);
        }
        
        .store-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .item-card {
            display: flex;
            flex-direction: column;
            padding: 25px;
            border-radius: 20px;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .note-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 248, 255, 0.95));
            border-top: 5px solid #d97706;
            text-align: center;
            justify-content: center;
        }

        .note-card::before {
            content: '📄';
            position: absolute;
            font-size: 150px;
            opacity: 0.03;
            bottom: -20px;
            right: -20px;
            pointer-events: none;
        }

        .note-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .search-container-main {
            max-width: 600px; 
            margin: 0 auto; 
            position: relative;
        }

        .search-container-main input {
            width: 100%; 
            padding: 18px 25px 18px 50px; 
            border-radius: 50px; 
            border: 2px solid transparent; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            font-size: 16px; 
            outline: none;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .search-container-main input:focus {
            border-color: #007bb5;
            box-shadow: 0 10px 30px rgba(0, 123, 181, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>

    <nav class="navbar glass">
        <div class="logo">E-Library<span>.</span></div>
        <div class="nav-links">
            <a href="index.php">Main Platform</a>
            <a href="live_classes.php">Webinars</a>
            <a href="<?php echo $dashboard_link; ?>" class="btn-dash" style="background: #eef2f6; border-radius: 50px; padding: 8px 18px; font-weight: bold; color: #1a1638;">Returns to Dashboard</a>
        </div>
    </nav>

    <div class="hero" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 120px 20px 60px 20px; gap: 20px;">
        <h1 style="font-size: 42px; margin: 0; color: #1a1638;">Expand your <span style="color: #007bb5;">Mental Library</span></h1>
        <p style="opacity: 0.8; font-size: 16px; max-width: 700px; margin: 0; line-height: 1.6;">Access expert-curated book recommendations from real university professors, alongside exclusive class notes and materials uploaded directly by your instructors.</p>
        
        <div class="search-container-main" style="width: 100%; max-width: 600px; position: relative; margin-top: 10px;">
            <span class="search-icon">🔍</span>
            <input type="text" id="libSearch" placeholder="Search by book title, course, author, or tags...">
        </div>
        
        <div class="filter-container" style="display: flex; justify-content: center; gap: 15px; margin-top: 10px; flex-wrap: wrap;">
            <button class="filter-btn active" data-filter="all">🌟 All Resources</button>
            <button class="filter-btn" data-filter="book">📚 Recommended Books</button>
            <button class="filter-btn" data-filter="note">📄 Instructor Notes</button>
        </div>
    </div>

    <div class="store-section" style="padding: 20px 8% 80px 8%;">
        <div class="store-grid" id="store-grid">
            <?php
            // 1. Fetch Books
            $sql_books = "SELECT * FROM elibrary ORDER BY id DESC";
            $res_books = mysqli_query($conn, $sql_books);
            
            if (mysqli_num_rows($res_books) > 0) {
                while ($row = mysqli_fetch_assoc($res_books)) {
                    $tags = explode(',', $row['recommended_for']);
                    echo '<div class="glass item-card book-item" data-type="book">';
                    echo '  <div class="search-target" style="display:none;">book</div>'; // invisible generic tag
                    echo '  <div class="search-target" style="position:absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); backdrop-filter:blur(5px); padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: bold; color: #1a1638; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 10;">' . htmlspecialchars($row['category']) . '</div>';
                    echo '  <img src="' . htmlspecialchars($row['cover_image']) . '" class="book-cover" alt="Cover" style="width:100%; height:280px; object-fit:cover; border-radius:15px; margin-bottom:20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">';
                    echo '  <h3 class="search-target" style="margin: 0 0 5px 0; font-size: 18px; color: #1a1638;">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '  <p class="search-target" style="opacity: 0.6; font-size: 13px; font-weight: 600; margin: 0 0 15px 0;">by ' . htmlspecialchars($row['author']) . '</p>';
                    echo '  <p style="font-size: 13px; opacity: 0.8; margin-bottom: 20px; line-height: 1.6; flex-grow: 1;">' . mb_strimwidth(htmlspecialchars($row['description']), 0, 120, "...") . '</p>';
                    
                    echo '  <div class="search-target" style="margin-bottom: 25px; display:flex; flex-wrap:wrap; gap:8px;">';
                    foreach ($tags as $t) {
                        $trim_t = trim($t);
                        if (!empty($trim_t)) {
                            echo '<span style="background: rgba(0,123,181,0.08); border: 1px solid rgba(0,123,181,0.2); color: #007bb5; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 600;"># ' . htmlspecialchars($trim_t) . '</span>';
                        }
                    }
                    echo '  </div>';
                    
                    echo '  <a href="' . htmlspecialchars($row['amazon_link']) . '" target="_blank" style="display:flex; justify-content:center; align-items:center; gap: 8px; background:linear-gradient(135deg, #ffb800, #f59e0b); color:#1a1638; text-decoration:none; padding:12px; border-radius:12px; font-weight:bold; box-shadow: 0 5px 15px rgba(255,184,0,0.3); transition: 0.3s; margin-top:auto;" onmouseover="this.style.transform=\'translateY(-2px)\';" onmouseout="this.style.transform=\'translateY(0)\';">';
                    echo '      View on Amazon';
                    echo '  </a>';
                    echo '</div>';
                }
            }

            // 2. Fetch Instructor Notes
            $sql_notes = "SELECT cm.title AS note_title, cm.file_path, c.title AS course_title, u.username AS instructor_name 
                          FROM course_materials cm 
                          JOIN courses c ON cm.course_id = c.id 
                          JOIN users u ON c.instructor_id = u.id 
                          ORDER BY cm.id DESC";
            $res_notes = mysqli_query($conn, $sql_notes);

            if (mysqli_num_rows($res_notes) > 0) {
                while ($note = mysqli_fetch_assoc($res_notes)) {
                    echo '<div class="glass item-card note-card" data-type="note">';
                    echo '  <div class="search-target" style="display:none;">note material pdf</div>'; // generic tags
                    echo '  <div class="note-icon">📝</div>';
                    echo '  <div class="search-target" style="background: rgba(217,119,6,0.1); border: 1px solid rgba(217,119,6,0.2); color:#d97706; padding: 6px 15px; border-radius:50px; font-size: 12px; font-weight:bold; margin: 0 auto 20px auto; display: inline-block;">' . htmlspecialchars($note['course_title']) . '</div>';
                    echo '  <h3 class="search-target" style="margin: 0 0 10px 0; font-size: 20px; color: #1a1638;">' . htmlspecialchars($note['note_title']) . '</h3>';
                    echo '  <p class="search-target" style="opacity: 0.7; font-size: 14px; font-weight: 500; margin: 0 0 25px 0;">Uploaded by Prof. ' . htmlspecialchars($note['instructor_name']) . '</p>';
                    echo '  <div style="flex-grow: 1;"></div>'; // spacer
                    echo '  <a href="' . htmlspecialchars($note['file_path']) . '" download style="display:block; text-align:center; background:linear-gradient(135deg, #007bb5, #005f8c); color:#fff; text-decoration:none; padding:12px 20px; border-radius:12px; font-weight:bold; box-shadow: 0 5px 15px rgba(0,123,181,0.3); transition: 0.3s;" onmouseover="this.style.transform=\'translateY(-2px)\';" onmouseout="this.style.transform=\'translateY(0)\';">Download PDF</a>';
                    echo '</div>';
                }
            }

            if (mysqli_num_rows($res_books) == 0 && mysqli_num_rows($res_notes) == 0) {
                echo "<p style='text-align:center; grid-column: 1 / -1; font-size: 20px; opacity: 0.6; padding: 80px; background: rgba(255,255,255,0.5); border-radius: 20px;'>The library shelves are currently empty. Check back soon for reading material! 📖</p>";
            }
            ?>
        </div>
    </div>

    <!-- Search & Filter Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('libSearch');
            const filterBtns = document.querySelectorAll('.filter-btn');
            const items = document.querySelectorAll('.item-card');
            
            let currentFilter = 'all';

            function filterItems() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                items.forEach(item => {
                    const itemType = item.getAttribute('data-type');
                    
                    // Harvest all searchable text within this item
                    let textContent = "";
                    item.querySelectorAll('.search-target').forEach(el => {
                        textContent += " " + el.innerText.toLowerCase();
                    });

                    // Check if search phrase exists in the text content
                    const matchesSearch = textContent.includes(searchTerm);
                    // Check if the card matches the currently selected category tab
                    const matchesType = (currentFilter === 'all' || currentFilter === itemType);

                    if (matchesSearch && matchesType) {
                        item.style.display = "flex"; // Restore specific flex layout for these cards
                        item.style.animation = "fadeIn 0.4s ease";
                    } else {
                        item.style.display = "none";
                    }
                });
            }

            // Type to search
            searchInput.addEventListener('input', filterItems);

            // Click tab to filter
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentFilter = btn.getAttribute('data-filter');
                    filterItems();
                });
            });
        });
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
    <?php include 'chatbot_widget.php'; ?>
</body>
</html>