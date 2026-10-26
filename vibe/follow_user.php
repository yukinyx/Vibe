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
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}

$loggedInUser = $_SESSION['username'] ?? '';
$followedUser = $_POST['followed_user'] ?? '';
$action = $_POST['action'] ?? '';

if ($loggedInUser && $followedUser) {
    if ($action === 'follow') {
        // Insert follow relationship
        $stmt = $pdo->prepare("INSERT INTO follows (follower_username, followed_username) VALUES (:follower, :followed)");
        $stmt->execute([':follower' => $loggedInUser, ':followed' => $followedUser]);

        // Insert notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (recipient_username, sender_username, post_id, type, created_at)
            VALUES (:recipient_username, :sender_username, NULL, 'follow', NOW())
        ");
        $stmt->execute([
            ':recipient_username' => $followedUser,
            ':sender_username' => $loggedInUser
        ]);

        echo json_encode(['success' => true, 'message' => 'Followed successfully!']);
    } elseif ($action === 'unfollow') {
        // Delete follow relationship
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_username = :follower AND followed_username = :followed");
        $stmt->execute([':follower' => $loggedInUser, ':followed' => $followedUser]);

        echo json_encode(['success' => true, 'message' => 'Unfollowed successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid follow action.']);
}