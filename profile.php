<?php
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_option = $_POST['payment_option'];
    
    // Handle File Upload
    $profile_image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "profile_images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image_path = $new_filename;
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $message_type = "error";
            }
        } else {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $message_type = "error";
        }
    }

    // Update Database
    try {
        $sql = "UPDATE users SET name = :name, phone = :phone, address = :address, payment_option = :payment_option";
        
        if ($profile_image_path) {
            $sql .= ", profile_image = :profile_image";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':payment_option', $payment_option);
        $stmt->bindParam(':id', $user_id);
        
        if ($profile_image_path) {
            $stmt->bindParam(':profile_image', $profile_image_path);
        }
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating profile.";
            $message_type = "error";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch User Data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Determine profile image source
$profile_img_src = 'Elements/profile.png'; // Default
if (!empty($user['profile_image'])) {
    $profile_img_src = 'profile_images/' . $user['profile_image'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kawaii Kingdom</title>
    <link rel="stylesheet" href="CSS/home.css" />
    <link rel="stylesheet" href="CSS/profile.css">
    <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>
<body>

  <audio id="bg-music" autoplay>
    <source src="./music/Greeny.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>
  
  <div id="left-animation" class="side-animation">
    <ul class="menu-list">
      <li><a href="index.php">HOME</a></li>
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
                <img src="<?php echo htmlspecialchars($profile_img_src); ?>" alt="Profile" id="profile-icon" style="object-fit: cover; border-radius: 50%;">
              </a>
            </div>
          </li>
        </ul>
      </nav>
    </header>

        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <h1>Customize Profile</h1>
                    <p>Update your personal information and preferences</p>
                </div>

                <!-- Alert removed, using toast notification instead -->

                <form class="profile-form" method="POST" enctype="multipart/form-data">
                    <div class="profile-image-section">
                        <div class="profile-img-wrapper">
                            <img src="<?php echo htmlspecialchars($profile_img_src); ?>" alt="Profile Picture" class="profile-img-display" id="preview-image">
                        </div>
                        <label for="profile_image" class="file-upload-label">Change Photo</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewFile()">
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly title="Email cannot be changed">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g., 09123456789">
                    </div>

                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your full address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="payment_option">Preferred Payment Option</label>
                        <select id="payment_option" name="payment_option">
                            <option value="">Select Payment Method</option>
                            <option value="GCash" <?php echo ($user['payment_option'] ?? '') == 'GCash' ? 'selected' : ''; ?>>GCash</option>
                            <option value="COD" <?php echo ($user['payment_option'] ?? '') == 'COD' ? 'selected' : ''; ?>>Cash on Delivery (COD)</option>
                            <option value="Credit Card" <?php echo ($user['payment_option'] ?? '') == 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                            <option value="PayPal" <?php echo ($user['payment_option'] ?? '') == 'PayPal' ? 'selected' : ''; ?>>PayPal</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-save">Save Changes</button>
                    
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <a href="cart.php" class="btn-save" style="text-decoration: none; display: block; text-align: center; background-color: #333;">View Cart</a>
                        <a href="logout.php" class="btn-save" style="text-decoration: none; display: block; text-align: center; background-color: #d32f2f;">Logout</a>
                    </div>
                </form>
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

    <script>
        function previewFile() {
            const preview = document.getElementById('preview-image');
            const file = document.querySelector('input[type=file]').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // Notification System
        function showNotification(message, type = 'success') {
            // Remove existing notification if any
            const existingNotification = document.querySelector('.cart-notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `cart-notification cart-notification-${type}`;
            notification.textContent = message;
            
            // Add styles
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : '#f44336'};
                color: white;
                padding: 16px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 10000;
                font-family: 'Rubik', sans-serif;
                font-size: 1rem;
                font-weight: 500;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
                max-width: 300px;
                border: 2px solid #323232;
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            requestAnimationFrame(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            });
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        <?php if ($message): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification("<?php echo addslashes($message); ?>", "<?php echo $message_type; ?>");
            });
        <?php endif; ?>
    </script>
    <script src="JS/home.js"></script>
</body>
</html>
