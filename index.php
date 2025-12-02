<?php
session_start();
include_once 'connection.php';

$profile_image = 'Elements/profile.png';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Fetch user profile image
    $userQuery = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $userQuery->execute([$user_id]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    if (!empty($user['profile_image'])) {
        $profile_image = 'profile_images/' . $user['profile_image'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KAWAII KINGDOM</title>

  <link rel="stylesheet" href="CSS/home.css" />
  <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
    rel="stylesheet">
</head>

<body>

  <audio id="bg-music" autoplay>
    <source src="./music/Greeny.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>
  
  <div id="left-animation" class="side-animation">
    <ul class="menu-list">
      <li><a href="#">HOME</a></li>
      <li><a href="about">ABOUT US</a></li>
      <li><a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'login'; ?>"><?php echo isset($_SESSION['user_id']) ? 'LOGOUT' : 'LOGIN'; ?></a></li>
      <li><a href="#">CONTACT ME</a></li>
      <li><a href="shop">SHOP NOW</a></li>
    </ul>
  </div>
  <div id="right-animation" class="side-animation"></div>
  
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

          <li class="nav-item" id="nav-title"><a href="#">KAWAII KINGDOM</a></li>
          
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


    <section class="intro-home">
      <h1>Let's peel, stick, and smile!</h1>
      <p>Explore our treasure trove of charming stickers designed to brighten your day and personalize your space.</p>
      <a class="fancy" href="shop">
        <span class="top-key"></span>
        <span class="text">Shop Available Stickers</span>
        <span class="bottom-key-1"></span>
      </a>

      <div class="home-stickers">
      </div>
    </section>


    <div class="third">
      <h1>BEST SELLING STICKERS</h1>
      
      <div class="slider">
        <div class="slide-track">

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/March 7th_1.png" alt=""></div>
            </div>
            <div class="card-title">March 7th</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Charlotte 1.png" alt=""></div>
            </div>
            <div class="card-title">Charlotte</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./PNGs/holoBisaya.png" alt=""></div>
            </div>
            <div class="card-title">Bisaya</div>
            <div class="card-subtitle">Holographic. Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Lynette.png" alt=""></div>
            </div>
            <div class="card-title">Lynette</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Lyney.png" alt=""></div>
            </div>
            <div class="card-title">Lyney</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./PNGs/Apple.png" alt=""></div>
            </div>
            <div class="card-title">Apple</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Sucrose.png" alt=""></div>
            </div>
            <div class="card-title">Sucrose</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Yoimiya 4.png" alt=""></div>
            </div>
            <div class="card-title">Yoimiya</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <!-- copy and paste another card to apply infinite scroll effect -->
          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/March 7th_1.png" alt=""></div>
            </div>
            <div class="card-title">March 7th</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Charlotte 1.png" alt=""></div>
            </div>
            <div class="card-title">Charlotte</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./PNGs/holoBisaya.png" alt=""></div>
            </div>
            <div class="card-title">Bisaya</div>
            <div class="card-subtitle">Holographic. Lorem ipsum dolor sit amet, consectetur adipisicing elit.</div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Lynette.png" alt=""></div>
            </div>
            <div class="card-title">Lynette</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Lyney.png" alt=""></div>
            </div>
            <div class="card-title">Lyney</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./PNGs/Apple.png" alt=""></div>
            </div>
            <div class="card-title">Apple</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Sucrose.png" alt=""></div>
            </div>
            <div class="card-title">Sucrose</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-img">
              <div class="img"><img src="./Sticker/Yoimiya 4.png" alt=""></div>
            </div>
            <div class="card-title">Yoimiya</div>
            <div class="card-subtitle">Product description. Lorem ipsum dolor sit amet, consectetur adipisicing elit.
            </div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-price"><span>₱</span> ???</div>
              <button class="card-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z">
                  </path>
                  <path
                    d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z">
                  </path>
                  <path
                    d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z">
                  </path>
                </svg>
              </button>
            </div>
          </div>
        </div>
        
      </div>
      <div class="third-button">
        <a class="fancy" href="shop">
          <span class="top-key"></span>
          <span class="text">peel here to find out their prices :></span>
          <span class="bottom-key-1"></span>
        </a>
      </div>

    </div>

    <div class="why-choose-us">
      <h1>Stick with the Best!</h1>
      <span class="decorative-span"></span>
      <p>Choose us to sprinkle a dash of fun onto your favorite spaces and possessions. With Kawaii Kingdom, it's not just a sticker; it's a little piece of happiness. 🌈✨ Stick with us for a smile in every peel!</p>
    </div>

    <div class="type-sticker">
      <h1>STICKY SHEETS SHOWCASE</h1>

      <div class="type-first-row">
        <div class="card1">
          <img class="card1-img" src="./Elements/vinyl.png" alt="">
          <div class="card__content">
            <p class="card__title">Vinyl</p>
            <p class="card__description">Durable, smooth, and ready to groove, our Vinyl stickers are like the cool DJs of surfaces, spinning through rain or shine without missing a beat!</p>
          </div>
        </div>  

        <div class="card1">
          <img class="card1-img" src="./Elements/Holographic Paper.jpg" alt="">
          <div class="card__content">
            <p class="card__title">Holographic</p>
            <p class="card__description">Add a spectrum of sparkle to your stuff with Holographic stickers that turn the light into a disco ball dance on whatever they stick to!</p>
          </div>
        </div>  

        <div class="card1">
          <img class="card1-img" src="./Elements/Glitter.png" alt="">
          <div class="card__content">
            <p class="card__title">Glitter</p>
            <p class="card__description">For those who live by 'shine bright like a diamond,' our Glitter stickers are the ultimate sidekicks. They bring the bling and make your things sing!</p>
          </div>
        </div>   
        
        <div class="card1">
          <img class="card1-img" src="./Elements/paper.jpg" alt="">
          <div class="card__content">
            <p class="card__title">Paper</p>
            <p class="card__description">Classic, crisp, and oh-so chic! Paper stickers are the timeless charmers that bring a touch of simplicity and elegance to your personal canvas.</p>
          </div>
        </div>
      </div>

      <div class="type-second-row">
        <div class="card1">
          <img class="card1-img" src="./Elements/die-cut.png" alt="">
          <div class="card__content">
            <p class="card__title">Die-cut</p>
            <p class="card__description">Shape-shifters of the sticker world, Die-cut stickers contour to the form of their funky designs. Perfect for those who appreciate a fit that's spot-on!</p>
          </div>
        </div>

        <div class="card1">
          <img class="card1-img" src="./Elements/decal1.jpg" alt="">
          <div class="card__content">
            <p class="card__title">Decal</p>
            <p class="card__description">Make a statement that sticks! Decals are the bold and beautiful cousins in our sticker family, ready to turn any space into a personal piece of art.</p>
          </div>
        </div>

        <div class="card1">
          <img class="card1-img" src="./Elements/static-cling.jpg" alt="">
          <div class="card__content">
            <p class="card__title">Static-cling</p>
            <p class="card__description">No commitment issues here! Our Static-cling stickers hug your windows and mirrors with a special non-adhesive love, ready to move when you are.</p>
          </div>
        </div>


      </div>

    </div>

    <div class="coming-soon">
    <h1>COMING SOON!~</h1>
    <span class="decorative-span-2"></span>
      <div class="video-box">
      <video class="video" controls autoplay loop>
        <source src="./video/video.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      </div>
    </div>

    <div class="news-stories">
      <h1>ADHESIVE ANNOUNCEMENTS</h1>
      <span class="decorative-span-1"></span>
      
      <div class="news-container">
        <div class="news-content-1">
          <div class="img-news-1">
            <img src="./Elements/author2.jpg" alt="">
          </div>
          <div class="article-title-description">
            <h2>CREATIVE CORNER: THE ARTIST</h2>
            <p>Get to know the creative mind behind Kawaii Kingdom! In this exclusive interview, Dave shares his journey, inspiration, and the magic behind the stickers that make you smile.</p>
          </div>
        </div>

        <div class="news-content-1">
          <div class="img-news-1">
            <img src="./Elements/made.jpg" alt="">
          </div>
          <div class="article-title-description">
            <h2>HOW IT'S MADE</h2>
            <p>Peek behind the sticker sheet and discover the magic of our making! From the initial doodle to the final slice, our process combines creativity with precision.</p>
          </div>
        </div>

        <div class="news-content-1">
          <div class="img-news-1">
            <img src="./Elements/custom-stickers.png" alt="">
          </div>
          <div class="article-title-description">
            <h2>COMING SOON: CUSTOM STICKERS!</h2>
            <p>Whether it&rsquo;s your pet&rsquo;s goofy grin or a sketch from your dream diary, we&rsquo;ll transform it into a sticky masterpiece. Stay tuned for the reveal, and get ready to turn your imagination into your new favorite adhesive art piece!</p>
          </div>
        </div>
      </div>
    </div>

    <div class="newsletter">
      <div class="subscribe">
        <h2 class="subscribe__title">Stick Around for More Fun!</h2>
        <p class="subscribe__copy">Join our sticker squad! Subscribe for a splash of color in your inbox with the latest news and stick-tacular updates. No spam, just smiles!</p>
        <div class="form">
          <input type="email" class="form__email" placeholder="Enter your email address" />
          <button class="form__button">Send</button>
        </div>
        <div class="notice">
          <input type="checkbox" id="newsletter-checkbox" class="hidden-cbx" />
          <label for="newsletter-checkbox" class="cbx"></label>
          <span class="notice__copy">Tick this box to let us craft a bundle of joy for you each month with our vibrant newsletter!</span>
        </div>
      </div>
    </div>

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
          <a href="#" id="link-home">HOME</a>
          <a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'login'; ?>" id="link-login"><?php echo isset($_SESSION['user_id']) ? 'LOGOUT' : 'LOGIN'; ?></a>
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
          <a href="https://www.facebook.com/dackydeyb"><img src="./Elements/footer-facebook.png"
              alt="Facebook Link"></a>
          <a href="https://twitter.com/dackydeyb"><img src="./Elements/footer-twitter.png" alt="Twitter Profile"></a>
          <a href="https://github.com/dackydeyb"><img src="./Elements/footer-github.png" alt="Github Profile"></a>
          <a href="https://www.reddit.com/user/DaYousoro/"><img src="./Elements/footer-reddit.png"
              alt="Reddit Link"></a>
          <a href="#"><img src="./Elements/footer-gmail.png" alt="Email"></a>
        </li>
      </ul>

      <p id="credits">© Dave Jhared G. Paduada BSCpE II - IF</p>

    </footer>
  </main>
  <script src="JS/home.js"></script>
</body>

</html>