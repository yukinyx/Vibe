<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vibe";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set default session variables if not set
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Guest';
}
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'guest';
}
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
  $_SESSION['profile_pic'] = 'default.jpg';
}

// Fetch user posts with poster info, total heart count, and whether user hearted
$user_posts_query = "
    SELECT 
        posts.*, 
        users.name AS poster_name, 
        users.profile_pic AS poster_pic,
        EXISTS(SELECT 1 FROM hearts WHERE hearts.post_id = posts.id AND hearts.username = ?) AS hearted_by_user,
        (SELECT COUNT(*) FROM hearts WHERE hearts.post_id = posts.id) AS total_hearts
    FROM posts
    JOIN users ON posts.username = users.username
    WHERE posts.username = ?
    ORDER BY posts.created_at DESC";


    $stmt = $conn->prepare($user_posts_query);
    $stmt->bind_param('ss', $_SESSION['username'], $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    

// Handle heart toggle and count update
if (isset($_POST['post_id'])) {
    $postId = $_POST['post_id'];
    $username = $_SESSION['username'];

    // Check if the user already hearted this post
    $checkHeartQuery = "SELECT * FROM hearts WHERE post_id = ? AND username = ?";
    $stmt = $conn->prepare($checkHeartQuery);
    $stmt->bind_param('is', $postId, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If user already hearted the post, remove the heart
        $deleteHeartQuery = "DELETE FROM hearts WHERE post_id = ? AND username = ?";
        $stmt = $conn->prepare($deleteHeartQuery);
        $stmt->bind_param('is', $postId, $username);
        $stmt->execute();
        $status = 'unhearted'; // Post is unhearted
    } else {
        // If user has not hearted the post, add the heart
        $insertHeartQuery = "INSERT INTO hearts (post_id, username) VALUES (?, ?)";
        $stmt = $conn->prepare($insertHeartQuery);
        $stmt->bind_param('is', $postId, $username);
        $stmt->execute();
        $status = 'hearted'; // Post is hearted
    }

    // Get the updated heart count
    $heartCountQuery = "SELECT COUNT(*) as heart_count FROM hearts WHERE post_id = ?";
    $stmt = $conn->prepare($heartCountQuery);
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $heartCount = $row['heart_count'];

    // Return the updated heart status and count as JSON
    echo json_encode([
        'status' => $status,
        'heartCount' => $heartCount
    ]);
    exit(); // Stop further script execution
}

// Profile update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $profilePic = $_FILES['profilePic'] ?? null;

  if (!empty($name)) {
      if ($profilePic && $profilePic['error'] === UPLOAD_ERR_OK) {
          $uploadDir = 'uploads/';
          if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true);
          }

          $fileName = uniqid() . '_' . basename($profilePic['name']);
          $filePath = $uploadDir . $fileName;

          if (move_uploaded_file($profilePic['tmp_name'], $filePath)) {
              $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE username = ?");
              $stmt->bind_param('ss', $fileName, $_SESSION['username']);
              $stmt->execute();
              $_SESSION['profile_pic'] = $fileName;
          }
      } else {
          // If no profile picture is uploaded, ensure it defaults to default.jpg
          if (empty($_SESSION['profile_pic']) || $_SESSION['profile_pic'] === 'default.jpg') {
              $defaultPic = 'default.jpg';
              $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE username = ?");
              $stmt->bind_param('ss', $defaultPic, $_SESSION['username']);
              $stmt->execute();
              $_SESSION['profile_pic'] = $defaultPic;
          }
      }

      // Update the user's name
      $stmt = $conn->prepare("UPDATE users SET name = ? WHERE username = ?");
      $stmt->bind_param('ss', $name, $_SESSION['username']);
      $stmt->execute();

      $_SESSION['name'] = $name;

      header('Location: profile.php');
      exit();
  }
}

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
  $postId = $_POST['delete_post_id'];
  $username = $_SESSION['username'];

  // Ensure the logged-in user owns the post
  $checkPostQuery = "SELECT * FROM posts WHERE id = ? AND username = ?";
  $stmt = $conn->prepare($checkPostQuery);
  $stmt->bind_param('is', $postId, $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      // Delete the post
      $deletePostQuery = "DELETE FROM posts WHERE id = ?";
      $stmt = $conn->prepare($deletePostQuery);
      $stmt->bind_param('i', $postId);
      $stmt->execute();

      // Optionally, delete associated hearts
      $deleteHeartsQuery = "DELETE FROM hearts WHERE post_id = ?";
      $stmt = $conn->prepare($deleteHeartsQuery);
      $stmt->bind_param('i', $postId);
      $stmt->execute();

      echo json_encode(['status' => 'success', 'message' => 'Post deleted successfully.']);
  } else {
      echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this post.']);
  }
  exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Page</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="profile.css">
