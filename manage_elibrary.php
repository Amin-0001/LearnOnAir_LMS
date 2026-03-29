<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "lms_db");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit();
}
// Add New Book Recommendation
if (isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $amazon_link = mysqli_real_escape_string($conn, $_POST['amazon_link']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $recommended_for = mysqli_real_escape_string($conn, $_POST['recommended_for']);
    // Handle Image upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "uploads/elibrary/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $cover_image = $target_dir . time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES["cover_image"]["name"]));
        move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_image);
    }
    $sql = "INSERT INTO elibrary (title, author, category, cover_image, amazon_link, description, recommended_for) 
            VALUES ('$title', '$author', '$category', '$cover_image', '$amazon_link', '$description', '$recommended_for')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Book Recommendation Published!'); window.location='manage_elibrary.php';</script>";
    }
}
// Delete Book
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $q = mysqli_query($conn, "SELECT cover_image FROM elibrary WHERE id='$id'");
    $img = mysqli_fetch_assoc($q)['cover_image'];
    if ($img && file_exists($img)) {
        unlink($img);
    }
    mysqli_query($conn, "DELETE FROM elibrary WHERE id='$id'");
    echo "<script>alert('Book removed from library.'); window.location='manage_elibrary.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage E-Library - Learn On Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <?php include 'global_sidebar.php'; ?>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">Curate E-Library (Book Recommendations)</div>
            <a href="index.php" style="color: #007bb5; font-weight: 600;">🏠 Home</a>
        </div>
        <div class="dashboard-grid">
            <div class="panel glass">
                <h3>Publish New Recommendation 📚</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="input-group">
                        <label>Book Title</label>
                        <input type="text" name="title" required placeholder="e.g. Clean Code">
                    </div>
                    <div class="input-group">
                        <label>Author / Publisher</label>
                        <input type="text" name="author" required placeholder="Robert C. Martin">
                    </div>
                    <div class="input-group">
                        <label>Book Category</label>
                        <select name="category" required>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Data & Analytics">Data & Analytics</option>
                            <option value="Design & UX">Design & UX</option>
                            <option value="Business & Interview">Business & Interview</option>
                            <option value="Self Development">Self Development</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Affiliate / Amazon Purchase Link</label>
                        <input type="url" name="amazon_link" required placeholder="https://amazon.com/dp/123...">
                    </div>
                    <div class="input-group">
                        <label>Brief Review / Why read this?</label>
                        <textarea name="description" rows="3" required
                            placeholder="This book establishes the best practices..."></textarea>
                    </div>
                    <div class="input-group">
                        <label>Recommended For (Keywords)</label>
                        <input type="text" name="recommended_for"
                            placeholder="e.g. Beginners, Python Devs, AI Enthusiasts">
                    </div>
                    <div class="input-group">
                        <label>Book Cover Image (JPG, PNG)</label>
                        <input type="file" name="cover_image" accept="image/*" required>
                    </div>
                    <button type="submit" name="add_book" class="btn-submit">Add to Virtual Library</button>
                </form>
            </div>
            <div class="panel glass">
                <h3 style="color: #007bb5;">Library Collection overview</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Book Details</th>
                            <th>Purchase Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM elibrary ORDER BY id DESC";
                        $res = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_assoc($res)) {
                                echo "<tr>";
                                echo "<td><img src='" . htmlspecialchars($row['cover_image']) . "' class='thumb'></td>";
                                echo "<td><strong style='color: #1a1638;'>" . htmlspecialchars($row['title']) . "</strong><br><small style='opacity:0.7;'>By " . htmlspecialchars($row['author']) . "</small><br>
                                    <span style='background:rgba(255,184,0,0.2); color:#ffb800; padding:2px 6px; border-radius:4px; font-size:10px; margin-top:4px; display:inline-block;'>" . htmlspecialchars($row['category']) . "</span></td>";
                                echo "<td><a href='" . htmlspecialchars($row['amazon_link']) . "' target='_blank' style='color:#007bb5; text-decoration:underline;'>View Store -></a></td>";
                                echo "<td><a href='manage_elibrary.php?delete=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Remove from library?\");'>Delete</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; opacity:0.6;'>No books added yet. The library is empty!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>