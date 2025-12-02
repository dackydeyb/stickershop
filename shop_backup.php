<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
  echo "User ID: " . $_SESSION['user_id'];
} else {
  echo "User not logged in.";
}

include_once 'connection.php';
// --- START PAGINATION LOGIC ---
// 1. Define items per page
$items_per_page = 20;

// 2. Get the current page from the URL, default to 1 if not set
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
  $current_page = 1;
}

// 3. Calculate the OFFSET for the SQL query
$offset = ($current_page - 1) * $items_per_page;

// --- SEARCH LOGIC ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 4. Get the TOTAL number of items to calculate total pages
$count_sql = "SELECT COUNT(*) FROM items";
$count_params = [];

if (!empty($search)) {
  $count_sql .= " WHERE (name LIKE :search_name OR description LIKE :search_desc)";
  $count_params[':search_name'] = "%$search%";
  $count_params[':search_desc'] = "%$search%";
}

try {
  $total_items_query = $conn->prepare($count_sql);
  foreach ($count_params as $key => $value) {
    $total_items_query->bindValue($key, $value);
  }
  $total_items_query->execute();
  $total_items = $total_items_query->fetchColumn();
} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
  $total_items = 0;
}

// 5. Calculate total pages
$total_pages = ceil($total_items / $items_per_page);

// --- END PAGINATION LOGIC ---


// Initialize $items as an empty array
$items = [];

try {
  // Fetch ONLY items for the current page, with optional search filter
  $sql = "SELECT * FROM items";
  $params = [];
  
  if (!empty($search)) {
    $sql .= " WHERE (name LIKE :search_name OR description LIKE :search_desc)";
    $params[':search_name'] = "%$search%";
    $params[':search_desc'] = "%$search%";
  }
  
  $sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";
  $stmt = $conn->prepare($sql);
  
  // Bind search parameters if they exist
  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  // We must bind parameters as integers
  $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  echo "Error: ". $e->getMessage();
}

