<?php
$host = 'localhost';
$dbname = 'vibe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Delete accounts where the deletion grace period has expired
$stmt = $pdo->prepare("
    DELETE FROM users 
    WHERE deletion_expires_at IS NOT NULL 
      AND deletion_expires_at <= NOW()
");
$stmt->execute();
?>