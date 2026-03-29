<?php

// Database configuration (matches .env)
$host = '127.0.0.1';
$db   = 'patil_photography';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
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
     echo "Orders table checked/created.\n";

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
     echo "Assets table checked/created.\n";

     // 3. Ensure users table has status and phone if missing
     $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(50) NULL AFTER email;");
     $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'Active' AFTER role;");
     
     // 4. Ensure services table has ratePerDay if missing
     $pdo->exec("ALTER TABLE services ADD COLUMN IF NOT EXISTS ratePerDay DECIMAL(15,2) DEFAULT 0 AFTER basePrice;");

     // 5. Ensure sliders table has order if missing
     $pdo->exec("ALTER TABLE sliders ADD COLUMN IF NOT EXISTS `order` INT DEFAULT 0;");
     $pdo->exec("ALTER TABLE sliders ADD COLUMN IF NOT EXISTS `subtitle` VARCHAR(255) NULL;");

     echo "Users, Services and Sliders tables updated.\n";

} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
