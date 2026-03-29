<?php
$css_file = 'style.css';
$css = file_get_contents($css_file);

$missing_css = "
/* --- Restored Dashboard Grid for Instructor --- */
.dashboard-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
    align-items: start;
}

.left-column {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.form-card {
    padding: 30px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.course-list-card {
    padding: 30px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.course-list-card h3, .form-card h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #1a1638;
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
";

if (strpos($css, '.dashboard-grid {') === false) {
    file_put_contents($css_file, $css . "\n" . $missing_css);
    echo "Appended missing .dashboard-grid and related CSS.";
} else {
    echo "CSS already exists.";
}
?>