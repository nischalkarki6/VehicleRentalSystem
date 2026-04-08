document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Navbar
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
            navbar.style.padding = '10px 0';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = '0 2px 15px rgba(0,0,0,0.05)';
            navbar.style.padding = '15px 0';
        }
    });

    // 2. Mobile Menu Toggle (Basic implementation)
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links ul');

    // Simple toggle logic since we hid it with display:none in css for brevity
    // In a real app we'd use max-height or transform, but we mock it here.
    if(mobileBtn && navLinks) {
        mobileBtn.addEventListener('click', () => {
            const isHidden = window.getComputedStyle(document.querySelector('.nav-links')).display === 'none';
            if(isHidden) {
                alert("Mobile menu opened! (Implement a full slide-out menu here)");
            }
        });
    }

    // 3. Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if(target) {
                // Adjusting for fixed header offset
                const headerOffset = 70;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
  
                window.scrollTo({
                     top: offsetPosition,
                     behavior: "smooth"
                });
                
                // Update active class
                document.querySelectorAll('.nav-links a').forEach(link => link.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // 4. Booking Form Submission Intercept
    const bookingForm = document.getElementById('booking-form');
    if(bookingForm) {
        bookingForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const btn = bookingForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Searching...';
            btn.disabled = true;
            
            // Mock API Call delay
            setTimeout(() => {
                btn.innerHTML = 'Vehicles Found! <i class="fa-solid fa-check"></i>';
                btn.style.background = '#2ecc71';
                btn.style.color = 'white';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.disabled = false;
                    // Scroll to fleet
                    document.getElementById('fleet').scrollIntoView({ behavior: 'smooth' });
                }, 1500);
            }, 1000);
        });
    }
});
