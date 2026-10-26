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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$loggedInUser = $_SESSION['username'] ?? '';
$postId = $_POST['post_id'] ?? '';

if ($loggedInUser && $postId) {
    // Check if the user has already liked the post
    $stmt = $pdo->prepare("SELECT * FROM hearts WHERE post_id = :post_id AND username = :username");
    $stmt->execute([':post_id' => $postId, ':username' => $loggedInUser]);
    $isHearted = $stmt->fetch();

    if ($isHearted) {
        // If the user has already liked the post, remove the like
        $stmt = $pdo->prepare("DELETE FROM hearts WHERE post_id = :post_id AND username = :username");
        $stmt->execute([':post_id' => $postId, ':username' => $loggedInUser]);
    } else {
        // Otherwise, add the like
        $stmt = $pdo->prepare("INSERT INTO hearts (post_id, username) VALUES (:post_id, :username)");
        $stmt->execute([':post_id' => $postId, ':username' => $loggedInUser]);
    }

    // Get the updated total hearts count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM hearts WHERE post_id = :post_id");
    $stmt->execute([':post_id' => $postId]);
    $totalHearts = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'hearted' => !$isHearted, 'total_hearts' => $totalHearts]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}