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

    // Check if the post belongs to the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :post_id AND username = :username");
    $stmt->execute([':post_id' => $postId, ':username' => $username]);
    $post = $stmt->fetch();

    if ($post) {
        // Delete the post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
        $stmt->execute([':post_id' => $postId]);

        // Delete associated media files
        $media = json_decode($post['media'], true);
        if (!empty($media)) {
            foreach ($media as $file) {
                $filePath = 'uploads/' . $file;
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the file
                }
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Post not found or unauthorized']);
    }
}
?>