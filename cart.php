<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Database connection
include 'connection.php';

// Fetch cart items
// NO MORE GROUP BY or COUNT(*)! We now select the quantity directly.
$query = $conn->prepare("
    SELECT 
        cart.id as cart_id,
        cart.item_id,
        cart.quantity,
        cart.paper_type,
        items.image,
        items.name,
        items.price
    FROM cart 
    INNER JOIN items ON cart.item_id = items.id 
    WHERE cart.user_id = ?
    ORDER BY cart.id DESC
");
$query->execute([$user_id]);
$cart_items = $query->fetchAll(PDO::FETCH_ASSOC);

// For each item, fetch stock for ALL paper types
foreach ($cart_items as &$item) {
    $stockStmt = $conn->prepare("SELECT paper_type, stock FROM item_stock WHERE item_id = ?");
    $stockStmt->execute([$item['item_id']]);
    $item['stocks'] = $stockStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Set current stock based on selected paper type
    $currentPaperType = $item['paper_type'];
    $item['stock'] = isset($item['stocks'][$currentPaperType]) ? $item['stocks'][$currentPaperType] : 0;
}
unset($item);

// Get total cart count (SUM of all quantities)
$countQuery = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
$countQuery->execute([$user_id]);
$cartCount = $countQuery->fetchColumn();
if ($cartCount === null) {
    $cartCount = 0;
}

// Fetch user profile image
$userQuery = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$userQuery->execute([$user_id]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);
$profile_image = !empty($user['profile_image']) ? 'profile_images/' . $user['profile_image'] : 'Elements/profile.png';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Kawaii Kingdom</title>
    <link rel="stylesheet" href="CSS/cart.css" />
    <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
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
            <li><a href="cart">YOUR CART [<?php echo $cartCount; ?>]</a></li>
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
                            <a href="shop">
                                <img src="Elements/search.png" alt="Search-Icon" id="search-icon">
                            </a>
                            <a href="profile.php">
                                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" id="profile-icon" style="object-fit: cover; border-radius: 50%;">
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
        </header>

        <div class="cart-container">
            <div class="cart-header">
                <h1 class="cart-title">Your Shopping Cart</h1>
                <?php if (count($cart_items) > 0) : ?>
                    <p class="cart-subtitle"><span id="cart-item-count"><?php echo $cartCount; ?></span> item(s) in your cart</p>
                <?php else : ?>
                    <p class="cart-subtitle">Your cart is empty</p>
                <?php endif; ?>
            </div>

            <?php if (count($cart_items) > 0) : ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item) : 
                            $basePrice = floatval($item['price']);
                            $quantity = intval($item['quantity']);
                            $stock = isset($item['stock']) ? (int)$item['stock'] : 0;
                            $rowTotal = $basePrice * $quantity;
                            $availableStock = $stock;
                        ?>
                            <div class="cart-item-card" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>" data-base-price="<?php echo $basePrice; ?>" data-current-quantity="<?php echo $quantity; ?>" data-stock="<?php echo $availableStock; ?>">
                                <div class="item-image-container">
                                    <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="item-price-info">
                                        <span class="base-price">₱<?php echo number_format($basePrice, 2); ?></span>
                                        <span class="price-label">per item</span>
                                    </div>
                                    
                                    <div class="item-paper-type" style="margin-top: 10px;">
                                        <label style="font-size: 0.9rem; color: #666;">Paper Type:</label>
                                        <select class="paper-type-select" data-cart-id="<?php echo $item['cart_id']; ?>">
                                            <?php foreach ($item['stocks'] as $type => $typeStock): ?>
                                                <option value="<?php echo $type; ?>" 
                                                    <?php echo ($type == $item['paper_type']) ? 'selected' : ''; ?>
                                                    <?php echo ($typeStock <= 0) ? 'disabled' : ''; ?>
                                                >
                                                    <?php echo $type; ?> (<?php echo $typeStock > 0 ? $typeStock : 'Out of Stock'; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="item-stock-info" style="margin-top: 5px;">
                                        <span class="stock-label">Stock Available: </span>
                                        <span class="stock-value"><?php echo $availableStock; ?></span>
                                    </div>
                                </div>

                                <div class="item-quantity">
                                    <label class="quantity-label">Quantity</label>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn" type="button" aria-label="Decrease quantity">−</button>
                                        <input type="number" class="quantity-input" value="<?php echo $quantity; ?>" min="1" max="<?php echo $availableStock; ?>" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">
                                        <button class="quantity-btn plus-btn" type="button" aria-label="Increase quantity">+</button>
                                    </div>
                                    <?php if ($quantity > $availableStock) : ?>
                                        <div class="stock-warning" style="color: #c62828; font-size: 12px; margin-top: 5px;">
                                            Warning: Quantity exceeds available stock
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="item-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-price">₱<span class="total-amount"><?php echo number_format($rowTotal, 2); ?></span></span>
                                </div>

                                <button class="remove-btn" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>" aria-label="Remove item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-card">
                            <h2 class="summary-title">Order Summary</h2>
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>₱<span id="subtotal">0.00</span></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span class="free-shipping">FREE</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row total-row">
                                <span>Grand Total</span>
                                <span class="grand-total">₱<span id="grand-total">0.00</span></span>
                            </div>
                            <button class="checkout-btn" id="checkout-btn">
                                Proceed to Checkout
                            </button>
                            <a href="shop" class="continue-shopping-btn">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added anything to your cart yet.</p>
                    <a href="shop" class="shop-now-btn">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Checkout Modal -->
    <div id="checkout-modal" class="checkout-modal">
        <div class="checkout-modal-overlay"></div>
        <div class="checkout-modal-container">
            <button class="checkout-modal-close" aria-label="Close modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="checkout-modal-content">
                <h2 class="checkout-modal-title">Checkout</h2>
                
                <div class="checkout-order-summary">
                    <h3>Order Summary</h3>
                    <div id="checkout-items-list"></div>
                    <div class="checkout-total-section">
                        <div class="checkout-total-row">
                            <span>Subtotal:</span>
                            <span>₱<span id="checkout-subtotal">0.00</span></span>
                        </div>
                        <div class="checkout-total-row">
                            <span>Admin Fee (2%):</span>
                            <span>₱<span id="checkout-admin-fee">0.00</span></span>
                        </div>
                        <div class="checkout-total-row">
                            <span>VAT (12%):</span>
                            <span>₱<span id="checkout-vat">0.00</span></span>
                        </div>
                        <div class="checkout-total-row">
                            <span>Shipping:</span>
                            <span class="free-shipping">FREE</span>
                        </div>
                        <div class="checkout-total-divider"></div>
                        <div class="checkout-total-row checkout-grand-total">
                            <span>Grand Total:</span>
                            <span>₱<span id="checkout-grand-total">0.00</span></span>
                        </div>
                    </div>
                </div>

                <div class="checkout-payment-section">
                    <h3>Payment Method</h3>
                    <div class="payment-methods">
                        <label class="payment-method-option">
                            <input type="radio" name="payment_method" value="cash" checked>
                            <span class="payment-method-label">
                                <span class="payment-icon">💵</span>
                                <span>Cash on Delivery</span>
                            </span>
                        </label>
                        <label class="payment-method-option">
                            <input type="radio" name="payment_method" value="paypal" id="payment-paypal">
                            <span class="payment-method-label">
                                <span class="payment-icon">💳</span>
                                <span>PayPal / Credit Card</span>
                            </span>
                        </label>
                        <label class="payment-method-option">
                            <input type="radio" name="payment_method" value="gcash">
                            <span class="payment-method-label">
                                <span class="payment-icon">📱</span>
                                <span>GCash</span>
                            </span>
                        </label>
                        <label class="payment-method-option">
                            <input type="radio" name="payment_method" value="paymaya">
                            <span class="payment-method-label">
                                <span class="payment-icon">💳</span>
                                <span>PayMaya</span>
                            </span>
                        </label>
                        <label class="payment-method-option">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <span class="payment-method-label">
                                <span class="payment-icon">🏦</span>
                                <span>Bank Transfer</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Credit Card Details (shown when PayPal is selected) -->
                <div class="credit-card-details" id="credit-card-details" style="display: none;">
                    <h3>Credit Card Details</h3>
                    <div class="credit-card-form">
                        <div class="form-group">
                            <label for="card-number">Card Number</label>
                            <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>
                        <div class="form-group">
                            <label for="card-name">Cardholder Name</label>
                            <input type="text" id="card-name" name="card_name" placeholder="John Doe" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card-expiry">Expiry Date</label>
                                <input type="text" id="card-expiry" name="card_expiry" placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label for="card-cvv">CVV</label>
                                <input type="text" id="card-cvv" name="card_cvv" placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-actions">
                    <button class="checkout-cancel-btn" id="checkout-cancel-btn">Cancel</button>
                    <button class="checkout-confirm-btn" id="checkout-confirm-btn">Confirm Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Thank You Modal -->
    <div id="thank-you-modal" class="thank-you-modal">
        <div class="thank-you-modal-overlay"></div>
        <div class="thank-you-modal-container">
            <div class="thank-you-content">
                <div class="thank-you-icon">✓</div>
                <h2 class="thank-you-title">Thank You!</h2>
                <p class="thank-you-message">Your order has been placed successfully.</p>
                <p class="thank-you-submessage">We appreciate your purchase!</p>
                <button class="thank-you-btn" id="thank-you-close-btn">Continue Shopping</button>
            </div>
        </div>
    </div>

    <script src="JS/cart.js"></script>
    <script src="JS/shop.js"></script>
</body>

</html>
