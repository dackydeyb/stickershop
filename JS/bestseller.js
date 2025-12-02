/* Hamburger Menu Checkbox */
document.addEventListener('DOMContentLoaded', function () {
  const menuCheckbox = document.querySelector('.hamburger input[type="checkbox"]');
  const menuText = document.querySelector('.hamburger .menu-text');

  if (menuCheckbox && menuText) {
    menuCheckbox.addEventListener('change', function () {
      if (menuCheckbox.checked) {
        menuText.textContent = 'CLOSE';
      } else {
        menuText.textContent = 'MENU';
      }
    });
  }
});

/* Menu Animation */
document.addEventListener('DOMContentLoaded', function () {
  var menuCheckbox = document.querySelector('.hamburger input[type="checkbox"]');
  var leftAnimation = document.getElementById('left-animation');
  var rightAnimation = document.getElementById('right-animation');
  
  // Safety check: ensure elements exist
  if (!menuCheckbox || !leftAnimation || !rightAnimation) {
    console.error('Menu elements not found');
    return;
  }
  
  var menuListItems = leftAnimation.querySelectorAll('.menu-list li');

  function resetMenuItems() {
    menuListItems.forEach(function (item) {
      item.style.opacity = '0';
      item.style.animation = 'none';
      void item.offsetWidth;
    });
  }

  function animateMenuItems() {
    menuListItems.forEach(function (item) {
      item.style.animation = 'none';
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
    });
    void leftAnimation.offsetWidth;
    menuListItems.forEach(function (item, index) {
      var delay = 200 + (index * 200);
      setTimeout(function () {
        void item.offsetWidth;
        item.style.animation = 'floatUpLinks 0.5s ease-out forwards';
        item.style.animationDelay = (delay / 1000) + 's';
      }, 10);
    });
  }

  function toggleAnimations() {
    leftAnimation.classList.remove('animate-in-left', 'animate-out-left');
    rightAnimation.classList.remove('animate-in-right', 'animate-out-right');

    if (menuCheckbox.checked) {
      leftAnimation.style.display = 'flex';
      rightAnimation.style.display = 'flex';
      requestAnimationFrame(() => {
        leftAnimation.classList.add('animate-in-left');
        rightAnimation.classList.add('animate-in-right');
        setTimeout(animateMenuItems, 50);
      });
    } else {
      menuListItems.forEach(function (item) {
        item.style.opacity = '0';
      });
      leftAnimation.classList.add('animate-out-left');
      rightAnimation.classList.add('animate-out-right');
    }
  }

  function handleAnimationEnd(event) {
    const target = event.target;
    if (!menuCheckbox.checked) {
      target.style.display = 'none';
      target.classList.remove('animate-out-left', 'animate-out-right');
      resetMenuItems();
    } else {
      target.classList.remove('animate-in-left', 'animate-in-right');
    }
  }

  menuCheckbox.addEventListener('change', toggleAnimations);
  leftAnimation.addEventListener('animationend', handleAnimationEnd);
  rightAnimation.addEventListener('animationend', handleAnimationEnd);
});

// ===================================================================
// === NEW DYNAMIC SHOP LOGIC STARTS HERE ===
// ===================================================================

/**
 * We wrap your card animation logic in a function.
 * This lets us call it on the *first* page load AND
 * every time we fetch *new* items.
 */
function initializeCardAnimations() {
  const cards = document.querySelectorAll('.card');
  if (cards.length === 0) return; // No cards to animate

  const cardsContainer = document.querySelector('.main-right-content');

  // 1. Function to apply staggered delays
  function applyStaggerDelay() {
    if (!cards.length) return;
    const containerWidth = cardsContainer.clientWidth;
    const cardWidth = cards[0].offsetWidth;
    const gap = parseFloat(window.getComputedStyle(cardsContainer).gap) || 34;
    const numColumns = Math.floor((containerWidth + gap) / (cardWidth + gap));
    const delayIncrement = 150; // 0.15s

    cards.forEach((card, index) => {
      const columnIndex = index % numColumns;
      const delay = columnIndex * delayIncrement;
      card.style.transitionDelay = `${delay}ms`;
    });
  }

  // Run the delay function
  applyStaggerDelay();
  // Re-run if window resizes
  // Note: We might want a more robust resize handler, but this is simple
  window.addEventListener('resize', applyStaggerDelay);

  // 2. Intersection Observer logic (your code, unchanged)
  const cardObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('card-animated');
      } else {
        entry.target.classList.remove('card-animated');
      }
    });
  }, {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
  });

  // Observe all cards
  cards.forEach(card => {
    cardObserver.observe(card);
  });
}

