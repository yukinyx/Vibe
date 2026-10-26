<?php
$host = "localhost";
$db = "vibe";
$user = "root";
$pass = "";
$conn = new mysqli($host, $user, $pass, $db);

// Check for DB connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize user inputs
  $username = htmlspecialchars(trim($_POST["username"] ?? ''));
  $email = htmlspecialchars(trim($_POST["email"] ?? ''));
  $password = $_POST["password"] ?? '';
  $confirm = $_POST["confirm"] ?? '';

  // Validate email domain
  $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
  $email_domain = strtolower(substr(strrchr($email, "@"), 1));
  $is_allowed_email = in_array($email_domain, $allowed_domains) || str_ends_with($email_domain, '.edu.ph');

  // Validate password strength
  $is_strong_password = preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password);

  // Validate password confirmation
  $passwords_match = $password === $confirm;

  if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
      $message = "All fields are required.";
      $messageClass = "error";
  } elseif (!$is_allowed_email) {
      $message = "Email domain not allowed. Please use a supported email address.";
      $messageClass = "error";
  } elseif (!$is_strong_password) {
      $message = "Password must be at least 8 characters long, with uppercase, lowercase, number, and special symbol.";
      $messageClass = "error";
  } elseif (!$passwords_match) {
      $message = "Passwords do not match.";
      $messageClass = "error";
  } else {
      // Check if username already exists
      $check_username_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
      $check_username_stmt->bind_param("s", $username);
      $check_username_stmt->execute();
      $check_username_stmt->store_result();

      if ($check_username_stmt->num_rows > 0) {
          $message = "Signup failed. This username is already taken.";
          $messageClass = "error";
      } else {
          // Check if email already exists
          $check_email_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
          $check_email_stmt->bind_param("s", $email);
          $check_email_stmt->execute();
          $check_email_stmt->store_result();

          if ($check_email_stmt->num_rows > 0) {
              $message = "Signup failed. This email is already registered.";
              $messageClass = "error";
          } else {
              // Proceed with database insert
              $hashed = password_hash($password, PASSWORD_DEFAULT);
              $default_name = $username; // or you can leave it empty if you prefer
$default_pic = 'default.jpg'; // make sure this image exists in your uploads folder
$stmt = $conn->prepare("INSERT INTO users (username, email, password, name, profile_pic) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $email, $hashed, $default_name, $default_pic);


              if ($stmt->execute()) {
                  $message = "Signup successful! Welcome, $username";
                  $messageClass = "success";
                  $_SESSION['user_id'] = $newUserId; // after insert
                  $_SESSION['username'] = $username;

                  
              } else {
                  $message = "Signup failed due to a system error. Please try again.";
                  $messageClass = "error";
              }
              $stmt->close();
          }
          $check_email_stmt->close();
      }
      $check_username_stmt->close();
  }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vibe Signup</title>
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&family=Inria+Serif:wght@700&family=Inter:wght@400;300&family=Quicksand&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="signup.css"/>
</head>

<body>
  <?php if (!empty($message)) : ?>
    <div class="popup <?= $messageClass ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="center-wrapper">
    <form method="POST">
      <div class="signup-container">
        <div style="width: 729px; height: 580px; left: 144px; top: 120px; position: absolute; background:#e2a4ff; border-radius: 50px; border: 3px black solid"></div>
        <img style="width: 189px; height: 194px; left: 405px; top: 0px; position: absolute; border-radius: 9999px; border: 3px black solid" src="icon.jpg" />
        
        <input class="input" name="username" type="text" placeholder="Username" required style="position:absolute; width:561px; height:50px; left:505px; top:265px; background:#ffd4f2; border:1px solid black; padding:10px; font-size:18px;"/>
        <input class="input" name="email" type="email" placeholder="Email" required style="position:absolute; width:561px; height:50px; left:505px; top:340px; background:#ffd4f2; border:1px solid black; padding:10px; font-size:18px;"/>

        <input class="input" id="password" name="password" type="password" placeholder="Password" required 
       style="position:absolute; width:561px; height:50px; left:505px; top:415px; background:#ffd4f2; border:1px solid black; padding:10px; font-size:18px;" 
       oninput="toggleIconVisibility('password')" />
<span id="password-icon" onclick="togglePasswordVisibility('password')" 
      style="position:absolute; left:750px; top:430px; cursor:pointer; font-size:18px; display:none;">👁️</span>

<input class="input" id="confirm" name="confirm" type="password" placeholder="Confirm Password" required 
       style="position:absolute; width:561px; height:50px; left:505px; top:490px; background:#ffd4f2; border:1px solid black; padding:10px; font-size:18px;" 
       oninput="toggleIconVisibility('confirm')" />
<span id="confirm-icon" onclick="togglePasswordVisibility('confirm')" 
      style="position:absolute; left:750px; top:505px; cursor:pointer; font-size:18px; display:none;">👁️</span>

        <button type="submit" class="submit-btn" style="position:absolute; width:215px; height:59px; left:500px; top:575px; background:#CA54F1; border-radius:60px; border:3px solid black; font-size:18px;">CREATE ACCOUNT</button>
        
        <div class="signup-title" style="position: absolute; left: 430px; top: 205px; font-size: 36px; font-weight: bold;">SIGN UP</div>

        <div style="position: absolute; top: 650px; left: 360px;">
          <span class="login-text">Already have an account? </span>
          <span class="login-link" onclick="window.parent.showLogin()">Login here</span>
        </div>
      </div>
    </form>
  </div>
  <script>
  function togglePasswordVisibility(id) {
  const input = document.getElementById(id);
  const icon = document.getElementById(id + '-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = '🙈'; // Change icon to "hide"
  } else {
    input.type = 'password';
    icon.textContent = '👁️'; // Change icon to "show"
  }
}

function toggleIconVisibility(id) {
  const input = document.getElementById(id);
  const icon = document.getElementById(id + '-icon');
  if (input.value.length > 0) {
    icon.style.display = 'inline'; 
  } else {
    icon.style.display = 'none'; 
  }
}
  </script>
</body>
</html>

        
      
        
    