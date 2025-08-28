<!DOCTYPE html>
<html lang="en">

@extends('layout.app')

@section('title', 'Service Details - Salon Good')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deluxe Hair Coloring - Salon Good</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
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
            --gradient-primary: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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

        /* Main Content */
        .main-content {
            margin-top: 100px;
            padding: 6rem 0;
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
            
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        /* Service Details Layout */
        .service-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-bottom: 4rem;
        }

        .service-info {
            order: 2;
        }

        .service-visual {
            order: 1;
        }

        .service-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--primary-black);
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .category-badge {
            display: inline-block;
            background: var(--light-gray);
            color: var(--primary-black);
            padding: 0.5rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .category-badge:hover {
            background: var(--primary-black);
            color: var(--pure-white);
        }

        /* Service Image */
        .service-image-preview {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .service-image-preview:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-hover);
        }

        .image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #f5f5f5, #e0e0e0);
            color: var(--soft-black);
            font-size: 1.1rem;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .image-placeholder::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: var(--transition);
        }

        .image-placeholder:hover::before {
            left: 100%;
        }

        .image-placeholder span {
            position: relative;
            z-index: 2;
        }

        /* Meta Info */
        .meta-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2.5rem 0;
            padding: 2.5rem;
            background: var(--light-gray);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .meta-info::before {
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

        .meta-info:hover::before {
            opacity: 1;
        }

        .meta-item {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .meta-item strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--soft-black);
            margin-bottom: 0.5rem;
        }

        .meta-value {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-black);
        }

        .availability-available {
            color: #28a745;
        }

        .availability-unavailable {
            color: #dc3545;
        }

        /* Details Sections */
        .details-section {
            margin: 3rem 0;
            padding: 3rem 2.5rem;
            background: var(--pure-white);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .details-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.02), transparent);
            transition: var(--transition);
        }

        .details-section:hover::before {
            left: 100%;
        }

        .details-section:hover {
            box-shadow: var(--shadow-light);
            border-color: var(--primary-black);
        }

        .details-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .details-section h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: var(--primary-black);
        }

        .details-section p {
            color: var(--soft-black);
            line-height: 1.8;
            font-size: 1.05rem;
            position: relative;
            z-index: 2;
        }

        /* Booking Section */
        .booking-section {
            text-align: center;
            margin: 4rem 0;
            padding: 4rem 3rem;
            background: var(--light-gray);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .booking-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(0,0,0,0.03), transparent);
            opacity: 0;
            transition: var(--transition);
        }

        .booking-section:hover::before {
            opacity: 1;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 1.5rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 0;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid var(--primary-black);
            background: var(--primary-black);
            color: var(--pure-white);
            z-index: 2;
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

        .btn:hover::before {
            left: 0;
        }

        .btn:hover {
            color: var(--primary-black);
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn.disabled {
            background: var(--medium-gray);
            border-color: var(--medium-gray);
            color: var(--soft-black);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn.disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .btn.disabled::before {
            display: none;
        }

        .booking-note {
            margin-top: 1.5rem;
            color: var(--soft-black);
            font-style: italic;
            font-size: 1rem;
            position: relative;
            z-index: 2;
        }

        /* Back Link */
        .back-navigation {
            margin-top: 4rem;
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
        }

        .back-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
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
        }

        /* Related Services */
        .related-services {
            margin-top: 6rem;
            padding-top: 4rem;
            border-top: 1px solid var(--border-color);
        }

        .related-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--primary-black);
            position: relative;
        }

        .related-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 2px;
            background-color: var(--primary-black);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .related-card {
            background: var(--pure-white);
            border: 1px solid var(--border-color);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .related-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.02), transparent);
            transition: var(--transition);
        }

        .related-card:hover::before {
            left: 100%;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-black);
        }

        .related-card h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-black);
            position: relative;
            z-index: 2;
        }

        .related-card .price {
            font-weight: 700;
            color: var(--primary-black);
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .related-card a {
            color: var(--primary-black);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        .related-card a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-black);
            transition: var(--transition);
        }

        .related-card a:hover::after {
            width: 100%;
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
            
            .service-title {
                font-size: 2.2rem;
            }

            .container {
                padding: 0 1.5rem;
            }

            .service-details {
                gap: 3rem;
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

            .service-details {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .service-info {
                order: 1;
            }

            .service-visual {
                order: 2;
            }

            .service-title {
                font-size: 1.8rem;
            }

            .meta-info {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 4rem 0;
            }

            .container {
                padding: 0 1rem;
            }

            .details-section, .booking-section {
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

            .service-title {
                font-size: 1.5rem;
            }

            .meta-value {
                font-size: 1.4rem;
            }

            .btn {
                padding: 1.2rem 2rem;
                font-size: 1rem;
            }

            .service-image-preview {
                height: 250px;
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
            <h1>{{ $service->name }}</h1>
            <p>Discover everything you need to know about our premium services</p>
        </div>

        <!-- Service Details Section -->
        <div class="service-details animate-on-scroll">
            <!-- Service Information -->
            <div class="service-info">
                <h2 class="service-title">{{ $service->name }}</h2>
                <div class="category-badge">{{ $service->category->name }}</div>

                <!-- Price, Duration and Availability -->
                <div class="meta-info">
                    <div class="meta-item">
                        <strong>Price</strong>
                        <div class="meta-value">${{ number_format($service->price, 2) }}</div>
                    </div>
                    <div class="meta-item">
                        <strong>Duration</strong>
                        <div class="meta-value">{{ $service->duration }} minutes</div>
                    </div>
                    <div class="meta-item">
                        <strong>Availability</strong>
                        <div class="meta-value availability-available">Available</div>
                    </div>
                </div>
            </div>

            <!-- Service Visual (Image) -->
            <div class="service-visual">
                <div class="service-image-preview">
                    <div class="image-placeholder">
                        <span><i class="fas fa-palette"></i> Service Image Preview</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Description -->
        <div class="details-section animate-on-scroll">
            <h3>Description</h3>
            <p>{{ $service->description }}</p>
        </div>

        <!-- Service Benefits -->
        <div class="details-section animate-on-scroll">
            <h3>Benefits</h3>
            <p>{{ $service->benefits }}</p>
        </div>

        <!-- Stylist Qualifications -->
        <div class="details-section animate-on-scroll">
            <h3>Stylist Qualifications</h3>
            <p>{{ $service->stylist_qualifications }}</p>
        </div>

        <!-- Booking Section -->
        <div class="booking-section animate-on-scroll">
           <a href="{{ route('booking.select.stylist', $service->id) }}" class="btn">
            <span><i class="fas fa-calendar-check"></i> Book This Service</span>
        </a>
            <p class="booking-note">Select your preferred stylist and time slot</p>
        </div>

        <!-- Back Navigation Link -->
        <div class="back-navigation animate-on-scroll">
            <a href="{{ route('services.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Services
            </a>
        </div>

        <!-- Related Services -->
        <div class="related-services animate-on-scroll">
            <h3 class="related-title">You Might Also Like</h3>
            <div class="related-grid">
                @foreach($relatedServices as $relatedService)
                    <div class="related-card">
                        <h4>{{ $relatedService->name }}</h4>
                        <p class="price">${{ number_format($relatedService->price, 2) }}</p>
                        <a href="{{ route('services.show', $relatedService->id) }}">View Details</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
</html>

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
                    }, index * 150);
                }
            });
        }, observerOptions);

        // Observe all elements with animation class
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });

        // Enhanced section interactions
        document.querySelectorAll('.details-section, .related-card').forEach(section => {
            section.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            section.addEventListener('mouseleave', function() {
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

        // Image placeholder enhancement
        const imagePlaceholder = document.querySelector('.image-placeholder');
        if (imagePlaceholder) {
            imagePlaceholder.addEventListener('click', function() {
                // Simulate image loading or gallery opening
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        }
    </script>
</body>
</html>