/**
 * Main function to fetch products from our new API.
 * It builds a URL with the current filters and updates the page.
 */
async function fetchProducts(page = 1) {
  
  // --- NEW: Smoothly scroll to the top of the shop content ---
  const headerTitle = document.querySelector('.header-title');
  if (headerTitle) {
    headerTitle.scrollIntoView({ behavior: 'smooth' });
  }
  // --- END OF NEW CODE ---

  const searchInput = document.getElementById('search-bar');
  const sortInput = document.querySelector('input[name="sort-price"]:checked');
  
  // --- FIX #1 ---
  const categoryInput = document.querySelector('input[name="game-category"]:checked');

  const searchValue = searchInput ? searchInput.value : '';
  const sortValue = sortInput ? sortInput.value : 'default';
  const categoryValue = categoryInput ? categoryInput.value : 'all'; // This will no longer error!
  
  // Show some loading state (optional, but good UX)  
  const itemsContainer = document.querySelector('.main-right-content');
  itemsContainer.style.opacity = '0.8';

  try {
    // 1. Build the URL and fetch data
    const url = `api-fetch-items.php?page=${page}&sort=${sortValue}&search=${encodeURIComponent(searchValue)}&category=${encodeURIComponent(categoryValue)}`;
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    const data = await response.json();

    // 2. Get the containers to update
    const paginationContainer = document.querySelector('.pagination-container');

    // 3. Replace the HTML with the new HTML from the server
    itemsContainer.innerHTML = data.items_html;
    paginationContainer.innerHTML = data.pagination_html;

    // 4. Re-initialize animations for the new cards
    initializeCardAnimations();

  } catch (error) {
    console.error('Fetch error:', error);
    itemsContainer.innerHTML = '<p style="width: 100%; text-align: center; color: red;">Error loading stickers. Please try again.</p>';
  } finally {
    // Restore opacity
    itemsContainer.style.opacity = '1';
  }
}

// === ADD EVENT LISTENERS ===
document.addEventListener('DOMContentLoaded', function () {
  
  // 1. Run card animations on the *first* page load
  initializeCardAnimations();

  // 2. Get the filter inputs
  const searchInput = document.getElementById('search-bar');
  const sortRadios = document.querySelectorAll('input[name="sort-price"]');

  // --- FIX #2 ---
  const categoryRadios = document.querySelectorAll('input[name="game-category"]');
  
  const rightPanel = document.querySelector('.right-panel'); // For pagination clicks
  
  // --- NEW: Get the form that holds the search bar ---
  const searchForm = searchInput ? searchInput.closest('form') : null;

  // 3. Add listener for the search bar (triggers on "input", i.e., typing)
  // We "debounce" this so it doesn't fire on *every single keystroke*
  let searchTimeout;
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetchProducts(1); // Search always resets to page 1
      }, 300); // Wait 300ms after user stops typing
    });
  }

  // --- NEW FIX: Add a 'submit' listener to the form ---
  if (searchForm) {
    searchForm.addEventListener('submit', function(event) {
      // This is the key: it stops the browser from trying to go to "/submit"
      event.preventDefault(); 
      
      // We can also clear the debounce and run the search immediately
      clearTimeout(searchTimeout); 
      fetchProducts(1);
    });
  }

  // 4. Add listener for all sort radio buttons
  sortRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      fetchProducts(1); // Sorting resets to page 1
    });
  });

  if (categoryRadios.length > 0) {
    categoryRadios.forEach(radio => {
      radio.addEventListener('change', () => {
        fetchProducts(1); // Category filter resets to page 1
      });
    });
  }

  // 5. Add listener for pagination clicks
  // We use event delegation on the container, since the links inside will be replaced
  if (rightPanel) {
    rightPanel.addEventListener('click', function(event) {
      // Find the link that was clicked
      const pageLink = event.target.closest('a.page-link');

      // If it wasn't a page link, or if it's disabled, do nothing
      if (!pageLink || pageLink.closest('.page-item.disabled')) {
        return;
      }
      
      // Stop the link from actually navigating
      event.preventDefault(); 
      
      // Get the page number from the link's href
      const url = new URL(pageLink.href);
      const page = url.searchParams.get('page');

      if (page) {
        fetchProducts(page);
      }
    });
  }

});

// ===================================================================
// === PRODUCT MODAL FUNCTIONALITY (UPGRADED) ===
// ===================================================================