</head>
<body>
  <div class="main-wrapper">
    <div class="layout">
      <div class="sidebar">
        <img class="main-profile-img" src="img/VIBE.png" onclick="location.href='home.php'"/>
        <div class="menu">
          <div class="menu-item" onclick="location.href='home.php'">
            <img src="img/home.svg" class="icons" />Home
          </div>
          <div class="menu-item" onclick="location.href='notifications.php'">
            <img src="img/bell.svg" class="icons" />Notifications
          </div>
          <div class="menu-item" onclick="location.href='profile.php'">
            <img src="img/profile.svg" class="icons" />Profile
          </div>
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
        <div class="post-box">
          <img src="img/back.svg" class="icons1" onclick="location.href='home.php'" />
        </div>
        <div class="mainprofile-wrapper">
        <img class="profile-pic-img" src="uploads/<?php echo htmlspecialchars($_SESSION['profile_pic'] ?? 'default.jpg'); ?>" alt="Profile Picture" />
          <div class="main-profile">
            <div class="profile-name1"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
            <div class="profile-handle1">@<?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <div class="edit-btn">Edit Profile</div>
          </div>
        </div>

        <?php foreach ($posts as $post): ?>
  <div class="post" data-post-id="<?= $post['id'] ?>">
  <div class="post-header">
    <img src="uploads/<?= isset($post['poster_pic']) && !empty($post['poster_pic']) ? htmlspecialchars($post['poster_pic']) : 'default.jpg' ?>" 
         alt="Profile Picture" 
         style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />
    <span class="post-username"><?= htmlspecialchars($post['poster_name']) ?></span>

    <!-- Show delete button only if the logged-in user is the poster -->
    <?php if ($post['username'] === $_SESSION['username']): ?>
        <button class="delete-button1" onclick="deletePost(<?= $post['id'] ?>)">Delete</button>
    <?php endif; ?>
</div>
    <p><?= htmlspecialchars($post['content']) ?></p>

    <?php
    $media = json_decode($post['media'], true); // Decode the JSON media column
    if (!empty($media)) {
        foreach ($media as $file) {
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                echo '<img src="uploads/' . htmlspecialchars($file) . '" alt="Post Image" class="post-image" />';
            } elseif (in_array($fileExtension, ['mp4', 'webm', 'ogg'])) {
                echo '<video controls class="post-video"><source src="uploads/' . htmlspecialchars($file) . '" type="video/' . htmlspecialchars($fileExtension) . '"></video>';
            }
        }
    }
    ?>

<div class="post-footer">
<span class="heart" onclick="toggleHeart(this, <?= $post['id'] ?>)" 
      style="color: <?= $post['hearted_by_user'] ? '#bc6ec7' : '#000'; ?>; font-size: <?= $post['hearted_by_user'] ? '1.5em' : '1em'; ?>">
    <?= $post['hearted_by_user'] ? '&#x2665;' : '&#x2661;'; ?>
</span>
<span class="heart-count"><?= $post['total_hearts'] ?></span>

</div>

  </div>
<?php endforeach; ?>

        
      </div>

      <div class="pro-container">
        <!-- Edit Profile Modal -->
        <div id="editProfileModal" class="modal" style="display: none;">
          <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form id="editProfileForm" method="POST" enctype="multipart/form-data">
              <label for="name">Name:</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>">
              <label for="profilePic">Profile Picture:</label>
              <input type="file" id="profilePic" name="profilePic" accept="image/*">
              <button type="submit" class="save-button">Save</button>
            </form>
          </div>
        </div>

        <!-- Profile Information Display -->
        <div class="profile-wrapper">
          <div class="profile-pic">
            <img src="uploads/<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Profile Picture" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />
          </div>
          <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
            <div class="profile-handle">@<?php echo htmlspecialchars($_SESSION['username']); ?></div>
          </div>
          <div class="profile-menu-wrapper">
            <div class="profile-dots">...</div>
            <div class="profile-popup-menu">
            <div class="logout-button" onclick="openLogoutModal()">
    <div class="button-text">Log out</div>
