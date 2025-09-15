<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Beauty Salon Services' ?></title>
     
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    
    @stack('styles')
  
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Variables */
        :root {
            --primary-black: #000000;
            --soft-black: #333333;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --pure-white: #ffffff;
            --border-color: #e0e0e0;
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Basic styling for the layout */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--primary-black);
            background-color: var(--light-gray);
            overflow-x: hidden;
        }

        html {
            scroll-behavior: smooth;
        }
        
        /* Enhanced Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-color);
            z-index: 2000; 
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-light);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-black);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
        }

        .logo:hover {
            transform: scale(1.02);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
        }
        
        .nav-links a {
            color: var(--primary-black);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: var(--transition);
            padding: 0.5rem 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-black);
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            transform: translateY(-1px);
        }
        
        /* Mobile menu styles */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--primary-black);
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-menu-toggle:hover {
            transform: scale(1.1);
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 100px auto 2rem;
            min-height: calc(100vh - 200px);
        }
        
        /* Enhanced Footer */
        .footer {
            background: var(--primary-black);
            color: var(--pure-white);
            padding: 4rem 0 2rem;
            text-align: center;
            margin-top: 4rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.02), transparent);
            opacity: 0;
            transition: var(--transition);
        }

        .footer:hover::before {
            opacity: 1;
        }

        .footer .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            text-align: center;
        }

        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--pure-white);
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2.5rem;
            line-height: 1.8;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-size: 1.05rem;
        }

        .social-links {
            display: flex;
            gap: 1.2rem;
            justify-content: center;
            margin-bottom: 2.5rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--pure-white);
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .social-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--pure-white);
            transition: var(--transition);
        }

        .social-links a:hover::before {
            left: 0;
        }

        .social-links a:hover {
            color: var(--primary-black);
            transform: translateY(-3px);
        }

        .social-links a i {
            position: relative;
            z-index: 2;
            font-size: 1.2rem;
        }

        .copyright {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
        }

        /* Contact Info in Footer */
        .footer-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-info-item {
            text-align: center;
        }

        .footer-info-item h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--pure-white);
        }

        .footer-info-item p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .back-link {
            color: var(--primary-black);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: var(--transition);
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background: var(--pure-white);
        }

        .back-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-black);
            transition: var(--transition);
        }

        .back-link:hover::after {
            width: 100%;
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(15px);
                flex-direction: column;
                gap: 0;
                padding: 2rem;
                border-top: 1px solid var(--border-color);
                box-shadow: var(--shadow-light);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .navbar {
                position: relative;
            }
            
            .nav-links a {
                padding: 1rem 0;
                border-bottom: 1px solid var(--border-color);
                width: 100%;
                text-align: center;
            }

            .nav-links a:last-child {
                border-bottom: none;
            }

            .main-content {
                margin-top: 120px;
                padding: 1rem;
            }

            .nav-container {
                padding: 1rem 1.5rem;
            }

            .logo {
                font-size: 1.5rem;
            }

            .footer-info {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .social-links {
                gap: 1rem;
            }

            .footer .container {
                padding: 0 1rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem;
            }

            .footer {
                padding: 3rem 0 1.5rem;
            }

            .footer-logo {
                font-size: 1.8rem;
            }

            .footer-text {
                font-size: 0.95rem;
            }

            .social-links a {
                width: 45px;
                height: 45px;
            }

            .social-links a i {
                font-size: 1rem;
            }
        }

        /* Additional utility classes */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Scroll animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(40px);
            transition: var(--transition);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .container-footer{
            background-color: black;
        }

        /* ====== ONLY NEW LINES: profile dropdown fix ====== */
        .profile-menu { position: relative; }
        .profile-toggle { display: inline-flex; align-items: center; gap: .5rem; background: none; border: 0; cursor: pointer; }
        .profile-dropdown{
          position: absolute; right: 0; top: calc(100% + 8px);
          min-width: 180px; background:#fff; border:1px solid var(--border-color);
          border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,.12);
          padding: .5rem 0; display:none; z-index:3000;
        }
        .profile-menu.open .profile-dropdown{ display:block; }
        .profile-dropdown a, .profile-dropdown button{
          display:block; width:100%; text-align:left; padding:.65rem .9rem;
          background:none; border:0; color:var(--soft-black); text-decoration:none; cursor:pointer;
        }
        .profile-dropdown a:hover, .profile-dropdown button:hover{ background:#f3f4f6; }
        /* ================================================ */
        
    </style>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
</head>
<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="logo">🌟 Salon Good</a>
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
            <ul class="nav-links" id="navLinks">
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="{{ route('services.index') }}">All Services</a></li>
                <li><a href="{{ route('stylist.index') }}">Stylists</a></li>

                {{-- 👇 Auth links --}}
                @auth
                    {{-- Profile dropdown --}}
                    <li class="profile-menu">
                        <button type="button" class="profile-toggle" id="profileToggle">
                            <img
                                src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}"
                                alt="Profile"
                                class="avatar"
                            />
                            <span class="profile-name">{{ Auth::user()->name }}</span>
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </button>

                        <ul class="profile-dropdown" id="profileDropdown">
                            <li><a href="{{ route('profile.show') }}">My Profile</a></li>
                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <li><a href="{{ route('api-tokens.index') }}">API Tokens</a></li>
                            @endif
                            @auth
                            <li><a href="{{ route('bookings.index') }}">My Bookings</a></li>
                            <li><a href="{{ route('payments.history') }}">Payment History</a></li>
                             <a href="{{ route('refunds.refund') }}" class="nav-link">
                             <i class="fas fa-undo"></i>
                                     My Refunds
                                </a>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="logout-link">Logout</button>
                                </form>
                            </li>
                            @endauth
                        </ul>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">Sign Up</a></li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      @yield('content')
    </main>

    <!-- Enhanced Footer -->
    <footer class="footer">
        <div class="container-footer">
            <div class="footer-content">
                <div class="footer-logo">Salon Good</div>
                <p class="footer-text">Premium beauty services in a luxurious setting. Our expert stylists are dedicated to helping you look and feel your best with personalized treatments and exceptional care.</p>
                
                <!-- Contact Information -->
                <div class="footer-info">
                    <div class="footer-info-item">
                        <h4><i class="fas fa-map-marker-alt"></i> Location</h4>
                        <p>123 Beauty Street<br>Downtown City, DC 12345</p>
                    </div>
                    <div class="footer-info-item">
                        <h4><i class="fas fa-phone"></i> Contact</h4>
                        <p>(555) 123-4567<br>info@salongood.com</p>
                    </div>
                    <div class="footer-info-item">
                        <h4><i class="fas fa-clock"></i> Hours</h4>
                        <p>Mon-Fri: 9AM-8PM<br>Sat-Sun: 9AM-6PM</p>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="social-links">
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
                
                <p class="copyright">© <?= date('Y') ?> Salon Good. All rights reserved. | Designed with care for beauty professionals.</p>
            </div>
        </div>
    </footer>
    
    <!-- Enhanced Scripts -->
    <script>
        // Mobile menu toggle with enhanced functionality
        function toggleMobileMenu() {
            const navLinks = document.getElementById('navLinks');
            const toggleButton = document.querySelector('.mobile-menu-toggle');
            
            navLinks.classList.toggle('active');
            
            // Update toggle button icon
            if (navLinks.classList.contains('active')) {
                toggleButton.innerHTML = '✕';
            } else {
                toggleButton.innerHTML = '☰';
            }
        }
        
        // Enhanced navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navbar = document.querySelector('.navbar');
            const navLinks = document.getElementById('navLinks');
            const toggleButton = document.querySelector('.mobile-menu-toggle');
            
            if (!navbar.contains(event.target) && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                toggleButton.innerHTML = '☰';
            }
        });
        
        // Close mobile menu when window is resized to desktop
        window.addEventListener('resize', function() {
            const navLinks = document.getElementById('navLinks');
            const toggleButton = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth > 768) {
                navLinks.classList.remove('active');
                toggleButton.innerHTML = '☰';
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 100;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    const navLinks = document.getElementById('navLinks');
                    const toggleButton = document.querySelector('.mobile-menu-toggle');
                    navLinks.classList.remove('active');
                    toggleButton.innerHTML = '☰';
                }
            });
        });

        // Scroll animations with enhanced performance
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 100); // Staggered animation
                }
            });
        }, observerOptions);

        // Observe all elements with animation class when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });

        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const navLinks = document.getElementById('navLinks');
                const toggleButton = document.querySelector('.mobile-menu-toggle');
                navLinks.classList.remove('active');
                toggleButton.innerHTML = '☰';
            }
        });

        // Enhanced button interactions
        document.querySelectorAll('.back-link, .social-links a').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Footer animation effects
        const footer = document.querySelector('.footer');
        if (footer) {
            footer.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
            });
            
            footer.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        }

        // Performance optimization: debounce scroll events
        let ticking = false;
        
        function updateOnScroll() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateOnScroll);
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestTick);

        /* ====== ONLY NEW LINES: profile dropdown behavior ====== */
        (function () {
          const menu  = document.querySelector('.profile-menu');
          const btn   = document.getElementById('profileToggle');
          if (!menu || !btn) return;

          btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('open');
          });

          document.addEventListener('click', (e) => {
            if (!menu.contains(e.target)) menu.classList.remove('open');
          });

          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') menu.classList.remove('open');
          });
        })();
        /* ======================================================= */
    </script>
    <script src="{{ asset('js/main.js') }}"></script>

</body>
