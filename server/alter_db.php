<?php
$conn = new mysqli("localhost", "root", "", "patil_photography");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("ALTER TABLE testimonials MODIFY COLUMN status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active'");
if ($conn->error) {
    echo "Error: " . $conn->error;
} else {
    echo "Success!";
}
