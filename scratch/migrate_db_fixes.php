<?php
// DB Migration Script
$mysqli = new mysqli("localhost", "root", "", "patil_photography");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check for gallery column in love_stories
$result = $mysqli->query("SHOW COLUMNS FROM love_stories LIKE 'gallery'");
if ($result->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE love_stories ADD COLUMN gallery LONGTEXT AFTER thumbnail")) {
        echo "Added gallery column to love_stories\n";
    } else {
        echo "Error adding gallery column: " . $mysqli->error . "\n";
    }
} else {
    echo "gallery column already exists in love_stories\n";
}

// Add display_order if missing
$result = $mysqli->query("SHOW COLUMNS FROM love_stories LIKE 'display_order'");
if ($result->num_rows == 0) {
    $mysqli->query("ALTER TABLE love_stories ADD COLUMN display_order INT DEFAULT 1 AFTER status");
}

// Check for clientName vs name in testimonials
$result = $mysqli->query("SHOW COLUMNS FROM testimonials LIKE 'clientName'");
if ($result->num_rows > 0) {
    // Keep it as is but ensure we have display_order
    $res2 = $mysqli->query("SHOW COLUMNS FROM testimonials LIKE 'display_order'");
    if ($res2->num_rows == 0) {
        $mysqli->query("ALTER TABLE testimonials ADD COLUMN display_order INT DEFAULT 1 AFTER rating");
        echo "Added display_order to testimonials\n";
    }
}

$mysqli->close();
