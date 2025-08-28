<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Categories - Salon Good</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--primary-black);
            background-color: var(--pure-white);
            overflow-x: hidden;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-color);
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-light);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-black);
            text-decoration: none;
            transition: var(--transition);
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

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--primary-black);
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

    

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 6rem;
            position: relative;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeInUp 1s ease-out;
        }

        .header p {
            font-size: 1.3rem;
            color: var(--soft-black);
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        /* Section Title */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            color: var(--primary-black);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background-color: var(--primary-black);
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2.5rem;
            margin-top: 4rem;
        }

        .category-card {
            background: var(--pure-white);
            border: 1px solid var(--border-color);
            padding: 3rem 2.5rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.03), transparent);
            transition: var(--transition);
        }

        .category-card:hover::before {
            left: 100%;
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-black);
        }

        .category-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary-black);
        }

        .category-card p {
            color: var(--soft-black);
            margin-bottom: 2.5rem;
            line-height: 1.7;
            font-size: 1.05rem;
        }

        .category-card a {
            color: var(--primary-black);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            padding: 1rem 2rem;
            border: 2px solid var(--primary-black);
            display: inline-block;
        }

        .category-card a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-black);
            transition: var(--transition);
            z-index: 1;
        }

        .category-card a span {
            position: relative;
            z-index: 2;
        }

        .category-card a:hover::before {
            left: 0;
        }

        .category-card a:hover {
            color: var(--pure-white);
            transform: translateY(-2px);
        }

        /* Quick Actions Section */
        .quick-actions {
            text-align: center;
            margin-top: 6rem;
            padding: 4rem 3rem;
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .quick-actions::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(0,0,0,0.02), transparent);
            opacity: 0;
            transition: var(--transition);
        }

        .quick-actions:hover::before {
            opacity: 1;
        }

        .quick-actions h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary-black);
        }

        .quick-actions p {
            color: var(--soft-black);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 1.2rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 0;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid var(--primary-black);
            background: var(--pure-white);
            color: var(--primary-black);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-black);
            transition: var(--transition);
            z-index: 1;
        }

        .btn span {
            position: relative;
            z-index: 2;
        }

        .btn:hover::before {
            left: 0;
        }

        .btn:hover {
            color: var(--pure-white);
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background: var(--primary-black);
            color: var(--pure-white);
        }

        .btn-secondary::before {
            background: var(--pure-white);
        }

        .btn-secondary:hover {
            color: var(--primary-black);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
            background: var(--light-gray);
            border: 1px solid var(--border-color);
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-black);
        }

        .empty-state p {
            color: var(--soft-black);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(40px);
            transition: var(--transition);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .header h1 {
                font-size: 2.8rem;
            }
            
            .section-title {
                font-size: 2.4rem;
            }

            .container {
                padding: 0 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .header h1 {
                font-size: 2.2rem;
            }

            .categories-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 320px;
                justify-content: center;
            }

          

            .container {
                padding: 0 1rem;
            }

            .category-card, .quick-actions {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .header p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .btn {
                padding: 1rem 2rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>


    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Header Section -->
            <div class="header">
                <h1>Our Premium Services</h1>
                <p>Discover our range of professional beauty and wellness services designed to enhance your natural radiance</p>
            </div>

            <!-- Service Categories Section -->
            <div class="categories-section">
                <h2 class="section-title animate-on-scroll">Service Categories</h2>
                <div class="categories-grid">
                    <!-- Hair Services Category -->
                    <div class="category-card animate-on-scroll">
                        <h3>Hair Services</h3>
                        <p>From precision cuts to vibrant color transformations, our hair experts create looks that complement your personal style and enhance your natural beauty.</p>
                        <a href="#"><span>View Services</span></a>
                    </div>
                    
                    <!-- Nail Care Category -->
                    <div class="category-card animate-on-scroll">
                        <h3>Nail Care</h3>
                        <p>Indulge in our luxurious manicures and pedicures using premium products for healthy, beautiful nails that make a lasting impression.</p>
                        <a href="#"><span>View Services</span></a>
                    </div>
                    
                    <!-- Skin Treatments Category -->
                    <div class="category-card animate-on-scroll">
                        <h3>Skin Treatments</h3>
                        <p>Revitalize your skin with our advanced facials and treatments designed specifically for your unique skin type and concerns.</p>
                        <a href="#"><span>View Services</span></a>
                    </div>
                    
                    <!-- Spa Services Category -->
                    <div class="category-card animate-on-scroll">
                        <h3>Spa Services</h3>
                        <p>Escape and unwind with our full-body treatments, massages, and wellness services in a tranquil, rejuvenating environment.</p>
                        <a href="#"><span>View Services</span></a>
                    </div>
                    
                    <!-- Bridal Packages Category -->
                    <div class="category-card animate-on-scroll">
                        <h3>Bridal Packages</h3>
                        <p>Complete bridal beauty packages including hair, makeup, and skincare treatments to make your special day absolutely perfect.</p>
                        <a href="#"><span>View Services</span></a>
                    </div>
                    
                    <!-- Special Treatments Category -->
                    <div class="category-card animate-on-scroll">
                        <h3>Special Treatments</h3>
                        <p>Exclusive premium treatments and seasonal services featuring the latest in beauty technology and luxury experiences.</p>
                        <a href="#"><span>View Services</span></a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions animate-on-scroll">
                <h3>Not sure what you need?</h3>
                <p>Browse all our services or get in touch with our beauty experts for personalized recommendations</p>
                <div class="action-buttons">
                    <a href="list.php" class="btn">
                        <span><i class="fas fa-search"></i> Browse All Services</span>
                    </a>
                    <a href="#contact" class="btn btn-secondary">
                        <span><i class="fas fa-phone-alt"></i> Contact Us</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 100);
                }
            });
        }, observerOptions);

        // Observe all elements with animation class
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });

        // Enhanced card interactions
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
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
                }
            });
        });
    </script>
</body>
</html>