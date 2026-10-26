<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodbye</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="goodbye.css">
</head>
<body>
    <div class="goodbye-wrapper">
        <div class="goodbye-content">
            <img src="img/VIBE.png" alt="Vibe Logo" class="goodbye-logo">
            <h1>Goodbye!</h1>
            <p>Your account deletion request has been received. You have 7 days to recover your account before it is permanently deleted.</p>
            <p>If you change your mind, you can <a href="recover_account.php" class="recover-link">recover your account</a> within the grace period.</p>
            <a href="main.php" class="goodbye-button">Return to Login</a>
        </div>
    </div>
</body>
</html>