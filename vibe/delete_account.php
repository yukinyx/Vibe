<?php
session_start();

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

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$loggedInUser = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Set the deletion request timestamp and expiration date (7 days from now)
    $stmt = $pdo->prepare("
        UPDATE users 
        SET deletion_requested_at = NOW(), 
            deletion_expires_at = DATE_ADD(NOW(), INTERVAL 7 DAY) 
        WHERE username = :username
    ");
    $stmt->execute([':username' => $loggedInUser]);

    // Log the user out
    session_destroy();
    header("Location: goodbye.php");
    exit();
}
?>