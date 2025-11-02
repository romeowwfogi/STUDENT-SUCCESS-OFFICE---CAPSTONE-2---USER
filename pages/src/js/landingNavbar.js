// ======================= NAVBAR FUNCTIONALITY =======================

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Hamburger menu functionality
    const hamburger = document.querySelector('.hamburger_icon');
    const navMenu = document.querySelector('.hamburger_nav');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }

    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // No JS-driven hover or page-load animations to keep UI stable
});