<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'patil_photography');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$sql = "ALTER TABLE love_stories MODIFY COLUMN gallery MEDIUMTEXT";
if ($conn->query($sql) === TRUE) {
    echo "Column gallery upgraded to MEDIUMTEXT\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();
