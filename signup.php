<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if user already exists
    $checkSql = "SELECT * FROM users WHERE email = :email";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        echo "<script>alert('This user already exists.'); window.location.href = 'signup.php';</script>";
    } else {
        // Insert user
        $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo "<script>alert('User registered successfully.'); window.location.href = 'login.php';</script>";
        } else {
            echo "Error registering user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">

  <link rel="stylesheet" href="CSS/login.css" />
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
      <li><a href="login.php">LOGIN</a></li>
      <li><a href="#">CONTACT ME</a></li>
      <li><a href="shop.php">SHOP NOW</a></li>
    </ul>
  </div>
  <div id="right-animation" class="side-animation">
    <!-- <div class="menu-title">KAWAII KINGDOM</div> -->
  </div>

  <main>
    <header>
      <nav class="navbar">
        <ul class="nav-list">
          <li class="nav-item">
            <label class="hamburger">
              <input type="checkbox">
              <svg viewBox="0 0 32 32">
                <path class="line line-top-bottom" d="M27 10 13 10C10.8 10 9 8.2 9 6 9 3.5 10.8 2 13 2 15.2 2 17 3.8 17 6L17 26C17 28.2 18.8 30 21 30 23.2 30 25 28.2 25 26 25 23.8 23.2 22 21 22L7 22"></path>
                <path class="line" d="M7 16 27 16"></path>
              </svg>
              <span class="menu-text">MENU</span>
            </label>
          </li>
          <li class="nav-item" id="nav-title"><a href="index.php">KAWAII KINGDOM</a></li>
          <li class="nav-item">
            <div class="nav-search-profile">
              <form action="submit">
                <div class="search-container">
                  <img src="Elements/search.png" alt="Search-Icon" id="search-icon">
                  <input type="text" placeholder="Search..." id="search-bar">
                </div>
              </form>
              <a href="login.php">
                <img src="Elements/profile.png" alt="Profile" id="profile-icon">
              </a>
            </div>
          </li>
        </ul>
      </nav>
    </header>

    <section class="login-container">
      <form class="form" action="signup.php" method="post">
        <h1>- Register -</h1>
        <div class="flex-column">
          <label>Email </label>
        </div>
        <div class="inputForm">
          <svg height="20" viewBox="0 0 32 32" width="20" xmlns="http://www.w3.org/2000/svg">
            <g id="Layer_3" data-name="Layer 3">
              <path d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z"></path>
            </g>
          </svg>
          <input type="email" name="email" class="input" placeholder="Enter your Email" required>
        </div>

        <div class="flex-column">
          <label>Password </label>
        </div>
        <div class="inputForm">
          <svg height="20" viewBox="-64 0 512 512" width="20" xmlns="http://www.w3.org/2000/svg">
            <path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path>
            <path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0"></path>
          </svg>
          <input type="password" name="password" class="input" placeholder="Enter your Password" required>
          <svg viewBox="0 0 576 512" height="1em" xmlns="http://www.w3.org/2000/svg">
            <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
          </svg>
        </div>

        <button class="button-submit" type="submit">Sign Up</button>
        </div>
      </form>
    </section>

  </main>
  <footer>
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
        <a href="login.php" id="link-login">LOGIN</a>
      </li>

      <li class="footer-column">
        <a href="shop.php" id="link-shop-all-stickers">SHOP ALL STICKERS</a>
        <a href="newarrivals.php" id="link-new-arrivals">NEW ARRIVALS</a>
        <a href="bestseller.php" id="link-best-sellers">BEST SELLERS</a>
      </li>

      <li class="footer-column">
        <a href="#" id="link-column-stickers">CUSTOM STICKERS</a>
        <a href="about" id="link-about-us">ABOUT US</a>
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
  <script src="JS/login.js"></script>
</body>

</html>