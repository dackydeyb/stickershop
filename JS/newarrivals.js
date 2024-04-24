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
    // opening animations and set display to flex
    leftAnimation.classList.add('animate-in-left');
    rightAnimation.classList.add('animate-in-right');
    leftAnimation.style.display = 'flex';
    rightAnimation.style.display = 'flex';
  } else {
    // closing animations
    leftAnimation.classList.add('animate-out-left');
    rightAnimation.classList.add('animate-out-right');
  }
}

function handleAnimationEnd(event) {
  // Remove animation classes to reset
  event.target.classList.remove('animate-in-left', 'animate-out-left', 'animate-in-right', 'animate-out-right');

  // Hide element if it's the closing animation
  if (!menuCheckbox.checked) {
    event.target.style.display = 'none';
  }
}


menuCheckbox.addEventListener('change', toggleAnimations);

leftAnimation.addEventListener('animationend', handleAnimationEnd);
rightAnimation.addEventListener('animationend', handleAnimationEnd);
});

function sortCards(ascending) {
// Select the container that holds all the cards
var container = document.querySelector('.main-right-content'); // Adjust if necessary
var cards = Array.from(container.querySelectorAll('.card'));

// Sort the cards by price
cards.sort(function(a, b) {
  var priceA = parseFloat(a.querySelector('.card-price').textContent.replace(/[^\d.]/g, ''));
  var priceB = parseFloat(b.querySelector('.card-price').textContent.replace(/[^\d.]/g, ''));
  return ascending ? priceA - priceB : priceB - priceA;
});

// Clear the container and append the cards in the new order
container.innerHTML = '';
cards.forEach(function(card) {
  container.appendChild(card);
});
}

// Event listeners for the sort radio buttons
document.getElementById('price-low-high').addEventListener('change', function() {
if(this.checked) {
  sortCards(true); // Sort low to high
}
});

document.getElementById('price-high-low').addEventListener('change', function() {
if(this.checked) {
  sortCards(false); // Sort high to low
}
});


document.addEventListener('DOMContentLoaded', function() {
// Get the search bar element
var searchBar = document.getElementById('search-bar');

// Event listener for the search bar input
searchBar.addEventListener('input', function() {
  filterCardsByName(this.value.toLowerCase()); // Convert to lower case for case-insensitive matching
});

// Call this function on page load to ensure correct state
filterCardsByName(searchBar.value.toLowerCase());
});

function filterCardsByName(searchText) {
var cards = document.querySelectorAll('.card');

cards.forEach(function(card) {
  var cardTitle = card.querySelector('.card-title').textContent.toLowerCase(); // Adjust selector if necessary
  if (cardTitle.startsWith(searchText) || searchText === '') {
    card.style.display = ''; // Show the card
  } else {
    card.style.display = 'none'; // Hide the card
  }
});
}