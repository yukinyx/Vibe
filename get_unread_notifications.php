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
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

$loggedInUser = $_SESSION['username'] ?? '';

if ($loggedInUser) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE recipient_username = :username AND is_read = FALSE");
    $stmt->execute([':username' => $loggedInUser]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['unread_count' => $result['unread_count']]);
} else {
    echo json_encode(['unread_count' => 0]);
}
?>