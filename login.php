<?php
session_start();
include 'connection.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Check if the user is admin
  $admin_email = 'admin@admin.com';
  $admin_hashed_password = '$2y$10$g/RSXTUid5rtSqknqC2Lgu/ISU0f8Z9VSxB6nq3n6rNQZPst1VsqW';

  if ($email == $admin_email && password_verify($password, $admin_hashed_password)) {
    $_SESSION['user'] = $email;
    $_SESSION['user_id'] = 2; // admin user_id is 2
    header('Location: item_add.php');
    exit;
  } else {
    // Check if the user is a normal user
    $sql = "SELECT id, password FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user'] = $email;
      $_SESSION['user_id'] = $user['id']; // Set user ID in session
      header('Location: shop'); // Redirect to the user dashboard or main page
      exit;
    } else {
      // Handle invalid login
      $error_message = 'Invalid email or password.';
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

  <link rel="stylesheet" href="CSS/login.css" />
  <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

  <script>
    function showAlert(message) {
      if (message) {
        alert(message);
      }
    }
   
  </script>

</head>

<body onload="showAlert('<?php echo $error_message; ?>')">

<audio id="bg-music" loop autoplay>
  <source src="./music/Koi_is_love.mp3" type="audio/mpeg">
  Your browser does not support the audio element.
</audio>

  <div id="left-animation" class="side-animation">
    <ul class="menu-list">
      <li><a href="index.php">HOME</a></li>
      <li><a href="about">ABOUT US</a></li>
      <li><a href="login">LOGIN</a></li>
      <li><a href="#">CONTACT ME</a></li>
      <li><a href="shop">SHOP NOW</a></li>
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
              <form action="shop.php" method="GET" id="search-form">
                <div class="search-container">
                  <img src="Elements/search.png" alt="Search-Icon" id="search-icon">
                  <input type="text" placeholder="Search..." id="search-bar" name="search">
                </div>
              </form>
              <a href="#">
                <img src="Elements/profile.png" alt="Profile" id="profile-icon">
              </a>
            </div>
          </li>
        </ul>
      </nav>
    </header>

    <section class="login-container">
      <form class="form" action="login" method="post">
        <h1>- Welcome Back -</h1>
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

        <div class="flex-row">
          <div>
            <input type="checkbox">
            <label>Remember me </label>
          </div>
          <span class="span">Forgot password?</span>
        </div>
        <button class="button-submit" type="submit">Sign In</button>
        <p class="p">Don't have an account? <a href="signup" class="span">Sign Up</a></p>

        </p>
        <p class="p line">Or With</p>

        <div class="flex-row">
          <button class="btn google">
            <svg version="1.1" width="20" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
              <path style="fill:#FBBB00;" d="M113.47,309.408L95.648,375.94l-65.139,1.378C11.042,341.211,0,299.9,0,256
      c0-42.451,10.324-82.483,28.624-117.732h0.014l57.992,10.632l25.404,57.644c-5.317,15.501-8.215,32.141-8.215,49.456
      C103.821,274.792,107.225,292.797,113.47,309.408z"></path>
              <path style="fill:#518EF8;" d="M507.527,208.176C510.467,223.662,512,239.655,512,256c0,18.328-1.927,36.206-5.598,53.451
      c-12.462,58.683-45.025,109.925-90.134,146.187l-0.014-0.014l-73.044-3.727l-10.338-64.535
      c29.932-17.554,53.324-45.025,65.646-77.911h-136.89V208.176h138.887L507.527,208.176L507.527,208.176z"></path>
              <path style="fill:#28B446;" d="M416.253,455.624l0.014,0.014C372.396,490.901,316.666,512,256,512
      c-97.491,0-182.252-54.491-225.491-134.681l82.961-67.91c21.619,57.698,77.278,98.771,142.53,98.771
      c28.047,0,54.323-7.582,76.87-20.818L416.253,455.624z"></path>
              <path style="fill:#F14336;" d="M419.404,58.936l-82.933,67.896c-23.335-14.586-50.919-23.012-80.471-23.012
      c-66.729,0-123.429,42.957-143.965,102.724l-83.397-68.276h-0.014C71.23,56.123,157.06,0,256,0
      C318.115,0,375.068,22.126,419.404,58.936z"></path>

            </svg>

            Google

          </button><button class="btn apple">
            <svg version="1.1" height="20" width="20" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 22.773 22.773" style="enable-background:new 0 0 22.773 22.773;" xml:space="preserve">
              <g>
                <g>
                  <path d="M15.769,0c0.053,0,0.106,0,0.162,0c0.13,1.606-0.483,2.806-1.228,3.675c-0.731,0.863-1.732,1.7-3.351,1.573 c-0.108-1.583,0.506-2.694,1.25-3.561C13.292,0.879,14.557,0.16,15.769,0z"></path>
                  <path d="M20.67,16.716c0,0.016,0,0.03,0,0.045c-0.455,1.378-1.104,2.559-1.896,3.655c-0.723,0.995-1.609,2.334-3.191,2.334 c-1.367,0-2.275-0.879-3.676-0.903c-1.482-0.024-2.297,0.735-3.652,0.926c-0.155,0-0.31,0-0.462,0 c-0.995-0.144-1.798-0.932-2.383-1.642c-1.725-2.098-3.058-4.808-3.306-8.276c0-0.34,0-0.679,0-1.019 c0.105-2.482,1.311-4.5,2.914-5.478c0.846-0.52,2.009-0.963,3.304-0.765c0.555,0.086,1.122,0.276,1.619,0.464 c0.471,0.181,1.06,0.502,1.618,0.485c0.378-0.011,0.754-0.208,1.135-0.347c1.116-0.403,2.21-0.865,3.652-0.648 c1.733,0.262,2.963,1.032,3.723,2.22c-1.466,0.933-2.625,2.339-2.427,4.74C17.818,14.688,19.086,15.964,20.67,16.716z"></path>
                </g>
              </g>
            </svg>

            Apple

          </button>
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
        <a href="login" id="link-login">LOGIN</a>
      </li>

      <li class="footer-column">
        <a href="shop" id="link-shop-all-stickers">SHOP ALL STICKERS</a>
        <a href="newarrivals" id="link-new-arrivals">NEW ARRIVALS</a>
        <a href="bestseller" id="link-best-sellers">BEST SELLERS</a>
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