document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('product-modal');
  if (!modal) return; // Safety check if modal doesn't exist on this page

  const modalOverlay = modal.querySelector('.modal-overlay');
  const modalClose = modal.querySelector('.modal-close');
  const modalImage = document.getElementById('modal-image');
  const modalTitle = document.getElementById('modal-title');
  const modalDescription = document.getElementById('modal-description');
  const modalPriceAmount = document.getElementById('modal-price-amount');
  const modalQuantityInput = document.getElementById('modal-quantity');
  const modalTotalAmount = document.getElementById('modal-total-amount');
  const modalPlusBtn = modal.querySelector('.plus-btn');
  const modalMinusBtn = modal.querySelector('.minus-btn');
  const modalItemId = document.getElementById('modal-item-id');
  const modalItemName = document.getElementById('modal-item-name');
  const modalItemPrice = document.getElementById('modal-item-price');
  const modalItemQuantity = document.getElementById('modal-item-quantity');
  
  let currentPrice = 0;

  // Function to open modal
  function openModal(card) {
    const itemId = card.getAttribute('data-item-id');
    const itemName = card.getAttribute('data-item-name');
    const itemPrice = card.getAttribute('data-item-price');
    const itemImage = card.getAttribute('data-item-image');
    const itemDescription = card.getAttribute('data-item-description');
    const itemStock = parseInt(card.getAttribute('data-item-stock')) || 0;
    const isOutOfStock = itemStock <= 0;

    // Set modal content
    modalImage.src = `images/${itemImage}`;
    modalImage.alt = itemName;
    modalTitle.textContent = itemName;
    modalDescription.textContent = itemDescription;
    modalPriceAmount.textContent = parseFloat(itemPrice).toFixed(2);
    
    // Handle stock display
    const modalStockSection = document.getElementById('modal-stock-section');
    const modalStockValue = document.getElementById('modal-stock-value');
    const modalOutOfStockMessage = document.getElementById('modal-out-of-stock-message');
    const modalAddToCartBtn = document.getElementById('modal-add-to-cart-btn');
    const modalAddToCartForm = document.querySelector('.modal-add-to-cart-form');
    const modalItemStock = document.getElementById('modal-item-stock');
    
    if (modalStockSection) {
      if (itemStock > 0) {
        modalStockSection.style.display = 'block';
        if (modalStockValue) modalStockValue.textContent = itemStock;
      } else {
        modalStockSection.style.display = 'none';
      }
    }
    
    if (modalOutOfStockMessage) {
      modalOutOfStockMessage.style.display = isOutOfStock ? 'block' : 'none';
    }
    
    if (modalAddToCartBtn) {
      modalAddToCartBtn.disabled = isOutOfStock;
      if (isOutOfStock) {
        modalAddToCartBtn.style.opacity = '0.5';
        modalAddToCartBtn.style.cursor = 'not-allowed';
      } else {
        modalAddToCartBtn.style.opacity = '1';
        modalAddToCartBtn.style.cursor = 'pointer';
      }
    }
    
    if (modalAddToCartForm) {
      if (isOutOfStock) {
        modalAddToCartForm.onsubmit = function(e) { e.preventDefault(); return false; };
      } else {
        modalAddToCartForm.onsubmit = null;
      }
    }
    
    // Set quantity input max to stock
    modalQuantityInput.value = 1;
    modalQuantityInput.max = itemStock > 0 ? itemStock : 1;
    if (isOutOfStock) {
      modalQuantityInput.disabled = true;
    } else {
      modalQuantityInput.disabled = false;
    }
    
    // Set form hidden inputs
    modalItemId.value = itemId;
    modalItemName.value = itemName;
    modalItemPrice.value = itemPrice;
    modalItemQuantity.value = 1;
    if (modalItemStock) modalItemStock.value = itemStock;

    // Store current price
    currentPrice = parseFloat(itemPrice);

    // Calculate initial total
    updateModalTotal();

    // Show modal with animation
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
  }

  // Function to close modal
  function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
  }

  // Function to update total price
  function updateModalTotal() {
    const quantity = parseInt(modalQuantityInput.value) || 1;
    const total = currentPrice * quantity;
    modalTotalAmount.textContent = total.toFixed(2);
    modalItemQuantity.value = quantity;
  }

  // Add click event to all cards (but not the add to cart button)
  document.addEventListener('click', function(e) {
    const card = e.target.closest('.card');
    if (!card) return;

    // Don't open modal if clicking on the add to cart button or form
    if (e.target.closest('.add-to-cart-form') || e.target.closest('.card-btn')) {
      return;
    }

    // Open modal
    openModal(card);
  });

  // Close modal when clicking overlay
  if (modalOverlay) {
    modalOverlay.addEventListener('click', closeModal);
  }

  // Close modal when clicking close button
  if (modalClose) {
    modalClose.addEventListener('click', closeModal);
  }

  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });

  // Quantity controls
  if (modalPlusBtn) {
    modalPlusBtn.addEventListener('click', function() {
      const currentValue = parseInt(modalQuantityInput.value) || 1;
      const maxStock = parseInt(modalQuantityInput.max) || 999;
      if (currentValue < maxStock) {
        modalQuantityInput.value = currentValue + 1;
        updateModalTotal();
      }
    });
  }

  if (modalMinusBtn) {
    modalMinusBtn.addEventListener('click', function() {
      const currentValue = parseInt(modalQuantityInput.value) || 1;
      if (currentValue > 1) {
        modalQuantityInput.value = currentValue - 1;
        updateModalTotal();
      }
    });
  }

  // Update total when quantity input changes
  if (modalQuantityInput) {
    modalQuantityInput.addEventListener('change', function() {
      const value = parseInt(this.value) || 1;
      const maxStock = parseInt(this.max) || 999;
      if (value < 1) {
        this.value = 1;
      } else if (value > maxStock) {
        this.value = maxStock;
      }
      updateModalTotal();
    });

    // Prevent invalid input
    modalQuantityInput.addEventListener('keydown', function(e) {
      if (e.key === '-' || e.key === '+' || e.key === 'e' || e.key === 'E' || e.key === '.') {
        e.preventDefault();
      }
    });
  }

  // Re-initialize modal when new cards are loaded via AJAX
  const originalFetchProducts = window.fetchProducts;
  if (originalFetchProducts) {
    window.fetchProducts = function(...args) {
      return originalFetchProducts.apply(this, args).then(() => {
        // Modal functionality is already set up via event delegation
        // so it should work with dynamically loaded cards
      });
    };
  }
});

