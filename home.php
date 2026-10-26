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
    die("Database connection failed: " . $e->getMessage());
}
// Determine which user's profile to show
if (isset($_GET['user'])) {
    // If 'user' is set in the URL, show that user's profile
    $profileUsername = $_GET['user'];
} else {
    // If no 'user' is set, show the logged-in user's profile
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
    $profileUsername = $_SESSION['username'];
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $content = $_POST['content'] ?? '';
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
                    $media[] = $fileName; // Add the file name to the media array
                }
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

    header("Location: home.php"); // Refresh the page after submission
    exit();
}


$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$searchResults = [];

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = '%' . trim($_GET['query']) . '%';

    $stmt = $pdo->prepare("SELECT username, name, profile_pic FROM users WHERE username LIKE :query OR name LIKE :query");
    $stmt->execute([':query' => $query]);

    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Retrieve posts with hearted state for the logged-in user
$stmt = $pdo->prepare("
    SELECT p.*, 
           u.name AS poster_name,
           u.profile_pic AS poster_pic,
           EXISTS(SELECT 1 FROM hearts h WHERE h.post_id = p.id AND h.username = :username) AS hearted
    FROM posts p
    JOIN users u ON p.username = u.username
    ORDER BY p.created_at DESC
");


$stmt->execute([':username' => $_SESSION['username']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="home.css">
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
            <div class="post-box">
                <form method="POST" enctype="multipart/form-data">
                    <textarea name="content" placeholder="What's happening?"></textarea>
                    <button type="button" class="upload-button" onclick="document.getElementById('imageUpload').click()">+</button>
                    <input type="file" id="imageUpload" name="files[]" accept="image/*" multiple style="display: none;" />
                    <span id="imageCount" style="font-size: 14px; color: #555;">No images attached</span>
                    <button type="submit" class="vibe-button">Vibe</button>
                </form>
            </div>
            <div class="feed" id="feed">
            <?php foreach ($posts as $post): ?>
    <div class="post" data-post-id="<?= $post['id'] ?>">
        <div class="post-header">
        <img src="uploads/<?= htmlspecialchars($post['poster_pic']) ?>" alt="Profile Picture" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" />

        <span class="post-username"><?= htmlspecialchars($post['poster_name']) ?></span>

            <?php if ($post['username'] === $_SESSION['username']): ?>
                <button class="delete-button1" onclick="deletePost(<?= $post['id'] ?>)">Delete</button>
            <?php endif; ?>
        </div>
        <p><?= htmlspecialchars($post['content']) ?></p>
        <?php
        $media = json_decode($post['media'], true);
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
            <span class="heart" onclick="toggleHeart(this)" style="color: <?= $post['hearted'] ? '#bc6ec7' : '#000'; ?>; font-size: <?= $post['hearted'] ? '1.5em' : '1em'; ?>">
                <?= $post['hearted'] ? '&#x2665;' : '&#x2661;'; ?>
            </span>
            <span class="heart-count"><?= isset($post['heart_count']) ? $post['heart_count'] : 0 ?></span>
        </div>
    </div>
<?php endforeach; ?>
            </div>
        </div>
   
      
<div class="pro-container">
<?php if (!empty($searchResults)): ?>
    <div class="search-results">
    <h3>Search Results:</h3>
    <?php foreach ($searchResults as $user): ?>
        <div class="search-user">
            <img src="uploads/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile" class="search-user-img" />
            <div class="search-user-info">
                <span class="search-name"><?= htmlspecialchars($user['name']) ?> (@<?= htmlspecialchars($user['username']) ?>)</span>
                <button class="view-profile-button" onclick="location.href='view_profile.php?user=<?= urlencode($user['username']) ?>'">View Profile</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php elseif (isset($_GET['query'])): ?>
    <p>No users found for "<?= htmlspecialchars($_GET['query']) ?>"</p>
<?php endif; ?>

<div class="search-container">
    <form method="GET" action="">
        <input type="text" name="query" placeholder="Search for users..." required class="search-input">
        <button type="submit" class="search-button">Search</button>
    </form>
</div>

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

<!-- Explicit Words Popup -->
<div id="explicitWordsModal" class="modal1" style="display: none;">
    <div class="modal-content1" style="
        background: #ffc0cb;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        font-family: 'Inter', sans-serif;
        color: black;
    ">
        <h2>Restricted Content</h2>
        <p id="explicitWordsMessage">Your post contains inappropriate language and cannot be submitted.</p>
        <button class="close-button1" onclick="closeExplicitWordsModal()" style="
            background:rgb(209, 100, 204);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        ">Close</button>
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
    function openExplicitWordsModal(message) {
    const modal = document.getElementById('explicitWordsModal');
    const messageElement = document.getElementById('explicitWordsMessage');
    messageElement.textContent = message;
    modal.style.display = 'flex';
}

function closeExplicitWordsModal() {
    document.getElementById('explicitWordsModal').style.display = 'none';
}

document.querySelector('.post-box form').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent form submission

    const content = document.querySelector('textarea[name="content"]').value.trim();
    const explicitWords = ['Potanginamo', 'Tanga', 'Bobo', 'Fuck', 'Putanginamo', 'tite', 'tanginamo', 'nigga', 'niga', 'bayag', 'puke', 'dick']; // Add your explicit words here

    // Check for explicit words
    const containsExplicit = explicitWords.some((word) => content.toLowerCase().includes(word.toLowerCase()));

    if (containsExplicit) {
        openExplicitWordsModal('Your post contains inappropriate language and cannot be submitted.');
    } else {
        // Submit the form via AJAX
        const formData = new FormData(this);

        fetch('post.php', {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    location.reload(); // Reload the page to show the new post
                } else {
                    openExplicitWordsModal(data.error || 'Failed to create the post.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                openExplicitWordsModal('An error occurred. Please try again.');
            });
    }
});
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
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
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

  <script>
   document.getElementById('imageUpload').addEventListener('change', function () {
    const files = this.files;
    const imageCount = document.getElementById('imageCount');
    if (files.length > 0) {
        imageCount.textContent = `${files.length} file(s) attached`; // Update with the number of files
    } else {
        imageCount.textContent = 'No images attached'; // Reset if no files are selected
    }
});
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

function toggleHeart(el) {
    const postId = el.closest('.post').dataset.postId;
    const heartCount = el.nextElementSibling;

    fetch('save_heart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}`,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.hearted) {
                el.innerHTML = '&#x2665;'; // Filled heart
                el.style.color = '#bc6ec7'; // Set heart color to purple
                el.style.fontSize = '1.5em'; // Make the heart a little bigger
                heartCount.textContent = parseInt(heartCount.textContent) + 1;
            } else {
                el.innerHTML = '&#x2661;'; // Empty heart
                el.style.color = '#000'; // Reset heart color
                el.style.fontSize = '1em'; // Reset heart size
                heartCount.textContent = parseInt(heartCount.textContent) - 1;
            }
        })
        .catch((error) => console.error('Error:', error));
}
</script>
  
<script>
  function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch('delete_post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}`,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
                    if (postElement) {
                        postElement.remove();
                    }
                } else {
                    alert('Failed to delete the post.');
                }
            })
            .catch((error) => console.error('Error:', error));
    }
}
</script>
</body>
</html>