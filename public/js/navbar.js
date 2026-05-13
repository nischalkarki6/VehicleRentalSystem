document.addEventListener('DOMContentLoaded', () => {
  const navHamburger = document.getElementById('navHamburger');
  const navMenu = document.getElementById('navMenu');
  const body = document.body;

  if (navHamburger && navMenu) {
    navHamburger.addEventListener('click', (e) => {
      e.stopPropagation();
      navMenu.classList.toggle('active');
      
      // Change icon based on state
      const icon = navHamburger.querySelector('.material-symbols-outlined');
      if (navMenu.classList.contains('active')) {
        icon.textContent = 'close';
        body.style.overflow = 'hidden'; // Prevent scroll when menu is open
      } else {
        icon.textContent = 'menu';
        body.style.overflow = '';
      }
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (navMenu.classList.contains('active') && !navMenu.contains(e.target) && !navHamburger.contains(e.target)) {
        navMenu.classList.remove('active');
        navHamburger.querySelector('.material-symbols-outlined').textContent = 'menu';
        body.style.overflow = '';
      }
    });

    // Close menu when clicking on a link
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        navHamburger.querySelector('.material-symbols-outlined').textContent = 'menu';
        body.style.overflow = '';
      });
    });
  }
});
