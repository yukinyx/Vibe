<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vibe Landing Page</title>
  <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="main.css" />
</head>
<body>
    
<video autoplay muted loop id="myVideo">
  <source src="cloudvid.mp4" type="video/mp4">
</video>

  <div class="landing-container">
    <div class="nav-bar">
      <div class="logo">
        <img src="img/gogo.png" alt="logo" onclick="location.href='main.php'"/>
        <div class="logo-text">Vibe </div>
      </div>
      <div class="nav-links">
        <div class="nav-link active"onclick="location.href='main.php'">HOME</div>
        <div class="nav-link">ABOUT US</div>
        <div class="nav-link">LOGIN</div>
        <div class="nav-link" onclick="window.parent.showSignup()">SIGN UP</div>
      </div>
    </div>

    <div class="content">
      <div class="text-section">
        <h1 class="main-heading">Connect, Share, and Express Your Vibe.</h1>
        <p class="sub-heading">
          A Web Application for Connecting, Sharing, and Expressing Yourself Through Real-Time Social Updates
        </p>
        <button class="cta-button"onclick="window.parent.showSignup()">Get Started</button>
      </div>
      <div class="image-section">
        <img src="img/gogo.png" alt="Hero" class="hero-image" onclick="location.href='main.php'"/>
      </div>
    </div>
  </div>

<div id="loginModal" class="modal" style="
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
">
  <div id="popupWrapper" style="position: relative;">
    <iframe src="login.php" width="1000" height="750" class="modal-content" style="
      border: none;
      border-radius: 30px;
      background: transparent;
    "></iframe>
  </div>
</div>

<div id="signupModal" class="modal" style="
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
">
  <div id="signupWrapper" style="position: relative;">
    <iframe src="signup.php" width="1050" height="800" class="modal-content" style="
      border: none;
      border-radius: 30px;
      background: transparent;
    "></iframe>
  </div>
</div>

<div id="aboutUsModal" class="modal" style="
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
">
  <div class="about-us-content" style="
    background: #ffc0cb;
    padding: 20px;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    text-align: center;
    font-family: 'Inter', sans-serif;
    color: black;
  ">
    <button class="close-button" onclick="closeAboutUs()" style="
      background: none;
      border: none;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      margin-bottom: 10px;
      color: black;
    ">&larr; Back</button>
    <h2>About Us</h2>
    <p>Vibe is a community where you can share, connect, and be yourself — anytime, anywhere. Whether it's something you wanted to say, something funny, or something that matters, Vibe allows you to make real-time posts and check out what others are sharing as well.</p>
    <p>We created Vibe so that people can stay connected, share what matters, and be themselves. It's about real people, and real vibes.</p>
    <p>So go ahead — connect, share, and express your vibe.</p>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
  const aboutUsLink = document.querySelector('.nav-link:nth-child(2)'); // "About Us" link
  const aboutUsModal = document.getElementById('aboutUsModal');

  aboutUsLink.addEventListener('click', () => {
    aboutUsModal.style.display = 'flex';
  });

  aboutUsModal.addEventListener('click', (e) => {
    if (!e.target.closest('.about-us-content')) {
      aboutUsModal.style.display = 'none';
    }
  });
});

function closeAboutUs() {
  document.getElementById('aboutUsModal').style.display = 'none';
}
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const signupLink = document.querySelector('.nav-link:nth-child(4)');
    const signupModal = document.getElementById('signupModal');
    const signupWrapper = document.getElementById('signupWrapper');

    signupLink.addEventListener('click', () => {
      signupModal.style.display = 'flex';
    });

    signupModal.addEventListener('click', (e) => {
      if (!signupWrapper.contains(e.target)) {
        signupModal.style.display = 'none';
      }
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const loginLink = document.querySelector('.nav-link:nth-child(3)');
    const modal = document.getElementById('loginModal');
    const popupWrapper = document.getElementById('popupWrapper');

    loginLink.addEventListener('click', () => {
      modal.style.display = 'flex';
    });

    modal.addEventListener('click', (e) => {
      if (!popupWrapper.contains(e.target)) {
        modal.style.display = 'none';
      }
    });
  });
</script>

<script>
  function showSignup() {
    document.getElementById('loginModal').style.display = 'none';
    document.getElementById('signupModal').style.display = 'flex';
  }

  function showLogin() {
    document.getElementById('signupModal').style.display = 'none';
    document.getElementById('loginModal').style.display = 'flex';
  }
</script>

</body>
</html>
