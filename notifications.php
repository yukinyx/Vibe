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
    die('Database connection failed: ' . $e->getMessage());
}

$loggedInUser = $_SESSION['username'] ?? '';

// Retrieve notifications for the logged-in user
$stmt = $pdo->prepare("
    SELECT n.*, p.content AS post_content 
    FROM notifications n
    LEFT JOIN posts p ON n.post_id = p.id
    WHERE n.recipient_username = :username
    ORDER BY n.created_at DESC
");
$stmt->execute([':username' => $loggedInUser]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Mark all notifications as read
$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE recipient_username = :username");
$stmt->execute([':username' => $loggedInUser]);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="notifications.css">
</head>
<body>
  <div class="main-wrapper">
    <div class="layout">

        <div class="sidebar">
            <img class="main-profile-img" src="img/VIBE.png" onclick="location.href='home.php'"/>
            <div class="menu">
                <div class="menu-item"onclick="location.href='home.php'">
                    <img src="img/home.svg" class="icons" />Home</div>
                    
                <div class="menu-item"onclick="location.href='notifications.php'">
                    <img src="img/bell.svg" class="icons" />Notifications</div>
                
                <div class="menu-item"onclick="location.href='profile.php'">
                    <img src="img/profile.svg" class="icons"/>Profile</div>
                
                    <div class="menu-item more-menu-wrapper" onclick="toggleDropdown()">
    <img src="img/3dot.svg" class="icons" /> More
    <div class="dropdown-menu" id="moreDropdown">
        <div class="dropdown-item" onclick="openPopup('aboutUsPopup')">About Us</div>
        <div class="dropdown-item" onclick="openPopup('contactUsPopup')">Contact Us</div>
    </div>
</div>
            </div>
        </div>
        
        <div class="main-feed">
                <div class="post-box">Notifications</div>
                <div class="notifications-list">
    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification-item">
                <?php if ($notification['type'] === 'follow'): ?>
                    <p>
                        <strong><?= htmlspecialchars($notification['sender_username']) ?></strong>
                        started following you.
                    </p>
                <?php elseif ($notification['type'] === 'heart'): ?>
                    <p>
                        <strong><?= htmlspecialchars($notification['sender_username']) ?></strong>
                        hearted your post: "<?= htmlspecialchars($notification['post_content']) ?>"
                    </p>
                <?php endif; ?>
                <small><?= htmlspecialchars($notification['created_at']) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
            </div>
      
<div class="pro-container">
  <div class="profile-wrapper">
    <img src="uploads/<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Profile Picture" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />
    <div class="profile-info">
    <div class="profile-name"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?></div>
    <div class="profile-handle">@<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?></div>
    </div>
    <div class="profile-menu-wrapper">
      <div class="profile-dots">...</div>
      <div class="profile-popup-menu">
      <div class="logout-button" onclick="openLogoutModal()">
    <div class="button-text">Log out</div>
</div>
        
      <div class="delete-button" onclick="openModal()">
    <div class="button-text">Delete Account</div>
</div>
        </div>
    </div>
</div>


<div id="deleteAccountModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Delete Account</h2>
        <p>Are you sure you want to delete your account? You will have 7 days to recover it before it is permanently deleted.</p>
        <form method="POST" action="delete_account.php">
            <button type="submit" name="confirm_delete" class="confirm-button">Yes, Delete My Account</button>
            <button type="button" class="cancel-button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>
    
<!-- About Us Popup -->
<div id="aboutUsPopup" class="popup" style="display: none;">
    <div class="popup-content">
        <button class="close-button" onclick="closePopup('aboutUsPopup')">&larr; Back</button>
        <h2>About Us</h2>
        <p>Vibe is a community where you can share, connect, and be yourself — anytime, anywhere. Whether it's something you wanted to say, something funny, or something that matters, Vibe allows you to make real-time posts and check out what others are sharing as well.</p>
        <p>We created Vibe so that people can stay connected, share what matters, and be themselves. It's about real people, and real vibes.</p>
        <p>So go ahead — connect, share, and express your vibe.</p>
    </div>
</div>

<!-- Contact Us Popup -->
<div id="contactUsPopup" class="popup" style="display: none;">
    <div class="popup-content">
        <button class="close-button" onclick="closePopup('contactUsPopup')">&larr; Back</button>
        <h2>Contact Us</h2>
        <p>23-62335@g.batstate-u.edu.ph - Sta. Catalina, Erich W.</p>
        <p>23-64828@g.batstate-u.edu.ph - Santos, Patricia Nicole</p>
        <p>23-64856@g.batstate-u.edu.ph - Aranda, Ralph A.</p>
        <p>23-68354@g.batstate-u.edu.ph - Taon, Lhan Arlo</p>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal2" style="display: none;">
    <div class="modal-content2" style="
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        font-family: 'Inter', sans-serif;
        color: black;
    ">
        <h2>Confirm Logout</h2>
        <p>Do you really want to logout?</p>
        <div style="margin-top: 20px;">
            <button class="confirm-button2" onclick="confirmLogout()" style="
                background:rgb(189, 92, 169);
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-right: 10px;
            ">Yes, Logout</button>
            <button class="cancel-button2" onclick="closeLogoutModal()" style="
                background: #ddd;
                color: black;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            ">Cancel</button>
        </div>
    </div>
</div>

    </div>
  </div>

  <script>
    function openLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

function confirmLogout() {
    window.location.href = 'main.php'; // Redirect to the logout page or main page
}
</script>

<script>
    function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'flex';
}

function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
}
</script>

<script>
function openModal() {
    document.getElementById('deleteAccountModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('deleteAccountModal').style.display = 'none';
}
</script>
  
  <script>
function toggleDropdown() {
    const dropdown = document.getElementById("moreDropdown");
    dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
}

document.addEventListener("click", function(event) {
    const dropdown = document.getElementById("moreDropdown");
    const wrapper = document.querySelector(".more-menu-wrapper");
    if (!wrapper.contains(event.target)) {
        dropdown.style.display = "none";
    }
});
  </script>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
  const profileMenuWrapper = document.querySelector('.profile-menu-wrapper');
  const profilePopupMenu = document.querySelector('.profile-popup-menu');

  profileMenuWrapper.addEventListener('click', (event) => {
    event.stopPropagation(); 
    profileMenuWrapper.classList.toggle('active');
  });

  document.addEventListener('click', () => {
    profileMenuWrapper.classList.remove('active');
    });
  });
  </script>
  
</body>
</html>