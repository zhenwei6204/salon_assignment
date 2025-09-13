
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Good - Premium Beauty Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 
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

        .mobile-menu-toggle:hover {
            transform: scale(1.1);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: var(--pure-white);
            max-width: 900px;
            padding: 0 2rem;
            animation: fadeInUp 1.2s ease-out;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            animation: fadeInUp 1.2s ease-out 0.2s both;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.4rem;
            font-weight: 300;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            animation: fadeInUp 1.2s ease-out 0.4s both;
            line-height: 1.7;
        }

        .hero-cta {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            animation: fadeInUp 1.2s ease-out 0.6s both;
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
            border: 2px solid;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--pure-white);
            transition: var(--transition);
            z-index: 1;
        }

        .btn span {
            position: relative;
            z-index: 2;
        }

        .btn-primary {
            background-color: var(--pure-white);
            color: var(--primary-black);
            border-color: var(--pure-white);
        }

        .btn-primary:hover::before {
            left: 0;
        }

        .btn-primary:hover {
            color: var(--primary-black);
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--pure-white);
            border-color: var(--pure-white);
        }

        .btn-secondary::before {
            background: var(--pure-white);
        }

        .btn-secondary:hover::before {
            left: 0;
        }

        .btn-secondary:hover {
            color: var(--primary-black);
            transform: translateY(-3px);
        }

        /* Sections */
        section {
            padding: 6rem 0;
            position: relative;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.2rem;
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
            transition: var(--transition);
        }

        /* Grid Layouts */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2.5rem;
            margin-top: 4rem;
        }

        /* Cards */
        .card {
            background: var(--pure-white);
            border: 1px solid var(--border-color);
            padding: 3rem 2.5rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.03), transparent);
            transition: var(--transition);
        }

        .card:hover::before {
            left: 100%;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-black);
        }

        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 2rem;
            color: var(--primary-black);
            transition: var(--transition);
        }

        .card:hover .card-icon {
            transform: scale(1.1);
        }

        .card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            color: var(--primary-black);
        }

        .card p {
            color: var(--soft-black);
            margin-bottom: 2rem;
            line-height: 1.7;
            font-size: 1.05rem;
        }

        .card-link {
            color: var(--primary-black);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .card-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-black);
            transition: var(--transition);
        }

        .card-link:hover::after {
            width: 100%;
        }

        .card-link:hover {
            transform: translateY(-2px);
        }

        /* Stylist Cards */
        .stylist-card {
            background: var(--pure-white);
            border: 1px solid var(--border-color);
            padding: 3rem 2.5rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stylist-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-black);
        }

        .stylist-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 2rem;
            overflow: hidden;
            border: 3px solid var(--border-color);
            transition: var(--transition);
            position: relative;
        }

        .stylist-card:hover .stylist-avatar {
            transform: scale(1.05);
            border-color: var(--primary-black);
        }

        .stylist-avatar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.1);
            opacity: 0;
            transition: var(--transition);
        }

        .stylist-card:hover .stylist-avatar::before {
            opacity: 1;
        }

        .stylist-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .stylist-card:hover .stylist-avatar img {
            transform: scale(1.1);
        }

        .stylist-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 0.5rem;
        }

        .stylist-title {
            color: var(--soft-black);
            font-style: italic;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .stylist-specializations {
            color: var(--primary-black);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .stylist-rating {
            color: var(--primary-black);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.8rem;
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .stylist-card:hover .stylist-rating {
            background-color: var(--primary-black);
            color: var(--pure-white);
        }

        /* Service Cards */
        .service-card {
            background: var(--pure-white);
            border: 1px solid var(--border-color);
            padding: 3rem 2.5rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-black);
        }

        .service-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary-black);
        }

        .service-price {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-black);
            margin-bottom: 0.8rem;
        }

        .service-duration {
            color: var(--soft-black);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1rem;
        }
        
        .container-stat {
            background-color: black;
        }

        /* Stats Section */
        .stats-section {
            background: var(--primary-black) !important;
            color: var(--pure-white);
            text-align: center;
        }

        .stats-section .section-title {
            color: var(--pure-white);
        }

        .stats-section .section-title::after {
            background-color: var(--pure-white);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2.5rem;
            margin-top: 4rem;
        }

        .stat-item {
            padding: 2.5rem 2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .stat-item:hover::before {
            left: 100%;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            display: block;
            margin-bottom: 0.8rem;
            position: relative;
            z-index: 2;
        }

        .stat-label {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
            position: relative;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CTA Section */
        .cta-section {
            background: var(--light-gray);
            text-align: center;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .cta-text {
            font-size: 1.3rem;
            color: var(--soft-black);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-section .btn {
            background: var(--pure-white);
            color: var(--primary-black);
            border-color: var(--primary-black);
        }

        .cta-section .btn-secondary {
            background: var(--primary-black);
            color: var(--pure-white);
            border-color: var(--primary-black);
        }

        .cta-section .btn-secondary::before {
            background: var(--pure-white);
        }

        .cta-section .btn-secondary:hover {
            color: var(--primary-black);
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
        }

        .copyright {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
            .hero-title {
                font-size: 3.5rem;
            }
            
            .section-title {
                font-size: 2.8rem;
            }

            .container {
                padding: 0 1.5rem;
            }
        }

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
                padding: 2rem;
                border-top: 1px solid var(--border-color);
                gap: 1.5rem;
            }

            .nav-links.active {
                display: flex;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .hero-title {
                font-size: 2.8rem;
            }

            .hero-cta {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 320px;
                justify-content: center;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            section {
                padding: 4rem 0;
            }

            .container {
                padding: 0 1rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .card, .stylist-card, .service-card {
                padding: 2rem 1.5rem;
            }

            .nav-container {
                padding: 1rem 1.5rem;
            }

            .btn {
                padding: 1rem 2rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
@extends('layout.app')

@section('title', 'Home Page')

@section('content')
<body>

    <!-- Hero Section -->   
    <section class="hero-section" id="home">
        <video class="hero-video" autoplay muted loop playsinline>
    <source src="{{ asset('videos/background_video.mp4') }}" type="video/mp4">
    Your browser does not support the video tag.
</video>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Experience Premium Beauty Care</h1>
            <p class="hero-subtitle">Indulge in luxury treatments tailored to your unique beauty needs. Our expert stylists provide personalized services in a serene, elegant environment.</p>
            <div class="hero-cta">
                <a href="#services" class="btn btn-primary">
                    <span><i class="fas fa-calendar-check"></i> Book Appointment</span>
                </a>
                <a href="#about" class="btn btn-secondary">
                    <span><i class="fas fa-spa"></i> Learn More</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Our Signature Services</h2>
            <div class="grid">
                <div class="card animate-on-scroll">
                    <div class="card-icon">
                        <i class="fas fa-cut"></i>
                    </div>
                    <h3>Hair Services</h3>
                    <p>From precision cuts to vibrant color transformations, our hair experts create looks that complement your personal style and enhance your natural beauty.</p>
                    <a href="#" class="card-link">Explore Services</a>
                </div>
                
                <div class="card animate-on-scroll">
                    <div class="card-icon">
                        <i class="fas fa-hand-sparkles"></i>
                    </div>
                    <h3>Nail Care</h3>
                    <p>Indulge in our luxurious manicures and pedicures using premium products for healthy, beautiful nails that make a lasting impression.</p>
                    <a href="#" class="card-link">Explore Services</a>
                </div>
                
                <div class="card animate-on-scroll">
                    <div class="card-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3>Skin Treatments</h3>
                    <p>Revitalize your skin with our advanced facials and treatments designed specifically for your unique skin type and concerns.</p>
                    <a href="#" class="card-link">Explore Services</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stylists Section -->
    <section id="stylists">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Meet Our Expert Stylists</h2>
            <div class="grid">
                <div class="stylist-card animate-on-scroll">
                    <div class="stylist-avatar">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=400&q=80" alt="Sarah Johnson">
                    </div>
                    <h3 class="stylist-name">Sarah Johnson</h3>
                    <p class="stylist-title">Senior Hair Stylist</p>
                    <p class="stylist-specializations">Specializes in color correction, balayage, and precision cuts with over 8 years of experience</p>
                    <div class="stylist-rating">
                        <i class="fas fa-star"></i> 4.9/5 (245 reviews)
                    </div>
                </div>
                
                <div class="stylist-card animate-on-scroll">
                    <div class="stylist-avatar">
                        <img src="https://images.unsplash.com/photo-1595152772835-219674b2a8a6?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=400&q=80" alt="Michael Chen">
                    </div>
                    <h3 class="stylist-name">Michael Chen</h3>
                    <p class="stylist-title">Master Colorist</p>
                    <p class="stylist-specializations">Expert in creative color techniques and dimensional coloring with award-winning expertise</p>
                    <div class="stylist-rating">
                        <i class="fas fa-star"></i> 4.8/5 (189 reviews)
                    </div>
                </div>
                
                <div class="stylist-card animate-on-scroll">
                    <div class="stylist-avatar">
                        <img src="https://images.unsplash.com/photo-1551832264-40d09b8aed6e?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=400&q=80" alt="Emily Rodriguez">
                    </div>
                    <h3 class="stylist-name">Emily Rodriguez</h3>
                    <p class="stylist-title">Skin Care Specialist</p>
                    <p class="stylist-specializations">Advanced facial treatments and personalized skincare regimens with certified dermatology expertise</p>
                    <div class="stylist-rating">
                        <i class="fas fa-star"></i> 4.9/5 (312 reviews)
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Services -->
    <section>
        <div class="container">
            <h2 class="section-title animate-on-scroll">Featured Services</h2>
            <div class="grid">
                @foreach($services as $service)
                <div class="service-card animate-on-scroll">
                    <h3>{{ $service->name }}</h3>
                    <p class="service-price">${{ number_format($service->price, 2) }}</p>
                    <p class="service-duration"><i class="far fa-clock"></i> {{ $service->duration }} mins</p>
                    <!-- Correct route link for service details -->
                    <a href="{{ route('services.show', $service->id) }}" class="view-detail-btn">View Details</a>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container-stat">
            <h2 class="section-title animate-on-scroll">Our Achievements</h2>
            <div class="stats-grid">
                <div class="stat-item animate-on-scroll">
                    <span class="stat-number">1000+</span>
                    <span class="stat-label">Happy Clients</span>
                </div>
                <div class="stat-item animate-on-scroll">
                    <span class="stat-number">5+</span>
                    <span class="stat-label">Years Experience</span>
                </div>
                <div class="stat-item animate-on-scroll">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Services Offered</span>
                </div>
                <div class="stat-item animate-on-scroll">
                    <span class="stat-number">10+</span>
                    <span class="stat-label">Expert Stylists</span>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="contact">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Ready to Transform Your Look?</h2>
            <p class="cta-text animate-on-scroll">
                Book your appointment today and experience the difference our expert stylists can make. Your beauty journey starts here with personalized care and premium treatments.
            </p>
            <div class="cta-buttons animate-on-scroll">
                <a href="#" class="btn btn-primary">
                    <span><i class="fas fa-calendar-check"></i> Book Now</span>
                </a>
                <a href="#" class="btn btn-secondary">
                    <span><i class="fas fa-phone-alt"></i> Contact Us</span>
                </a>
            </div>
        </div>
    </section>


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
                    navLinks.classList.remove('active');
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

        // Observe all elements with animation class
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });

        // Enhanced button interactions
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Card hover effects enhancement
        document.querySelectorAll('.card, .stylist-card, .service-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Video optimization - pause when not visible
        const video = document.querySelector('.hero-video');
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    video.play();
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.5 });

        if (video) {
            videoObserver.observe(video);
        }

        // Parallax effect for hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.hero-video');
            const speed = scrolled * 0.5;
            
            if (parallax && scrolled < window.innerHeight) {
                parallax.style.transform = `translateY(${speed}px)`;
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navbar = document.querySelector('.navbar');
            const navLinks = document.getElementById('navLinks');
            const toggleButton = document.querySelector('.mobile-menu-toggle');
            
            if (!navbar.contains(event.target)) {
                navLinks.classList.remove('active');
            }
        });

        // Preload critical images
        const imageUrls = [
            'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=400&q=80',
            'https://images.unsplash.com/photo-1595152772835-219674b2a8a6?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=400&q=80',
            'https://images.unsplash.com/photo-1551832264-40d09b8aed6e?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=400&q=80'
        ];

        imageUrls.forEach(url => {
            const img = new Image();
            img.src = url;
        });

        // Add loading states for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Remove any loading classes or add loaded classes
            document.body.classList.add('loaded');
            
            // Initialize any additional animations or effects
            setTimeout(() => {
                document.querySelectorAll('.animate-on-scroll').forEach((el, index) => {
                    el.style.transitionDelay = `${index * 0.1}s`;
                });
            }, 100);
        });

        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const navLinks = document.getElementById('navLinks');
                navLinks.classList.remove('active');
            }
        });

        // Performance optimization: debounce scroll events
        let ticking = false;
        
        function updateOnScroll() {
            // Navbar scroll effect
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Parallax effect
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.hero-video');
            const speed = scrolled * 0.5;
            
            if (parallax && scrolled < window.innerHeight) {
                parallax.style.transform = `translateY(${speed}px)`;
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
    </script>
</body>
</html>