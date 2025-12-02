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
document.addEventListener('DOMContentLoaded', function() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return; // Don't run if navbar doesn't exist

  let lastScrollTop = 0;

  window.addEventListener("scroll", function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;

    if (currentScroll > lastScrollTop && currentScroll > 60) {
      // Scroll Down
      navbar.style.top = "-60px";
    } else {
      // Scroll Up
      navbar.style.top = "0px";
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
  }, false);
});

/* Menu Animation */
document.addEventListener('DOMContentLoaded', function() {
  var menuCheckbox = document.querySelector('.hamburger input[type="checkbox"]');
  var leftAnimation = document.getElementById('left-animation');
  var rightAnimation = document.getElementById('right-animation');
  var menuListItems = leftAnimation.querySelectorAll('.menu-list li');

  function resetMenuItems() {
    // Reset opacity and animation state for all menu items
    menuListItems.forEach(function(item) {
      item.style.opacity = '0';
      item.style.animation = 'none';
      // Force reflow to reset animation
      void item.offsetWidth;
    });
  }

  function animateMenuItems() {
    // Re-trigger the floatUpLinks animation for menu items
    // First, reset all items to initial state
    menuListItems.forEach(function(item) {
      item.style.animation = 'none';
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
    });
    
    // Force reflow to ensure reset is applied
    void leftAnimation.offsetWidth;
    
    // Then re-apply the animation with proper delays matching CSS
    menuListItems.forEach(function(item, index) {
      var delay = 200 + (index * 200); // 0.2s, 0.4s, 0.6s, 0.8s, 1.0s
      
      setTimeout(function() {
        // Force reflow before applying animation
        void item.offsetWidth;
        
        // Apply the animation with the correct delay
        item.style.animation = 'floatUpLinks 0.5s ease-out forwards';
        item.style.animationDelay = (delay / 1000) + 's';
      }, 10); // Small delay to ensure reflow happens
    });
  }

  function toggleAnimations() {
    // --- OPTIMIZATION 1: Remove old classes FIRST ---
    // This prevents animations from "fighting" if you click fast.
    leftAnimation.classList.remove('animate-in-left', 'animate-out-left');
    rightAnimation.classList.remove('animate-in-right', 'animate-out-right');

    if (menuCheckbox.checked) {
      // --- OPENING MENU ---
      leftAnimation.style.display = 'flex';
      rightAnimation.style.display = 'flex';
      
      // We force the browser to "see" the display change before animating
      // This is a simple trick to ensure the animation plays correctly
      requestAnimationFrame(() => {
        leftAnimation.classList.add('animate-in-left');
        rightAnimation.classList.add('animate-in-right');
        
        // Animate menu items after a short delay to ensure panel is visible
        setTimeout(animateMenuItems, 50);
      });

    } else {
      // --- CLOSING MENU ---
      // Hide menu items immediately when closing
      menuListItems.forEach(function(item) {
        item.style.opacity = '0';
      });
      
      leftAnimation.classList.add('animate-out-left');
      rightAnimation.classList.add('animate-out-right');
    }
  }

  function handleAnimationEnd(event) {
    const target = event.target;
    
    // --- OPTIMIZATION 2: Smarter logic ---
    // Only hide the element AFTER its "out" animation finishes.
    if (!menuCheckbox.checked) {
      target.style.display = 'none';
      target.classList.remove('animate-out-left', 'animate-out-right');
      // Reset menu items when menu is fully closed
      resetMenuItems();
    } else {
      // Just clean up "in" animations
      target.classList.remove('animate-in-left', 'animate-in-right');
    }
  }

  menuCheckbox.addEventListener('change', toggleAnimations);

  leftAnimation.addEventListener('animationend', handleAnimationEnd);
  rightAnimation.addEventListener('animationend', handleAnimationEnd);
});

/*
  ==============================================
  MUSIC PLAYLIST & VIDEO SCROLL INTERACTION
  ==============================================
*/
document.addEventListener('DOMContentLoaded', function() {

  const backgroundMusic = document.getElementById('bg-music');
  const videoPlayer = document.querySelector('.video');
  
  const songs = [
    './music/Greeny.mp3', // Song 1
    './music/Popcorn.mp3' // Song 2
  ];

  let currentSongIndex = 0;
  
  // Make sure music and video are loaded before we do anything
  if (!backgroundMusic || !videoPlayer) {
    console.error("Music player or video not found!");
    return;
  }

  // --- 2. Background Music Playlist Logic ---
  backgroundMusic.addEventListener('ended', function() {
    // Move to the next song
    currentSongIndex = (currentSongIndex + 1) % songs.length;
    
    // Update the audio source and play
    backgroundMusic.src = songs[currentSongIndex];
    backgroundMusic.play();
  });

  // --- 3. Scroll Interaction Logic (Intersection Observer) ---
  const observerOptions = {
    root: null,
    threshold: 0.5
  };

  const videoObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        backgroundMusic.pause();
        videoPlayer.play();
      } else {
        videoPlayer.pause();
        if (backgroundMusic.paused) {
          backgroundMusic.play();
        }
      }
    });
  }, observerOptions);
  videoObserver.observe(videoPlayer);
});