</div>
              
              <div class="delete-button" onclick="openModal1()">
    <div class="button-text">Delete Account</div>
</div>
        </div>
    </div>
</div>


<div id="deleteAccountModal" class="modal1" style="display: none;">
    <div class="modal-content1">
        <h2>Delete Account</h2>
        <p>Are you sure you want to delete your account? You will have 7 days to recover it before it is permanently deleted.</p>
        <form method="POST" action="delete_account.php">
            <button type="submit" name="confirm_delete" class="confirm-button">Yes, Delete My Account</button>
            <button type="button" class="cancel-button" onclick="closeModal1()">Cancel</button>
        </form>
    </div>
</div>
  

<!-- About Us Popup -->
<div id="aboutUsPopup" class="popup" style="display: none;">
    <div class="popup-content">
        <button class="close-button1" onclick="closePopup('aboutUsPopup')">&larr; Back</button>
        <h2>About Us</h2>
        <p>Vibe is a community where you can share, connect, and be yourself — anytime, anywhere. Whether it's something you wanted to say, something funny, or something that matters, Vibe allows you to make real-time posts and check out what others are sharing as well.</p>
        <p>We created Vibe so that people can stay connected, share what matters, and be themselves. It's about real people, and real vibes.</p>
        <p>So go ahead — connect, share, and express your vibe.</p>
    </div>
</div>

<!-- Contact Us Popup -->
<div id="contactUsPopup" class="popup" style="display: none;">
    <div class="popup-content">
        <button class="close-button1" onclick="closePopup('contactUsPopup')">&larr; Back</button>
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
function openModal1() {
    document.getElementById('deleteAccountModal').style.display = 'flex';
}

function closeModal1() {
    document.getElementById('deleteAccountModal').style.display = 'none';
}
</script> <!-- End main-wrapper -->

  <script>
    // Close modal when clicking outside the modal content
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('editProfileModal');
      const content = document.querySelector('.modal-content');

      if (modal.style.display === 'flex' && !content.contains(event.target) && !event.target.classList.contains('edit-btn')) {
        closeModal();
      }
    });

    // Toggle heart logic (unchanged)
  function toggleHeart(element, postId) {
    let heartIcon = element;
    let heartCount = heartIcon.nextElementSibling;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "profile.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);

        if (response.status === "hearted") {
          heartIcon.style.color = "#bc6ec7";
          heartIcon.innerHTML = "&#x2665;";
        } else {
          heartIcon.style.color = "#000";
          heartIcon.innerHTML = "&#x2661;";
        }

        heartCount.innerHTML = response.heartCount;

        // Optional: update all matching posts in feed
        const allHearts = document.querySelectorAll('.post[data-post-id="' + postId + '"] .heart-count');
        allHearts.forEach(function (countElement) {
          countElement.innerHTML = response.heartCount;
        });
      }
    };

    xhr.send("post_id=" + postId);
  }

  // ✅ Moved outside so it's available globally
  function toggleDropdown() {
    const dropdown = document.getElementById("moreDropdown");
    dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
  }

  // Close dropdown when clicking elsewhere
  document.addEventListener("click", function (event) {
    const dropdown = document.getElementById("moreDropdown");
    const wrapper = document.querySelector(".more-menu-wrapper");
    if (!wrapper.contains(event.target)) {
      dropdown.style.display = "none";
    }
  });

  function openModal() {
    document.getElementById('editProfileModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('editProfileModal').style.display = 'none';
  }

  document.querySelector('.edit-btn').addEventListener('click', openModal);

  document.addEventListener('DOMContentLoaded', () => {
    const profileMenuWrapper = document.querySelector('.profile-menu-wrapper');
    profileMenuWrapper.addEventListener('click', (event) => {
      event.stopPropagation();
      profileMenuWrapper.classList.toggle('active');
    });

    document.addEventListener('click', () => {
      profileMenuWrapper.classList.remove('active');
    });
  });
  </script>
<script>
function deletePost(postId) {
    if (confirm("Are you sure you want to delete this post?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "profile.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === "success") {
                    // Remove the post from the DOM
                    const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
                    if (postElement) {
                        postElement.remove();
                    }
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            }
        };

        xhr.send("delete_post_id=" + postId);
    }
}
</script>
</body>
</html>