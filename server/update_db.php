<?php

function getEnvValue($file, $key, $default = '') {
    $content = file_get_contents($file);
    if (preg_match('/^' . preg_quote($key) . '\s*=\s*(.*)$/m', $content, $matches)) {
        return trim($matches[1], " \t\n\r\0\x0B\"'");
    }
    return $default;
}

$envFile = __DIR__ . '/.env';
$host = getEnvValue($envFile, 'database.default.hostname', '127.0.0.1');
$db   = getEnvValue($envFile, 'database.default.database', 'patil_photography');
$user = getEnvValue($envFile, 'database.default.username', 'root');
$pass = getEnvValue($envFile, 'database.default.password', '');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    if ($conn->query($sql) === TRUE) {
        echo "Executed: $sql\n";
    } else {
        echo "Existed or Error: $sql (" . $conn->error . ")\n";
    }
}

$conn->close();
echo "Success!\n";