// Initialize cart count
$cartCount = 0;

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $cartQuery = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
  $cartQuery->execute([$user_id]);
  $cartCount = $cartQuery->fetchColumn();

  // Fetch user profile image
  $userQuery = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
  $userQuery->execute([$user_id]);
  $user = $userQuery->fetch(PDO::FETCH_ASSOC);
  $profile_image = !empty($user['profile_image']) ? 'profile_images/' . $user['profile_image'] : 'Elements/profile.png';
} else {
  $profile_image = 'Elements/profile.png';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Now</title>

  <link rel="stylesheet" href="CSS/shop.css" />
  <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>

<body>

<audio id="bg-music" loop autoplay>
  <source src="./music/Koi_is_love.mp3" type="audio/mpeg">
  Your browser does not support the audio element.
</audio>

  <div id="left-animation" class="side-animation">
    <ul class="menu-list">
      <li><a href="index.php">HOME</a></li>
      <li><a href="about">ABOUT US</a></li>
      <li><a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'login'; ?>"><?php echo isset($_SESSION['user_id']) ? 'LOGOUT' : 'LOGIN'; ?></a></li>
      <li><a href="#">CONTACT ME</a></li>
      <li><a href="shop">SHOP NOW</a></li>
      <?php if (isset($_SESSION['user_id'])) : ?>
        <li><a href="cart">YOUR CART [<?php echo $cartCount; ?>]</a></li>
      <?php endif; ?>
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
                  <input type="text" placeholder="Search..." id="search-bar" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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

    <div class="header-title">
      <h1>- Shop All Stickers -</h1>
    </div>


    <div class="shop-container">

      <div class="left-panel">

        <!-- Sort by Price -->
        <div class="filter-sort">
          <div class="sort-title">Sort by Price</div>
          <div class="sort-options">
            <div class="cntr-rd">
              <input type="radio" id="price-default" name="sort-price" class="hidden-xs-up" value="default" checked>
              <label for="price-default" class="cbx-rd"></label>
              <label for="price-default" class="lbl">Default</label>
            </div>
            <div class="cntr-rd">
              <input type="radio" id="price-low-high" name="sort-price" class="hidden-xs-up" value="price-low-high">
              <label for="price-low-high" class="cbx-rd"></label>
              <label for="price-low-high" class="lbl">Low to High</label>
            </div>
            <div class="cntr-rd">
              <input type="radio" id="price-high-low" name="sort-price" class="hidden-xs-up" value="price-high-low">
              <label for="price-high-low" class="cbx-rd"></label>
              <label for="price-high-low" class="lbl">High to Low</label>
            </div>
          </div>
        </div>

        <div class="filter-game">
          <div class="game-title">Game Category</div>
          <div class="game-options">
            <div class="cntr-rd">
              <input type="radio" id="game-all" name="game-category" class="hidden-xs-up" value="all" checked>
              <label for="game-all" class="cbx-rd"></label>
              <label for="game-all" class="lbl">All</label>
            </div>
            
            <div class="cntr-rd">
              <input type="radio" id="game-genshin" name="game-category" class="hidden-xs-up" value="Genshin Impact">
              <label for="game-genshin" class="cbx-rd"></label>
              <label for="game-genshin" class="lbl">Genshin Impact</label>
            </div>

            <div class="cntr-rd">
              <input type="radio" id="game-honkai" name="game-category" class="hidden-xs-up" value="Honkai: Star Rail">
              <label for="game-honkai" class="cbx-rd"></label>
              <label for="game-honkai" class="lbl">Honkai: Star Rail</label>
            </div>
            
            <div class="cntr-rd">
              <input type="radio" id="game-zzz" name="game-category" class="hidden-xs-up" value="Zenless Zone Zero">
              <label for="game-zzz" class="cbx-rd"></label>
              <label for="game-zzz" class="lbl">Zenless Zone Zero</label>
            </div>
            
            <div class="cntr-rd">
              <input type="radio" id="game-wuthering" name="game-category" class="hidden-xs-up" value="Wuthering Waves">
              <label for="game-wuthering" class="cbx-rd"></label>
              <label for="game-wuthering" class="lbl">Wuthering Waves</label>
            </div>

            <div class="cntr-rd">
              <input type="radio" id="game-pgr" name="game-category" class="hidden-xs-up" value="Punishing Gray Raven">
              <label for="game-pgr" class="cbx-rd"></label>
              <label for="game-pgr" class="lbl">Punishing Gray Raven</label>
            </div>
          </div>
        </div>

        <!-- Filter by Review Stars -->
        <div class="filter-review">
          <div class="review-title">Review</div>
          <div class="rating">
            <input value="5" name="rate" id="star5" type="radio">
            <label title="text" for="star5"></label>
            <input value="4" name="rate" id="star4" type="radio">
            <label title="text" for="star4"></label>
            <input value="3" name="rate" id="star3" type="radio" checked="">
            <label title="text" for="star3"></label>
            <input value="2" name="rate" id="star2" type="radio">
            <label title="text" for="star2"></label>
            <input value="1" name="rate" id="star1" type="radio">
            <label title="text" for="star1"></label>
          </div>
        </div>
      </div>

      <div class="right-panel">
        <div class="main-right-content">
          <?php foreach ($items as $item) : 
            $stock = (isset($item['stock']) && $item['stock'] !== null) ? (int)$item['stock'] : 999;
            $is_out_of_stock = $stock <= 0;
          ?>
            <div class="card <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>" 
                 data-item-id="<?php echo htmlspecialchars($item['id']); ?>"
                 data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
                 data-item-price="<?php echo htmlspecialchars($item['price']); ?>"
                 data-item-image="<?php echo htmlspecialchars($item['image']); ?>"
                 data-item-description="<?php echo htmlspecialchars($item['description']); ?>"
                 data-item-stock="<?php echo $stock; ?>">
              <div class="card-img">
                <div class="img">
                  <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                </div>
              </div>
              <div class="card-title"><?php echo htmlspecialchars($item['name']); ?></div>
              <div class="card-subtitle"><?php echo htmlspecialchars($item['description']); ?></div>
              <hr class="card-divider">
              <div class="card-footer">
                <div class="card-price"><span>₱</span><?php echo htmlspecialchars($item['price']); ?></div>
                <form method="POST" action="add_to_cart" class="add-to-cart-form" <?php echo $is_out_of_stock ? 'onsubmit="return false;"' : ''; ?>>
                  <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                  <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['name']); ?>">
                  <input type="hidden" name="item_price" value="<?php echo htmlspecialchars($item['price']); ?>">
                  <button type="submit" class="card-btn" <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                      <path d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z"></path>
                      <path d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z"></path>
                      <path d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z"></path>
                      <path d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z"></path>
                    </svg>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>

        </div>

        <div class="pagination-container">
          <nav class="pagination-outer" aria-label="Page navigation">
            <ul class="pagination">

              <li class="page-item <?php if($current_page <= 1){ echo 'disabled'; } ?>">
                <a href="?page=<?php echo $current_page - 1; ?>" class="page-link" aria-label="Previous">
                  <span aria-hidden="true">«</span>
                </a>
              </li>

              <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($i == $current_page){ echo 'active'; } ?>">
                  <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
              <?php endfor; ?>

              <li class="page-item <?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
                <a href="?page=<?php echo $current_page + 1; ?>" class="page-link" aria-label="Next">
                  <span aria-hidden="true">»</span>
                </a>
              </li>

            </ul>
          </nav>
        </div>

        <div class="close-notes-container" id="close-notes">
          <ul>
            <li class="sticker-care">
              <h3>Sticker Care</h3>
              <p>Keep your stickers looking fresh and fabulous by keeping them away from Mr. Sunshine and Ms. Moisture. A gentle pat with a dry cloth will keep their adhesive powers strong for a long, long time!</p>
            </li>
            <li class="terms-of-service">
              <h3>Terms of Service</h3>
              <p>By visiting our website, you're agreeing to play by our rules – but don't worry, they're totally fair and fun! Every purchase is a party, and our terms of use make sure everyone has a blast.</p>
            </li>
            <li class="privacy-policy">
              <h3>Privacy Policy</h3>
              <p>Your privacy is our top priority, and we'll treat your personal info like a precious secret. Our detailed privacy policy outlines how we keep your deets safe and sound.</p>
            </li>
          </ul>
        </div>
      </div>



    </div>

    <!-- <div class="motto">
      
    </div> -->

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
          <a href="<?php echo isset($_SESSION['user_id']) ? 'logout.php' : 'login'; ?>" id="link-login"><?php echo isset($_SESSION['user_id']) ? 'LOGOUT' : 'LOGIN'; ?></a>
        </li>

        <li class="footer-column">
          <a href="#" id="link-shop-all-stickers">SHOP ALL STICKERS</a>
          <a href="newarrivals" id="link-new-arrivals">NEW ARRIVALS</a>
          <a href="bestseller" id="link-best-sellers">BEST SELLERS</a>
        </li>

        <li class="footer-column">
          <a href="#" id="link-column-stickers">CUSTOM STICKERS</a>
          <a href="about" id="link-about-us">ABOUT US</a>
          <a href="#" id="link-how-made">HOW IT'S MADE</a>
        </li>

        <li class="footer-column">
          <a href="#close-notes" id="link-sticker-care">STICKER CARE</a>
          <a href="#" id="link-order-tracking">ORDER TRACKING</a>
          <a href="#" id="link-returns">RETURNS & EXCHANGES</a>
        </li>

        <li class="footer-column">
          <a href="#close-notes" id="link-terms">TERMS OF SERVICE</a>
          <a href="#close-notes" id="link-privacy">PRIVACY POLICY</a>
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

  <!-- Product Detail Modal -->
  <div id="product-modal" class="product-modal">
    <div class="modal-overlay"></div>
    <div class="modal-container">
      <button class="modal-close" aria-label="Close modal">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
      <div class="modal-content">
        <div class="modal-image-section">
          <div class="modal-image-container">
            <img id="modal-image" src="" alt="" class="modal-image">
          </div>
        </div>
        <div class="modal-details-section">
          <h2 id="modal-title" class="modal-title"></h2>
          <p id="modal-description" class="modal-description"></p>
          <div class="modal-price-section">
            <span class="modal-price-label">Price</span>
            <span class="modal-price">₱<span id="modal-price-amount">0.00</span></span>
          </div>
          <div class="modal-stock-section" id="modal-stock-section" style="display: none;">
            <span class="modal-stock-label">Stock Available:</span>
            <span class="modal-stock-value" id="modal-stock-value">0</span>
          </div>
          <div class="modal-paper-type-section">
            <label for="modal-paper-type" class="modal-paper-type-label">Paper Type</label>
            <select id="modal-paper-type" name="paper_type" class="modal-paper-type-select">
              <option value="Vinyl">Vinyl</option>
              <option value="Holographic">Holographic</option>
              <option value="Glitter">Glitter</option>
              <option value="Paper">Paper</option>
              <option value="Die-Cut">Die-Cut</option>
              <option value="Decal">Decal</option>
              <option value="Static-Cling">Static-Cling</option>
            </select>
          </div>
          <div class="modal-quantity-section">
            <label class="modal-quantity-label">Quantity</label>
            <div class="modal-quantity-controls">
              <button class="modal-quantity-btn minus-btn" type="button" aria-label="Decrease quantity">−</button>
              <input type="number" id="modal-quantity" class="modal-quantity-input" value="1" min="1">
              <button class="modal-quantity-btn plus-btn" type="button" aria-label="Increase quantity">+</button>
            </div>
          </div>
          <div class="modal-total-section">
            <span class="modal-total-label">Total</span>
            <span class="modal-total-price">₱<span id="modal-total-amount">0.00</span></span>
          </div>
          <div class="modal-out-of-stock-message" id="modal-out-of-stock-message" style="display: none; padding: 10px; background-color: #ffebee; color: #c62828; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;">
            This item is currently out of stock.
          </div>
          <form method="POST" action="add_to_cart" class="modal-add-to-cart-form">
            <input type="hidden" id="modal-item-id" name="item_id" value="">
            <input type="hidden" id="modal-item-name" name="item_name" value="">
            <input type="hidden" id="modal-item-price" name="item_price" value="">
            <input type="hidden" id="modal-item-quantity" name="quantity" value="1">
            <input type="hidden" id="modal-item-stock" name="item_stock" value="0">
            <button type="submit" class="modal-add-to-cart-btn" id="modal-add-to-cart-btn">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                <path d="m397.78 316h-205.13a15 15 0 0 1 -14.65-11.67l-34.54-150.48a15 15 0 0 1 14.62-18.36h274.27a15 15 0 0 1 14.65 18.36l-34.6 150.48a15 15 0 0 1 -14.62 11.67zm-193.19-30h181.25l27.67-120.48h-236.6z"></path>
                <path d="m222 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z"></path>
                <path d="m368.42 450a57.48 57.48 0 1 1 57.48-57.48 57.54 57.54 0 0 1 -57.48 57.48zm0-84.95a27.48 27.48 0 1 0 27.48 27.47 27.5 27.5 0 0 0 -27.48-27.47z"></path>
                <path d="m158.08 165.49a15 15 0 0 1 -14.23-10.26l-25.71-77.23h-47.44a15 15 0 1 1 0-30h58.3a15 15 0 0 1 14.23 10.26l29.13 87.49a15 15 0 0 1 -14.23 19.74z"></path>
              </svg>
              Add to Cart
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="JS/shop.js"></script>
</body>

</html>