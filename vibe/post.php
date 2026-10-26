<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'vibe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $username = $_SESSION['username'];
    $content = trim($_POST['content']);
    $media = [];

    // Ensure the uploads directory exists
    $uploadDir = 'uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Save uploaded files
    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['files']['name'][$key]);
                $filePath = $uploadDir . '/' . $fileName;
                if (move_uploaded_file($tmpName, $filePath)) {
                    $media[] = $fileName;
                }
            }
        }
    }

    // Insert post into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (username, content, media) VALUES (:username, :content, :media)");
        $stmt->execute([
            ':username' => $username,
            ':content' => $content,
            ':media' => json_encode($media),
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to create the post.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
}