// ===================================================================
// === AJAX ADD TO CART FUNCTIONALITY ===
// ===================================================================

document.addEventListener('DOMContentLoaded', function() {
  // Handle add to cart form submissions (both card buttons and modal)
  document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Check if it's an add to cart form
    if (form.classList.contains('add-to-cart-form') || form.classList.contains('modal-add-to-cart-form')) {
      e.preventDefault(); // Prevent default form submission
      
      const formData = new FormData(form);
      const button = form.querySelector('button[type="submit"]');
      const originalButtonText = button.innerHTML;
      
      // Disable button and show loading state
      button.disabled = true;
      button.style.opacity = '0.6';
      button.style.cursor = 'not-allowed';
      
      // Convert FormData to URL-encoded string
      const params = new URLSearchParams();
      for (const [key, value] of formData.entries()) {
        params.append(key, value);
      }
      
      // Send AJAX request
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
      })
      .then(response => response.json())
      .then(data => {
        // Re-enable button
        button.disabled = false;
        button.style.opacity = '1';
        button.style.cursor = 'pointer';
        
        if (data.success) {
          // Show success message
          showNotification(data.message, 'success');
          
          // Update cart count in navigation if it exists
          const cartLink = document.querySelector('a[href="cart"]');
          if (cartLink && data.cartCount !== undefined) {
            const cartText = cartLink.textContent;
            const match = cartText.match(/\[(\d+)\]/);
            if (match) {
              cartLink.textContent = cartText.replace(/\[\d+\]/, `[${data.cartCount}]`);
            } else {
              cartLink.textContent = cartText.replace('YOUR CART', `YOUR CART [${data.cartCount}]`);
            }
          }
          
          // Close modal if it was opened from modal
          if (form.classList.contains('modal-add-to-cart-form')) {
            const modal = document.getElementById('product-modal');
            if (modal) {
              setTimeout(() => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
              }, 500); // Close after showing success message
            }
          }
        } else {
          if (data.message === 'not_logged_in') {
            showNotification('You need to log in first.', 'error');
            setTimeout(() => {
              window.location.href = 'login.php';
            }, 1500);
          } else {
            showNotification(data.message || 'Failed to add item to cart.', 'error');
          }
        }
      })
      .catch(error => {
        // Re-enable button
        button.disabled = false;
        button.style.opacity = '1';
        button.style.cursor = 'pointer';
        
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
      });
    }
  });
});

// ===================================================================
// === STACKABLE NOTIFICATION SYSTEM ===
// ===================================================================

