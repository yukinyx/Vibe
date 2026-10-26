<?php
session_start();

$host = 'localhost';
$dbname = 'vibe';
$username = 'root';
$password = '';

$message = '';
$messageClass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$inputUsername = ''; // to keep value in form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $inputUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($inputPassword, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            echo "<script>window.top.location.href = 'home.php';</script>";
            exit();

        } else {
            $message = 'Login failed. Incorrect password.';
            $messageClass = 'error';
        }
    } else {
        $message = 'Login failed. This username is not registered.';
        $messageClass = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vibe Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&family=Inria+Serif:wght@700&family=Inter:wght@400;300&family=Quicksand&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css" />
  <style>
    .popup.success {
      background-color: #c5f7c5;
      color: #2e7d32;
      padding: 10px 20px;
      border: 2px solid #2e7d32;
      border-radius: 10px;
      position: absolute;
      top: 100px;
      left: 50%;
      transform: translateX(-50%);
      font-weight: bold;
      font-size: 16px;
      z-index: 9999;
    }

    .popup.error {
      background-color: #ffd1d1;
      color: #b71c1c;
      padding: 10px 20px;
      border: 2px solid #b71c1c;
      border-radius: 10px;
      position: absolute;
      top: 100px;
      left: 50%;
      transform: translateX(-50%);
      font-weight: bold;
      font-size: 16px;
      z-index: 9999;
    }
  </style>
</head>

<body>
<div class="center-wrapper">
  <div class="login-container" style="position: relative;">
    <?php if (!empty($message)) : ?>
      <div class="popup <?= htmlspecialchars($messageClass) ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div style="width: 769px; height: 476px; left: 115px; top: 120px; position: absolute; background:#e2a4ff; border-radius: 50px; border: 3px black solid"></div>
    <img style="width: 189px; height: 194px; left: 405px; top: 0px; position: absolute; border-radius: 9999px; border: 3px black solid" src="icon.jpg" />

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
      <input type="text" name="username" placeholder="Username" required class="username"
        value="<?= htmlspecialchars($inputUsername) ?>"
        style="position:absolute; width:561px; height:50px; left:220px; top:265px; background:#ffd4f2; border:1px solid black; padding:10px; font-size:18px;" />

      <input class="input" id="password" name="password" type="password" placeholder="Password" required
        style="position:absolute; width:561px; height:50px; left:220px; top:345px; background:#ffd4f2; border:1px solid black; padding:10px; font-size:18px;"
        oninput="toggleIconVisibility('password')" />

      <span id="password-icon" onclick="togglePasswordVisibility('password')"
        style="position:absolute; left:760px; top:370px; cursor:pointer; font-size:18px; display:none;">👁️</span>

      
      <button type="submit" class="login-button"
        style="position:absolute; width:215px; height:59px; left:390px; top:450px; background:#CA54F1; border-radius:60px; border:3px solid black; font-size:18px; color:white; cursor:pointer;">
        LOGIN
      </button>
    </form>

    <div class="login-title" style="position: absolute; left: 425px; top: 200px; font-size: 36px; font-weight: bold;">LOGIN</div>
    <div style="position: absolute; top: 550px; left: 360px;">
      <span class="register-text">Don’t have an account? </span>
      <span class="register-link" onclick="window.parent.showSignup()">Register here</span>
    </div>
  </div>
</div>

<script>
function togglePasswordVisibility(id) {
  const input = document.getElementById(id);
  const icon = document.getElementById(id + '-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = '🙈';
  } else {
    input.type = 'password';
    icon.textContent = '👁️';
  }
}

function toggleIconVisibility(id) {
  const input = document.getElementById(id);
  const icon = document.getElementById(id + '-icon');
  icon.style.display = input.value.length > 0 ? 'inline' : 'none';
}

window.addEventListener('DOMContentLoaded', () => {
  const popup = document.querySelector('.popup.error');
  if (popup) {
    setTimeout(() => {
      popup.style.display = 'none';
    }, 5000);
  }
});
</script>
</body>
</html>
