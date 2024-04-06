
const stickySections = [...document.querySelectorAll('.sticky_wrap')]

window.addEventListener('scroll', (e) => {
  for(let i = 0; i < stickySections.length; i++){
    transform(stickySections[i])
  }
})

function transform(section) {

  const offsetTop = section.parentElement.offsetTop;

  const scrollSection = section.querySelector('.horizontal_scroll')

  let percentage = ((window.scrollY - offsetTop) / window.innerHeight) * 100;

  percentage = percentage < 0 ? 0 : percentage > 300 ? 300 : percentage;

  scrollSection.style.transform = `translate3d(${-(percentage)}vw, 0, 0)`
}



/* 
ScrollTrigger.create({
  animation:gsap.from(".logo", {
    y:"50vh",
    scale:4,
    yPercent:-50
  }),
  scrub:true,
  trigger: ".container-2",
  start: "top bottom",
  endTrigger: '.container-2',
  end: 'top center',
  markers: true,
  pin: true,
  pinSpacing: false
}); */

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
// Get the navbar
var navbar = document.querySelector('.navbar');

// Get the current position of the scroll
var lastScrollTop = 0;

window.addEventListener("scroll", function() {
   var currentScroll = window.pageYOffset || document.documentElement.scrollTop;

   if (currentScroll > lastScrollTop) {
       // Scroll Down
       navbar.style.top = "-60px"; // Adjust this value to the height of your navbar
   } else {
       // Scroll Up
       navbar.style.top = "0px";
   }

   lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
}, false);


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