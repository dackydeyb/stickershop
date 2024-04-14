/* Hamburger Menu Checkbox */
document.addEventListener('DOMContentLoaded', function() {
    const menuCheckbox = document.querySelector('.hamburger input[type="checkbox"]');
    const menuText = document.querySelector('.hamburger .menu-text');
  
    menuCheckbox.addEventListener('change', function() {
      if (menuCheckbox.checked) {
        menuText.textContent = 'CLOSE';
      } else {
        menuText.textContent = 'MENU';
      }
    });
  });

  /* Menu Animation */
document.addEventListener('DOMContentLoaded', function() {
    var menuCheckbox = document.querySelector('.hamburger input[type="checkbox"]');
    var leftAnimation = document.getElementById('left-animation');
    var rightAnimation = document.getElementById('right-animation');
  
    function toggleAnimations() {
      if (menuCheckbox.checked) {
        // Add opening animations and set display to flex
        leftAnimation.classList.add('animate-in-left');
        rightAnimation.classList.add('animate-in-right');
        leftAnimation.style.display = 'flex';
        rightAnimation.style.display = 'flex';
      } else {
        // Add closing animations
        leftAnimation.classList.add('animate-out-left');
        rightAnimation.classList.add('animate-out-right');
      }
    }
  
    function handleAnimationEnd(event) {
      // Remove the animation classes to reset the state
      event.target.classList.remove('animate-in-left', 'animate-out-left', 'animate-in-right', 'animate-out-right');
  
      // Hide the element if it's the closing animation
      if (!menuCheckbox.checked) {
        event.target.style.display = 'none';
      }
    }
  
    // Add change event listener to the checkbox
    menuCheckbox.addEventListener('change', toggleAnimations);
  
    // Add animation end event listeners to both animated elements
    leftAnimation.addEventListener('animationend', handleAnimationEnd);
    rightAnimation.addEventListener('animationend', handleAnimationEnd);
  });