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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['post_id'] ?? 0;
    $username = $_SESSION['username'] ?? '';

    // Check if the user already hearted the post
    $stmt = $pdo->prepare("SELECT * FROM hearts WHERE post_id = :post_id AND username = :username");
    $stmt->execute([':post_id' => $postId, ':username' => $username]);
    $heart = $stmt->fetch();

    if ($heart) {
        // Remove heart
        $stmt = $pdo->prepare("DELETE FROM hearts WHERE post_id = :post_id AND username = :username");
        $stmt->execute([':post_id' => $postId, ':username' => $username]);

        // Decrement heart count
        $stmt = $pdo->prepare("UPDATE posts SET heart_count = heart_count - 1 WHERE id = :post_id");
        $stmt->execute([':post_id' => $postId]);

        echo json_encode(['hearted' => false]);
    } else {
        // Add heart
        $stmt = $pdo->prepare("INSERT INTO hearts (post_id, username) VALUES (:post_id, :username)");
        $stmt->execute([':post_id' => $postId, ':username' => $username]);

        // Increment heart count
        $stmt = $pdo->prepare("UPDATE posts SET heart_count = heart_count + 1 WHERE id = :post_id");
        $stmt->execute([':post_id' => $postId]);

        // Get the post owner's username
        $stmt = $pdo->prepare("SELECT username FROM posts WHERE id = :post_id");
        $stmt->execute([':post_id' => $postId]);
        $postOwner = $stmt->fetchColumn();

        // Create a notification for the post owner
        if ($postOwner && $postOwner !== $username) {
            $stmt = $pdo->prepare("INSERT INTO notifications (recipient_username, sender_username, post_id, type) VALUES (:recipient_username, :sender_username, :post_id, 'heart')");
            $stmt->execute([
                ':recipient_username' => $postOwner,
                ':sender_username' => $username,
                ':post_id' => $postId,
            ]);
        }

        echo json_encode(['hearted' => true]);
    }
}
?>