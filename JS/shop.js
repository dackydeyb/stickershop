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
  
  /* Navigation Bar Hide On Scroll */
  var navbar = document.querySelector('.navbar');
  
  var lastScrollTop = 0;
  
  window.addEventListener("scroll", function() {
     var currentScroll = window.pageYOffset || document.documentElement.scrollTop;
  
     if (currentScroll > lastScrollTop) {
         navbar.style.top = "-60px"; 
     } else {
         navbar.style.top = "0px";
     }
  
     lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
  }, false);

  document.addEventListener('DOMContentLoaded', function () {
  var menuCheckbox = document.querySelector('.hamburger input[type="checkbox"]');
  var menu = document.querySelector('.menu');

  menuCheckbox.addEventListener('change', function () {
    if (menuCheckbox.checked) {
      menu.classList.add('active');
    } else {
      menu.classList.remove('active');
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

