<?php

function getEnvValue($file, $key, $default = '') {
    $content = file_get_contents($file);
    if (preg_match('/^' . preg_quote($key) . '\s*=\s*(.*)$/m', $content, $matches)) {
        return trim($matches[1], " \t\n\r\0\x0B\"'");
    }
    return $default;
}

$envFile = 'server/.env';
$host = getEnvValue($envFile, 'database.default.hostname', '127.0.0.1');
$db   = getEnvValue($envFile, 'database.default.database', 'patil_photography');
$user = getEnvValue($envFile, 'database.default.username', 'root');
$pass = getEnvValue($envFile, 'database.default.password', '');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     echo "Connected to database $db...\n";

     // 1. Sliders table: add order
     try { $pdo->exec("ALTER TABLE sliders ADD COLUMN `order` INT DEFAULT 0"); echo "Added order to sliders\n"; } catch(Exception $e) {}

     // 2. Love Stories table: add gallery
     try { $pdo->exec("ALTER TABLE love_stories ADD COLUMN `gallery` TEXT"); echo "Added gallery to love_stories\n"; } catch(Exception $e) {}

     // 3. Testimonials table: add thumbnail
     try { $pdo->exec("ALTER TABLE testimonials ADD COLUMN `thumbnail` TEXT"); echo "Added thumbnail to testimonials\n"; } catch(Exception $e) {}

     // 4. Team Members table: add order
     try { $pdo->exec("ALTER TABLE team_members ADD COLUMN `order` INT DEFAULT 0"); echo "Added order to team_members\n"; } catch(Exception $e) {}

     // 5. Popups table: fix columns
     try { $pdo->exec("ALTER TABLE popups ADD COLUMN `subtitle` TEXT"); } catch(Exception $e) {}
     try { $pdo->exec("ALTER TABLE popups ADD COLUMN `link` VARCHAR(255)"); } catch(Exception $e) {}
     try { $pdo->exec("ALTER TABLE popups ADD COLUMN `status` ENUM('Active', 'Inactive') DEFAULT 'Active'"); } catch(Exception $e) {}
     echo "Updated popups table columns\n";

     echo "Database migration successful!\n";

} catch (\PDOException $e) {
     echo "ERROR: " . $e->getMessage() . "\n";
     exit(1);
}