// Stackable Notification System
(function() {
  // Create notification container if it doesn't exist
  let notificationContainer = document.getElementById('cart-notification-container');
  if (!notificationContainer) {
    notificationContainer = document.createElement('div');
    notificationContainer.id = 'cart-notification-container';
    notificationContainer.style.cssText = `
      position: fixed;
      top: 80px;
      right: 20px;
      z-index: 10000;
      display: flex;
      flex-direction: column;
      gap: 4px;
      pointer-events: none;
    `;
    document.body.appendChild(notificationContainer);
  }

  const MAX_NOTIFICATIONS = 3;
  const notifications = [];
  const notificationTimeouts = new WeakMap(); // Track timeouts for each notification

  function showNotification(message, type = 'success') {
    // Clean up any notifications that are no longer in DOM
    updateNotificationPositions();
    
    // Remove oldest notification if we're at max capacity
    if (notifications.length >= MAX_NOTIFICATIONS) {
      const oldestNotification = notifications.shift();
      // Clear its timeout if it exists
      const timeout = notificationTimeouts.get(oldestNotification);
      if (timeout) {
        clearTimeout(timeout);
        notificationTimeouts.delete(oldestNotification);
      }
      removeNotification(oldestNotification, false);
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `cart-notification cart-notification-${type}`;
    notification.textContent = message;

    // Add styles
    notification.style.cssText = `
      background: ${type === 'success' ? '#4CAF50' : '#f44336'};
      color: white;
      padding: 16px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      font-family: 'Rubik', sans-serif;
      font-size: 1rem;
      font-weight: 500;
      max-width: 300px;
      border: 2px solid #323232;
      opacity: 0;
      transform: translateX(400px);
      transition: opacity 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55), transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55), margin-top 0.3s ease-out;
      pointer-events: auto;
      cursor: pointer;
    `;

    // Add click to dismiss
    notification.addEventListener('click', () => {
      const timeout = notificationTimeouts.get(notification);
      if (timeout) {
        clearTimeout(timeout);
        notificationTimeouts.delete(notification);
      }
      removeNotification(notification, true);
    });

    // Add to container and array
    notificationContainer.appendChild(notification);
    notifications.push(notification);

    // Animate in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
        // Update positions after animation starts (notification is now in DOM)
        setTimeout(() => {
          updateNotificationPositions();
        }, 50);
      });
    });

    // Auto remove after 3 seconds - store timeout reference
    const timeout = setTimeout(() => {
      notificationTimeouts.delete(notification);
      removeNotification(notification, true);
    }, 3000);
    notificationTimeouts.set(notification, timeout);
  }

  function updateNotificationPositions() {
    // Clean up array first - remove any notifications that are no longer in DOM
    const activeNotifications = notifications.filter(n => {
      const isInDOM = n.parentNode === notificationContainer;
      if (!isInDOM) {
        // Clean up timeout if it exists
        const timeout = notificationTimeouts.get(n);
        if (timeout) {
          clearTimeout(timeout);
          notificationTimeouts.delete(n);
        }
      }
      return isInDOM;
    });
    
    // Update the notifications array to only include active ones
    notifications.length = 0;
    notifications.push(...activeNotifications);
    
    let currentTop = 0;
    activeNotifications.forEach((notification) => {
      // Skip if notification is being removed (opacity is 0)
      if (notification.style.opacity === '0') {
        return;
      }
      notification.style.marginTop = `${currentTop}px`;
      // Calculate next position based on actual height + gap
      const height = notification.offsetHeight || 60; // Fallback height if not rendered yet
      currentTop += height + 4; // 4px gap
    });
  }

  function removeNotification(notification, updatePositions = true) {
    const index = notifications.indexOf(notification);
    if (index === -1) {
      // Already removed from array, but might still be in DOM - clean it up
      if (notification.parentNode === notificationContainer) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
          if (notification.parentNode === notificationContainer) {
            notificationContainer.removeChild(notification);
          }
        }, 400);
      }
      return;
    }

    // Check if already being removed (has opacity 0 and transform)
    if (notification.style.opacity === '0' && notification.style.transform.includes('translateX(400px)')) {
      return; // Already being removed
    }

    // Animate out
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(400px)';

    // Remove from array immediately
    notifications.splice(index, 1);

    // Remove from DOM after animation
    setTimeout(() => {
      if (notification.parentNode === notificationContainer) {
        notificationContainer.removeChild(notification);
      }
      // Clean up timeout
      const timeout = notificationTimeouts.get(notification);
      if (timeout) {
        clearTimeout(timeout);
        notificationTimeouts.delete(notification);
      }
      // Update positions of remaining notifications
      if (updatePositions) {
        updateNotificationPositions();
      }
    }, 400);
  }

  // Make showNotification available globally
  window.showNotification = showNotification;
})();