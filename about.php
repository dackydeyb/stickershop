<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? null;
$profile_image = 'Elements/profile.png'; // Default

if ($user_id) {
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user && $user['profile_image']) {
        $profile_image = 'profile_images/' . $user['profile_image'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us</title>

  <link rel="stylesheet" href="CSS/about.css" />
  <link rel="icon" type="image/png" href="Sticker/March 7th_4.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>
<body>

  <div id="left-animation" class="side-animation">
    <ul class="menu-list">
      <li><a href="index.php">HOME</a></li>
      <li><a href="about">ABOUT US</a></li>
      <li><a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'login'; ?>"><?php echo isset($_SESSION['user_id']) ? 'LOGOUT' : 'LOGIN'; ?></a></li>
      <li><a href="#">CONTACT ME</a></li>
      <li><a href="shop">SHOP NOW</a></li>
    </ul>
  </div>
  <div id="right-animation" class="side-animation">
  </div>
  


  <main>
    <header>
      <nav class="navbar">
        <ul class="nav-list">
          <li class="nav-item">
            <label class="hamburger">
              <input type="checkbox">
              <svg viewBox="0 0 32 32">
                <path class="line line-top-bottom"
                  d="M27 10 13 10C10.8 10 9 8.2 9 6 9 3.5 10.8 2 13 2 15.2 2 17 3.8 17 6L17 26C17 28.2 18.8 30 21 30 23.2 30 25 28.2 25 26 25 23.8 23.2 22 21 22L7 22">
                </path>
                <path class="line" d="M7 16 27 16"></path>
              </svg>
              <span class="menu-text">MENU</span>
            </label>
          </li>

          <li class="nav-item" id="nav-title"><a href="index.php">KAWAII KINGDOM</a></li>
          
          <li class="nav-item">
            <div class="nav-search-profile">
              <form action="shop.php" method="GET" id="search-form">
                <div class="search-container">
                  <img src="Elements/search.png" alt="Search-Icon" id="search-icon">
                  <input type="text" placeholder="Find a sticker :>" id="search-bar" name="search">
                </div>
              </form>
              <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login'; ?>">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" id="profile-icon" style="object-fit: cover; border-radius: 50%;">
              </a>
            </div>
          </li>
        </ul>
      </nav>
    </header>

  <section class="about-section-container">
    <div class="about-row">
      <div class="about-text">
        <h1>About Us</h1>
        <p>Welcome to Kawaii Kingdom, your premier corporate stickershop! We are dedicated to providing high-quality, fun, and creative stickers that bring joy to your everyday life. Our team is passionate about art and design, ensuring every sticker is a masterpiece.</p>
      </div>
      <div class="about-image">
        <img src="Elements/About_us.png" alt="About Us">
      </div>
    </div>

    <div class="about-row reverse">
      <div class="about-text">
        <h1>Our Mission</h1>
        <p>At Kawaii Kingdom, our mission is to spread happiness through sticky art. We believe that a simple sticker can transform a mundane object into something personal and exciting. We strive to create designs that resonate with everyone, from anime fans to minimalists.</p>
      </div>
      <div class="about-image">
        <img src="Elements/about_us2.jpg" alt="Our Mission">
      </div>
    </div>

    <div class="about-row">
      <div class="about-text">
        <h1>Our Story</h1>
        <p>Started as a small passion project, Kawaii Kingdom has grown into a beloved corporate stickershop. We take pride in our journey and the community we've built. Join us as we continue to stick together and make the world a more colorful place!</p>
      </div>
      <div class="about-image">
        <img src="Elements/about-me.jpg" alt="Our Story">
      </div>
    </div>
  </section>


  <footer >
    <div class="footer-title">
      <h2 id="footer-title-h2">
        <a href="#" id="footer-title-link">KAWAII KINGDOM</a>
      </h2>
      <p id="footer-title-p">A Sticker Shop</p>
    </div>
  
    <ul class="footer-links">
      <li>
        <img id="furina" src="./Sticker/FurinaDance.gif" alt="furina-dance">
      </li>
      
      <li class="footer-column">
        <a href="#" id="link-back-to-top">BACK TO TOP</a>
        <a href="index.php" id="link-home">HOME</a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'login'; ?>" id="link-login"><?php echo isset($_SESSION['user_id']) ? 'LOGOUT' : 'LOGIN'; ?></a>
      </li>

      <li class="footer-column">
        <a href="shop" id="link-shop-all-stickers">SHOP ALL STICKERS</a>
        <a href="newarrivals" id="link-new-arrivals">NEW ARRIVALS</a>
        <a href="bestseller" id="link-best-sellers">BEST SELLERS</a>
      </li>

      <li class="footer-column">
        <a href="#" id="link-column-stickers">CUSTOM STICKERS</a>
        <a href="#" id="link-about-us">ABOUT US</a>
        <a href="#" id="link-how-made">HOW IT'S MADE</a>
      </li>

      <li class="footer-column">
        <a href="#" id="link-sticker-care">STICKER CARE</a>
        <a href="#" id="link-order-tracking">ORDER TRACKING</a>
        <a href="#" id="link-returns">RETURNS & EXCHANGES</a>
      </li>

      <li class="footer-column">
        <a href="#" id="link-terms">TERMS OF SERVICE</a>
        <a href="#" id="link-privacy">PRIVACY POLICY</a>
        <a href="#" id="link-contact">CONTACT US</a>
      </li>

      <li class="footer-column">
        <a href="https://www.facebook.com/dackydeyb"><img src="./Elements/footer-facebook.png" alt="Facebook Link"></a>
        <a href="https://twitter.com/dackydeyb"><img src="./Elements/footer-twitter.png" alt="Twitter Profile"></a>
        <a href="https://github.com/dackydeyb"><img src="./Elements/footer-github.png" alt="Github Profile"></a>
        <a href="https://www.reddit.com/user/DaYousoro/"><img src="./Elements/footer-reddit.png" alt="Reddit Link"></a>
        <a href="#"><img src="./Elements/footer-gmail.png" alt="Email"></a>
      </li>
    </ul>

    <p id="credits">© Dave Jhared G. Paduada BSCpE II - IF</p>

  </footer>
  </main>

<script src="JS/about.js"></script>
<script>
// Ensure search form works when Enter is pressed
document.addEventListener('DOMContentLoaded', function() {
  const searchForm = document.getElementById('search-form');
  const searchInput = document.getElementById('search-bar');
  
  if (searchForm && searchInput) {
    // Handle form submission
    searchForm.addEventListener('submit', function(event) {
      const searchValue = searchInput.value.trim();
      if (searchValue) {
        // Allow the form to submit naturally to shop.php?search=...
        // No need to preventDefault since we want the redirect
      } else {
        // If search is empty, prevent submission
        event.preventDefault();
      }
    });
    
    // Also handle Enter key press on the input
    searchInput.addEventListener('keypress', function(event) {
      if (event.key === 'Enter') {
        const searchValue = searchInput.value.trim();
        if (searchValue) {
          // Form will submit naturally
        } else {
          event.preventDefault();
        }
      }
    });
  }
});
</script>
</body>
</html>

