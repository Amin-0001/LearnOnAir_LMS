<?php
$css_file = 'style.css';
$css = file_get_contents($css_file);

$missing_css = "
/* --- Restored Container Logic for Standalone Pages --- */
.container {
    max-width: 900px;
    width: 90%;
    margin: 60px auto;
    padding: 40px;
    border-radius: 20px;
    position: relative;
    z-index: 10;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    padding-bottom: 25px;
    margin-bottom: 30px;
}
";

// Use precise regex to check if `.container {` exists as a standalone class, not part of `.player-container`
if (!preg_match('/(^|\s)\.container\s*\{/i', $css)) {
    file_put_contents($css_file, $css . "\n" . $missing_css);
    echo "Appended missing .container and .header CSS.";
} else {
    echo "CSS already exists.";
}
?>