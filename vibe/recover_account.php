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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recover_account'])) {
    $username = $_POST['username'];

    // Remove the deletion request
    $stmt = $pdo->prepare("
        UPDATE users 
        SET deletion_requested_at = NULL, 
            deletion_expires_at = NULL 
        WHERE username = :username
    ");
    $stmt->execute([':username' => $username]);

    $message = "Your account has been successfully recovered!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="recover_account.css">
</head>
<body>
    <div class="recover-wrapper">
        <div class="recover-content">
            <img src="img/VIBE.png" alt="Vibe Logo" class="recover-logo">
            <h1>Recover Your Account</h1>
            <p>Enter your username to recover your account. If your account is eligible for recovery, it will be restored immediately.</p>
            <?php if ($message): ?>
                <div class="success-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" action="recover_account.php">
                <input type="text" name="username" placeholder="Enter your username" required class="recover-input">
                <button type="submit" name="recover_account" class="recover-button">Recover Account</button>
            </form>
            <a href="main.php" class="back-link">Back to Login</a>
        </div>
    </div>
</body>
</html>