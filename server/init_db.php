<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to read database configuration from .env if it exists
function getEnvValue($key, $default = '') {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $content = file_get_contents($envFile);
        if (preg_match('/^' . preg_quote($key) . '\s*=\s*(.*)$/m', $content, $matches)) {
            return trim($matches[1], " \t\n\r\0\x0B\"'");
        }
    }
    return $default;
}

$host = getEnvValue('database.default.hostname', 'localhost');
$db   = getEnvValue('database.default.database', 'u686584126_photography');
$user = getEnvValue('database.default.username', 'u686584126_photography');
$pass = getEnvValue('database.default.password', 'S8=jCe!G#$b');
$charset = 'utf8mb4';

// Inform user about credentials
echo "Using credentials from .env or defaults:<br>";
echo "Host: " . htmlspecialchars($host) . "<br>";
echo "Database: " . htmlspecialchars($db) . "<br>";
echo "User: " . htmlspecialchars($user) . "<br><hr>";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Helper function to check if column exists
     function columnExists($pdo, $table, $column) {
         try {
             $res = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
             return $res->rowCount() > 0;
         } catch(Exception $e) { return false; }
     }

     // 1. Create Orders table
     $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customerName VARCHAR(255) NULL,
        customerPhone VARCHAR(50) NULL,
        email VARCHAR(255) NULL,
        event_name VARCHAR(255) NULL,
        photographyType VARCHAR(100) NULL,
        location VARCHAR(255) NULL,
        date DATE NULL,
        event_end_date DATE NULL,
        start_time TIME NULL,
        end_time TIME NULL,
        services TEXT NULL,
        albumPages INT NULL,
        amount DECIMAL(15,2) DEFAULT 0,
        paidAmount DECIMAL(15,2) DEFAULT 0,
        remaining_amount DECIMAL(15,2) DEFAULT 0,
        deliverables VARCHAR(255) NULL,
        delivery_date DATE NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        notes TEXT NULL,
        serviceConfig TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
     echo "Orders table check/create complete.<br>";

     // 2. Create Assets table
     $pdo->exec("CREATE TABLE IF NOT EXISTS assets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(100) NULL,
        name VARCHAR(255) NULL,
        purchaseDate DATE NULL,
        nextService DATE NULL,
        quantity INT DEFAULT 0,
        `condition` VARCHAR(50) NULL,
        mm VARCHAR(100) NULL,
        section VARCHAR(100) NULL,
        updatedAt DATE NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
     echo "Assets table check/create complete.<br>";

     // 3. Update Existing Tables
     
     // Users
     if (!columnExists($pdo, 'users', 'phone')) {
         $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(50) NULL AFTER email");
     }
     if (!columnExists($pdo, 'users', 'status')) {
         $pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(50) DEFAULT 'Active' AFTER role");
     }

     // Services
     if (!columnExists($pdo, 'services', 'ratePerDay')) {
         $pdo->exec("ALTER TABLE services ADD COLUMN ratePerDay DECIMAL(15,2) DEFAULT 0 AFTER basePrice");
     }

     // Sliders
     if (!columnExists($pdo, 'sliders', 'order')) {
         $pdo->exec("ALTER TABLE sliders ADD COLUMN `order` INT DEFAULT 0");
     }
     if (!columnExists($pdo, 'sliders', 'subtitle')) {
         $pdo->exec("ALTER TABLE sliders ADD COLUMN subtitle VARCHAR(255) NULL");
     }

      // Testimonials table alignment (rename old columns if they exist)
      foreach ([['clientName', 'name', "VARCHAR(100) NULL"], ['review', 'text', "TEXT NULL"]] as $col) {
          if (columnExists($pdo, 'testimonials', $col[0]) && !columnExists($pdo, 'testimonials', $col[1])) {
              $pdo->exec("ALTER TABLE testimonials CHANGE COLUMN `{$col[0]}` `{$col[1]}` {$col[2]}");
              echo "Renamed column {$col[0]} to {$col[1]} in testimonials.<br>";
          }
      }
      if (!columnExists($pdo, 'testimonials', 'role')) $pdo->exec("ALTER TABLE testimonials ADD COLUMN role VARCHAR(100) NULL");
      if (!columnExists($pdo, 'testimonials', 'thumbnail')) $pdo->exec("ALTER TABLE testimonials ADD COLUMN thumbnail TEXT NULL");
      if (!columnExists($pdo, 'testimonials', 'order')) $pdo->exec("ALTER TABLE testimonials ADD COLUMN `order` INT DEFAULT 0");
      
      // Ensure testimonials status includes 'Pending'
      $pdo->exec("ALTER TABLE testimonials MODIFY COLUMN status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Pending'");
      echo "Testimonials status updated to include 'Pending'.<br>";
      
      // Love Stories (Gallery & Order)
      if (!columnExists($pdo, 'love_stories', 'gallery')) $pdo->exec("ALTER TABLE love_stories ADD COLUMN gallery TEXT NULL");
      if (columnExists($pdo, 'love_stories', 'display_order') && !columnExists($pdo, 'love_stories', 'order')) {
          $pdo->exec("ALTER TABLE love_stories CHANGE COLUMN `display_order` `order` INT DEFAULT 0");
      }

      // Team
      if (!columnExists($pdo, 'team_members', 'order')) $pdo->exec("ALTER TABLE team_members ADD COLUMN `order` INT DEFAULT 0");
      
      // Popups (Subtitle, Link, Status)
      if (!columnExists($pdo, 'popups', 'subtitle')) $pdo->exec("ALTER TABLE popups ADD COLUMN subtitle TEXT NULL");
      if (!columnExists($pdo, 'popups', 'link')) $pdo->exec("ALTER TABLE popups ADD COLUMN link VARCHAR(255) NULL");
      if (!columnExists($pdo, 'popups', 'status')) $pdo->exec("ALTER TABLE popups ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active'");
      
      // Ensure Users status exists and is correct
      if (!columnExists($pdo, 'users', 'status')) {
          $pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(50) DEFAULT 'Active' AFTER role");
      }

     echo "<br><b>Database migration successful!</b>";

} catch (\PDOException $e) {
     echo "<br><b style='color:red;'>Database Error:</b> " . htmlspecialchars($e->getMessage());
}
