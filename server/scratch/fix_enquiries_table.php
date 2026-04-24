<?php

$host = 'localhost';
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
    
    echo "Connected to database successfully.\n";
    
    // Add events and services columns to enquiries table
    $sql = "ALTER TABLE enquiries 
            ADD COLUMN IF NOT EXISTS events TEXT AFTER eventEndDate,
            ADD COLUMN IF NOT EXISTS services TEXT AFTER location";
    
    $pdo->exec($sql);
    echo "Columns 'events' and 'services' added to 'enquiries' table successfully.\n";
    
} catch (\PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
