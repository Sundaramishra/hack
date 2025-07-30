document.addEventListener('DOMContentLoaded', function() {
    // Premium counter animation
    function animateCounter(element, target, duration = 2000) {
        if (!element) return;
        
        const start = 0;
        const startTime = performance.now();
        
        function updateCounter(currentTime) {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            
            // Use easing function for smooth animation
            const easedProgress = 1 - Math.pow(1 - progress, 3); // Cubic ease-out
            
            const currentCount = Math.floor(easedProgress * (target - start) + start);
            element.textContent = currentCount + '+';
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }
        
        requestAnimationFrame(updateCounter);
    }
    
    // Enhanced counter animation for all counter elements
    function handleCounters() {
        // Handle ID-based counters (legacy support)
        const idCounterElements = [
            { id: 'counter-projects', target: 25 },
            { id: 'counter-clients', target: 15 },
            { id: 'counter-years', target: 4 }
        ];
        
        idCounterElements.forEach(counter => {
            const element = document.getElementById(counter.id);
            if (element && isElementInViewport(element) && !element.getAttribute('data-counted')) {
                animateCounter(element, counter.target);
                element.setAttribute('data-counted', 'true');
            }
        });
        
        // Handle class-based counters with data-target attribute (premium elements)
        const classCounters = document.querySelectorAll('.counter');
        classCounters.forEach(counter => {
            if (isElementInViewport(counter) && !counter.getAttribute('data-counted')) {
                const target = parseInt(counter.getAttribute('data-target'), 10) || 0;
                animateCounter(counter, target);
                counter.setAttribute('data-counted', 'true');
            }
        });
    }
    
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    window.addEventListener('scroll', handleCounters);
    // Initial check
    setTimeout(handleCounters, 500);
    
    // Enhanced Parallax effect
    const parallaxElements = document.querySelectorAll('[data-parallax]');
    
    function handleParallax() {
        const scrollY = window.scrollY;
        
        parallaxElements.forEach(element => {
            const speed = element.getAttribute('data-parallax') || 0.2;
            const offsetY = scrollY * speed;
            element.style.transform = `translateY(${offsetY}px)`;
        });
    }
    
    if (parallaxElements.length > 0) {
        window.addEventListener('scroll', handleParallax);
        handleParallax(); // Initial call
    }
    
    // Video play button functionality
    const playButtons = document.querySelectorAll('.play-btn');
    playButtons.forEach(button => {
        button.addEventListener('click', function() {
            const videoContainer = this.closest('.video-container');
            const video = videoContainer.querySelector('video');
            
            if (video) {
                if (video.paused) {
                    video.play();
                    this.innerHTML = '<i class="fas fa-pause text-xl"></i>';
                    this.classList.add('playing');
                } else {
                    video.pause();
                    this.innerHTML = '<i class="fas fa-play text-xl"></i>';
                    this.classList.remove('playing');
                }
            }
        });
    });
    
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            // Toggle the menu visibility
            mobileMenu.classList.toggle('hidden');
            
            // Toggle the hamburger/close icon
            const hamburgerIcon = mobileMenuButton.querySelector('svg:first-child');
            const closeIcon = mobileMenuButton.querySelector('svg:last-child');
            
            hamburgerIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });
    }
    
    // Enhanced background movement with parallax
   
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href !== '#') {
                e.preventDefault();
                
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Fade in elements on scroll
    const fadeElements = document.querySelectorAll('.fade-in-element');
    
    function checkFade() {
        const triggerBottom = window.innerHeight * 0.8;
        
        fadeElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            
            if (elementTop < triggerBottom) {
                element.classList.add('visible');
            }
        });
    }
    
    if (fadeElements.length > 0) {
        window.addEventListener('scroll', checkFade);
        checkFade(); // Check on initial load
    }
    
    // Portfolio item hover effect
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    portfolioItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.querySelector('.portfolio-overlay').style.opacity = '1';
        });
        
        item.addEventListener('mouseleave', function() {
            this.querySelector('.portfolio-overlay').style.opacity = '0';
        });
    });
    
    // Contact form validation
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const messageInput = document.getElementById('message');
            
            // Reset errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            
            // Validate name
            if (!nameInput.value.trim()) {
                isValid = false;
                showError(nameInput, 'Name is required');
            }
            
            // Validate email
            if (!emailInput.value.trim()) {
                isValid = false;
                showError(emailInput, 'Email is required');
            } else if (!isValidEmail(emailInput.value)) {
                isValid = false;
                showError(emailInput, 'Please enter a valid email address');
            }
            
            // Validate message
            if (!messageInput.value.trim()) {
                isValid = false;
                showError(messageInput, 'Message is required');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    function showError(input, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-red-500 text-sm mt-1';
        errorDiv.innerText = message;
        
        input.classList.add('border-red-500');
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }
    
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Alert auto-dismiss
    const alerts = document.querySelectorAll('[role="alert"]');
    
    alerts.forEach(alert => {
        const closeButton = alert.querySelector('svg');
        
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                alert.remove();
            });
        }
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.classList.add('opacity-0');
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    });
});
