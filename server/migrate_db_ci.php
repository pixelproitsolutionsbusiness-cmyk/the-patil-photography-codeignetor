<?php

namespace App\Controllers;

require_once __DIR__ . '/public/index.php';

$db = \Config\Database::connect();

$queries = [
    "ALTER TABLE sliders ADD COLUMN `order` INT DEFAULT 0",
    "ALTER TABLE love_stories ADD COLUMN `gallery` TEXT",
    "ALTER TABLE testimonials ADD COLUMN `thumbnail` TEXT",
    "ALTER TABLE team_members ADD COLUMN `order` INT DEFAULT 0",
    "ALTER TABLE popups ADD COLUMN `subtitle` TEXT",
    "ALTER TABLE popups ADD COLUMN `link` VARCHAR(255)",
    "ALTER TABLE popups ADD COLUMN `status` ENUM('Active', 'Inactive') DEFAULT 'Active'"
];

foreach ($queries as $sql) {
    try {
        $db->query($sql);
        echo "Executed: $sql\n";
    } catch (\Exception $e) {
        echo "Failed or already exists: $sql (" . $e->getMessage() . ")\n";
    }
}

echo "Migration complete.\n";
