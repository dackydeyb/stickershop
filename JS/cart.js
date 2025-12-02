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
// === CART FUNCTIONALITY ===
// ===================================================================

document.addEventListener('DOMContentLoaded', function () {
  const cartItems = document.querySelectorAll('.cart-item-card');
  const grandTotalElement = document.getElementById('grand-total');
  const subtotalElement = document.getElementById('subtotal');
  const checkoutBtn = document.getElementById('checkout-btn');

  // Calculate and update totals
  function calculateTotals() {
    let subtotal = 0;

    cartItems.forEach(card => {
      const quantityInput = card.querySelector('.quantity-input');
      const basePrice = parseFloat(card.getAttribute('data-base-price'));
      const quantity = parseInt(quantityInput.value) || 1;
      const rowTotal = basePrice * quantity;

      // Update row total
      const totalAmountElement = card.querySelector('.total-amount');
      if (totalAmountElement) {
        totalAmountElement.textContent = rowTotal.toFixed(2);
      }

      subtotal += rowTotal;
    });

    // Update subtotal and grand total
    if (subtotalElement) {
      subtotalElement.textContent = subtotal.toFixed(2);
    }
    if (grandTotalElement) {
      grandTotalElement.textContent = subtotal.toFixed(2);
    }
  }

  // Update quantity in database (adds/removes items automatically)
  function updateQuantity(card, newQuantity) {
    const itemId = card.getAttribute('data-item-id');
    const oldQuantity = parseInt(card.getAttribute('data-current-quantity')) || 1;

    if (!itemId) {
      console.error('Item ID not found');
      return;
    }

    fetch('update_cart_quantity.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `item_id=${itemId}&quantity=${newQuantity}&old_quantity=${oldQuantity}`
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update the current quantity attribute
          card.setAttribute('data-current-quantity', newQuantity);

          // Update cart count in navigation
          updateCartCount(data.cartCount);

          // Update cart subtitle
          const cartItemCount = document.getElementById('cart-item-count');
          if (cartItemCount) {
            cartItemCount.textContent = data.cartCount;
          }
        } else {
          console.error('Failed to update quantity:', data.message);
          // Show error message if available
          if (data.message) {
            alert(data.message);
          }
          // Revert the input value on error
          const quantityInput = card.querySelector('.quantity-input');
          if (quantityInput) {
            // If server returned maxStock, use that, otherwise use old quantity
            if (data.maxStock !== undefined) {
              quantityInput.value = data.maxStock;
              card.setAttribute('data-current-quantity', data.maxStock);
              calculateTotals();
            } else {
              quantityInput.value = oldQuantity;
            }
          }
        }
      })
      .catch(error => {
        console.error('Error updating quantity:', error);
        // Revert the input value on error
        const quantityInput = card.querySelector('.quantity-input');
        const oldQuantity = parseInt(card.getAttribute('data-current-quantity')) || 1;
        if (quantityInput) {
          quantityInput.value = oldQuantity;
        }
      });
  }

  // Update cart count in navigation menu
  function updateCartCount(newCount) {
    const cartLink = document.querySelector('a[href="cart"]');
    if (cartLink) {
      const cartText = cartLink.textContent;
      const match = cartText.match(/\[(\d+)\]/);
      if (match) {
        cartLink.textContent = cartText.replace(/\[\d+\]/, `[${newCount}]`);
      } else {
        cartLink.textContent = cartText.replace('YOUR CART', `YOUR CART [${newCount}]`);
      }
    }
  }

  // Remove item from cart
  function removeItem(card, cartId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
      return;
    }

    fetch('remove_from_cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `cart_id=${cartId}`
    })
      .then(response => response.json()) // <-- Change .text() to .json()
      .then(data => {
        if (data.success) { // <-- Check data.success
          // Add fade out animation
          card.style.transition = 'opacity 0.3s, transform 0.3s';
          card.style.opacity = '0';
          card.style.transform = 'translateX(-20px)';

          setTimeout(() => {
            card.remove();

            // Recalculate totals from remaining items
            const remainingCards = document.querySelectorAll('.cart-item-card');
            let subtotal = 0;
            remainingCards.forEach(rc => {
              const price = parseFloat(rc.getAttribute('data-base-price')) || 0;
              const qty = parseInt(rc.querySelector('.quantity-input').value) || 1;
              subtotal += price * qty;
            });
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('grand-total').textContent = subtotal.toFixed(2);

            // Update cart count from server response
            updateCartCount(data.cartCount);

            // Update cart subtitle
            const cartItemCount = document.getElementById('cart-item-count');
            if (cartItemCount) {
              cartItemCount.textContent = data.cartCount;
            }

            // Check if cart is empty
            if (data.cartCount === 0) {
              location.reload(); // Reload to show empty cart message
            }
          }, 300);
        } else {
          alert('Failed to remove item.');
        }
      })
  }

  // Setup event listeners for each cart item
  cartItems.forEach(card => {
    const quantityInput = card.querySelector('.quantity-input');
    const plusBtn = card.querySelector('.plus-btn');
    const minusBtn = card.querySelector('.minus-btn');
    const removeBtn = card.querySelector('.remove-btn');
    const cartId = card.getAttribute('data-cart-id');

    // Get stock limit from data attribute
    const maxStock = parseInt(card.getAttribute('data-stock')) || 999;

    // Plus button - automatically adds items, no confirmation
    if (plusBtn) {
      plusBtn.addEventListener('click', function () {
        const currentValue = parseInt(quantityInput.value) || 1;
        if (currentValue >= maxStock) {
          alert('Cannot add more items. Only ' + maxStock + ' available in stock.');
          return;
        }
        const newValue = currentValue + 1;
        quantityInput.value = newValue;
        calculateTotals();
        updateQuantity(card, newValue); // Pass card and new quantity
      });
    }

    // Minus button - automatically removes items, no confirmation
    if (minusBtn) {
      minusBtn.addEventListener('click', function () {
        const currentValue = parseInt(quantityInput.value) || 1;
        if (currentValue > 1) {
          const newValue = currentValue - 1;
          quantityInput.value = newValue;
          calculateTotals();
          updateQuantity(card, newValue); // Pass card and new quantity
        }
      });
    }

    // Quantity input change - automatically updates, no confirmation
    if (quantityInput) {
      quantityInput.addEventListener('change', function () {
        const value = parseInt(this.value) || 1;
        if (value < 1) {
          this.value = 1;
          return;
        }
        if (value > maxStock) {
          alert('Cannot set quantity to ' + value + '. Only ' + maxStock + ' available in stock.');
          this.value = maxStock;
          calculateTotals();
          updateQuantity(card, maxStock);
          return;
        }
        calculateTotals();
        updateQuantity(card, value); // Pass card and new quantity
      });

      // Prevent invalid input
      quantityInput.addEventListener('keydown', function (e) {
        if (e.key === '-' || e.key === '+' || e.key === 'e' || e.key === 'E' || e.key === '.') {
          e.preventDefault();
        }
      });
    }

    // Remove button
    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        removeItem(card, cartId);
      });
    }
    // Paper Type Change
    const paperTypeSelects = document.querySelectorAll('.paper-type-select');
    paperTypeSelects.forEach(select => {
      select.addEventListener('change', function () {
        const cartId = this.getAttribute('data-cart-id');
        const paperType = this.value;
        const card = this.closest('.cart-item-card');

        fetch('update_cart_paper_type.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            cartId: cartId,
            paperType: paperType
          })
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update stock display
              const stockValue = card.querySelector('.stock-value');
              if (stockValue) {
                stockValue.textContent = data.newStock;
              }

              // Update quantity max and value if needed
              const quantityInput = card.querySelector('.quantity-input');
              if (quantityInput) {
                quantityInput.max = data.newStock;
                if (data.newQuantity !== undefined && parseInt(quantityInput.value) !== data.newQuantity) {
                  quantityInput.value = data.newQuantity;
                  card.setAttribute('data-current-quantity', data.newQuantity);
                  calculateTotals();
                }
              }

              // Update card data-stock attribute
              card.setAttribute('data-stock', data.newStock);

            } else {
              alert('Failed to update paper type: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error updating paper type');
          });
      });
    });

  });

  // Checkout button
  const checkoutModal = document.getElementById('checkout-modal');
  const checkoutModalClose = document.querySelector('.checkout-modal-close');
  const checkoutCancelBtn = document.getElementById('checkout-cancel-btn');
  const checkoutConfirmBtn = document.getElementById('checkout-confirm-btn');
  const thankYouModal = document.getElementById('thank-you-modal');
  const thankYouCloseBtn = document.getElementById('thank-you-close-btn');

  function openCheckoutModal() {
    // Get fresh cart items
    const currentCartItems = document.querySelectorAll('.cart-item-card');

    if (currentCartItems.length === 0) {
      alert('Your cart is empty.');
      return;
    }

    // Populate checkout items list
    const checkoutItemsList = document.getElementById('checkout-items-list');
    const checkoutSubtotal = document.getElementById('checkout-subtotal');
    const checkoutAdminFee = document.getElementById('checkout-admin-fee');
    const checkoutVat = document.getElementById('checkout-vat');
    const checkoutGrandTotal = document.getElementById('checkout-grand-total');

    let itemsHtml = '';
    let subtotal = 0;

    currentCartItems.forEach(card => {
      const itemName = card.querySelector('.item-name').textContent;
      const paperTypeSelect = card.querySelector('.paper-type-select');
      const paperType = paperTypeSelect ? paperTypeSelect.value : '';
      const quantity = parseInt(card.querySelector('.quantity-input').value) || 1;
      const basePrice = parseFloat(card.getAttribute('data-base-price')) || 0;
      const rowTotal = basePrice * quantity;
      subtotal += rowTotal;

      itemsHtml += `
        <div class="checkout-item-row">
          <span class="checkout-item-name">${itemName} <small>(${paperType})</small></span>
          <span class="checkout-item-quantity">x${quantity}</span>
          <span class="checkout-item-price">₱${rowTotal.toFixed(2)}</span>
        </div>
      `;
    });

    // Calculate admin fee (2%) and VAT (12%)
    const adminFee = subtotal * 0.02;
    const subtotalWithAdminFee = subtotal + adminFee;
    const vat = subtotalWithAdminFee * 0.12;
    const grandTotal = subtotalWithAdminFee + vat;

    checkoutItemsList.innerHTML = itemsHtml;
    checkoutSubtotal.textContent = subtotal.toFixed(2);
    checkoutAdminFee.textContent = adminFee.toFixed(2);
    checkoutVat.textContent = vat.toFixed(2);
    checkoutGrandTotal.textContent = grandTotal.toFixed(2);

    // Reset payment method to cash on delivery and hide credit card form
    const cashRadio = document.querySelector('input[name="payment_method"][value="cash"]');
    if (cashRadio) {
      cashRadio.checked = true;
    }
    const creditCardDetails = document.getElementById('credit-card-details');
    if (creditCardDetails) {
      creditCardDetails.style.display = 'none';
      // Clear credit card form
      const cardInputs = creditCardDetails.querySelectorAll('input');
      cardInputs.forEach(input => input.value = '');
    }

    // Show modal
    checkoutModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeCheckoutModal() {
    checkoutModal.classList.remove('active');
    document.body.style.overflow = '';
  }

  function openThankYouModal() {
    thankYouModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeThankYouModal() {
    thankYouModal.classList.remove('active');
    document.body.style.overflow = '';
    // Redirect to shop
    window.location.href = 'shop';
  }

  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function () {
      openCheckoutModal();
    });
  }

  if (checkoutModalClose) {
    checkoutModalClose.addEventListener('click', closeCheckoutModal);
  }

  if (checkoutCancelBtn) {
    checkoutCancelBtn.addEventListener('click', closeCheckoutModal);
  }

  // Show/hide credit card details based on payment method
  const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
  const creditCardDetails = document.getElementById('credit-card-details');

  paymentMethodRadios.forEach(radio => {
    radio.addEventListener('change', function () {
      if (this.value === 'paypal' && creditCardDetails) {
        creditCardDetails.style.display = 'block';
      } else if (creditCardDetails) {
        creditCardDetails.style.display = 'none';
        // Clear credit card form when hidden
        const cardInputs = creditCardDetails.querySelectorAll('input');
        cardInputs.forEach(input => input.value = '');
      }
    });
  });

  // Format card number input (add spaces every 4 digits)
  const cardNumberInput = document.getElementById('card-number');
  if (cardNumberInput) {
    cardNumberInput.addEventListener('input', function (e) {
      let value = e.target.value.replace(/\s/g, '');
      let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
      if (formattedValue.length <= 19) {
        e.target.value = formattedValue;
      }
    });
  }

  // Format expiry date input (MM/YY)
  const cardExpiryInput = document.getElementById('card-expiry');
  if (cardExpiryInput) {
    cardExpiryInput.addEventListener('input', function (e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      e.target.value = value;
    });
  }

  // Format CVV input (numbers only)
  const cardCvvInput = document.getElementById('card-cvv');
  if (cardCvvInput) {
    cardCvvInput.addEventListener('input', function (e) {
      e.target.value = e.target.value.replace(/\D/g, '');
    });
  }

  // Close modal when clicking overlay
  if (checkoutModal) {
    const overlay = checkoutModal.querySelector('.checkout-modal-overlay');
    if (overlay) {
      overlay.addEventListener('click', closeCheckoutModal);
    }
  }

  if (thankYouModal) {
    const overlay = thankYouModal.querySelector('.thank-you-modal-overlay');
    if (overlay) {
      overlay.addEventListener('click', closeThankYouModal);
    }
  }

  if (checkoutConfirmBtn) {
    checkoutConfirmBtn.addEventListener('click', function () {
      const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

      // Validate credit card details if PayPal is selected
      if (paymentMethod === 'paypal') {
        const cardNumber = document.getElementById('card-number').value.trim();
        const cardName = document.getElementById('card-name').value.trim();
        const cardExpiry = document.getElementById('card-expiry').value.trim();
        const cardCvv = document.getElementById('card-cvv').value.trim();

        if (!cardNumber || !cardName || !cardExpiry || !cardCvv) {
          alert('Please fill in all credit card details.');
          return;
        }

        // Validate card number format (at least 13 digits, max 19)
        const cardNumberDigits = cardNumber.replace(/\s/g, '');
        if (cardNumberDigits.length < 13 || cardNumberDigits.length > 19) {
          alert('Please enter a valid card number.');
          return;
        }

        // Validate expiry format (MM/YY)
        if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
          alert('Please enter a valid expiry date (MM/YY).');
          return;
        }

        // Validate CVV (3-4 digits)
        if (cardCvv.length < 3 || cardCvv.length > 4) {
          alert('Please enter a valid CVV (3-4 digits).');
          return;
        }
      }

      // Get all cart items data (fresh query)
      const currentCartItems = document.querySelectorAll('.cart-item-card');
      const cartData = [];
      currentCartItems.forEach(card => {
        const itemId = card.getAttribute('data-item-id');
        const quantity = parseInt(card.querySelector('.quantity-input').value) || 1;
        cartData.push({
          item_id: itemId,
          quantity: quantity
        });
      });

      // Disable button during processing
      checkoutConfirmBtn.disabled = true;
      checkoutConfirmBtn.textContent = 'Processing...';

      // Send checkout request
      fetch('checkout_process.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          payment_method: paymentMethod,
          items: cartData
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Close checkout modal
            closeCheckoutModal();
            // Show thank you modal
            setTimeout(() => {
              openThankYouModal();
            }, 300);
          } else {
            alert(data.message || 'Error processing checkout. Please try again.');
            checkoutConfirmBtn.disabled = false;
            checkoutConfirmBtn.textContent = 'Confirm Payment';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error processing checkout. Please try again.');
          checkoutConfirmBtn.disabled = false;
          checkoutConfirmBtn.textContent = 'Confirm Payment';
        });
    });
  }

  if (thankYouCloseBtn) {
    thankYouCloseBtn.addEventListener('click', closeThankYouModal);
  }

  // Close modals on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (checkoutModal && checkoutModal.classList.contains('active')) {
        closeCheckoutModal();
      }
      if (thankYouModal && thankYouModal.classList.contains('active')) {
        closeThankYouModal();
      }
    }
  });

  // Initial calculation
  calculateTotals();
});

