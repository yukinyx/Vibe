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
    $username = $_POST['username'] ?? '';
    $content = $_POST['content'] ?? '';
    $media = [];

    // Ensure the uploads directory exists
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Save uploaded files
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_FILES["file$i"]) && $_FILES["file$i"]['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES["file$i"]['name']);
            $filePath = "uploads/$fileName";
            if (move_uploaded_file($_FILES["file$i"]['tmp_name'], $filePath)) {
                $media[] = $fileName; // Add the file name to the media array
            }
        }
    }

    // Insert post into the database
    $stmt = $pdo->prepare("INSERT INTO posts (username, content, media) VALUES (:username, :content, :media)");
    $stmt->execute([
        ':username' => $username,
        ':content' => $content,
        ':media' => json_encode($media), // Store media as JSON
    ]);

    echo json_encode(['success' => true]);
}
?>