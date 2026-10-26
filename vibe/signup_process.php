<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  if ($password === $confirm) {
    echo "<h2>Signup Successful!</h2>";
    echo "<p>Welcome, $username ($email)!</p>";
  } else {
    echo "<h2>Passwords do not match.</h2>";
  }
}
?>
