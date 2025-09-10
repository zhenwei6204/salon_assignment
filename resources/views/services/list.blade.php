@extends('layout.app')

@section('title', 'All Services - Salon Good')

@section('content')
<div class="container">
    <!-- Header Section with Background Image -->
    <div class="header hero-header">
        <div class="header-overlay"></div>
        <div class="header-content">
            <h1>Our Services</h1>
            <p>Discover our complete range of premium beauty and wellness services</p>
        </div>
    </div>

    <div class="categories-tab-bar">
        <div class="nav-container">
            <!-- All Categories Tab -->
            <div class="nav-item">
                <a href="{{ route('services.index') }}" 
                   class="{{ request('category') == '' ? 'active' : '' }}">All Categories</a>
            </div>
            
            <!-- Loop through categories and create tabs -->
            @foreach($categories as $category)
                <div class="nav-item">
                    <a href="{{ route('services.index', ['category' => $category->id]) }}" 
                       class="{{ request('category') == $category->id ? 'active' : '' }}">{{ $category->name }}</a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-section animate-on-scroll">
        <div class="search-bar">
            <form method="GET" action="{{ route('services.index') }}" id="searchForm">
                <!-- Preserve existing filters -->
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('price_range'))
                    <input type="hidden" name="price_range" value="{{ request('price_range') }}">
                @endif
                @if(request('duration'))
                    <input type="hidden" name="duration" value="{{ request('duration') }}">
                @endif
                
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Search services..." 
                       value="{{ request('search') }}">
                <button type="submit" class="search-btn">
                    <span><i class="fas fa-search"></i> Search</span>
                </button>
            </form>
        </div>

        <form method="GET" action="{{ route('services.index') }}" id="filterForm">
            <!-- Keep search value when filtering -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            
            <div class="filters">
                <!-- Category Filter (Hidden, controlled by tabs) -->
                <input type="hidden" name="category" value="{{ request('category') }}">

                <!-- Price Range Filter -->
                <div class="filter-group">
                    <label for="price_range">Price Range</label>
                    <select name="price_range" id="price_range" class="filter-select" onchange="submitFilters()">
                        <option value="">All Prices</option>
                        <option value="0-50" {{ request('price_range') == '0-50' ? 'selected' : '' }}>$0 - $50</option>
                        <option value="51-100" {{ request('price_range') == '51-100' ? 'selected' : '' }}>$51 - $100</option>
                        <option value="101-200" {{ request('price_range') == '101-200' ? 'selected' : '' }}>$101 - $200</option>
                        <option value="201+" {{ request('price_range') == '201+' ? 'selected' : '' }}>$201+</option>
                    </select>
                </div>

                <!-- Duration Filter -->
                <div class="filter-group">
                    <label for="duration">Duration</label>
                    <select name="duration" id="duration" class="filter-select" onchange="submitFilters()">
                        <option value="">All Durations</option>
                        <option value="0-30" {{ request('duration') == '0-30' ? 'selected' : '' }}>0-30 min</option>
                        <option value="31-60" {{ request('duration') == '31-60' ? 'selected' : '' }}>31-60 min</option>
                        <option value="61-120" {{ request('duration') == '61-120' ? 'selected' : '' }}>61-120 min</option>
                        <option value="121+" {{ request('duration') == '121+' ? 'selected' : '' }}>121+ min</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <a href="{{ route('services.index') }}" class="clear-filters-btn">
                        <i class="fas fa-times"></i> Clear All
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Services Section -->
    <div class="services-section">
        <h2 class="section-title animate-on-scroll">Available Services</h2>

        @if($services->count() > 0)
        <div class="services-grid">
            @foreach($services as $service)
            <div class="service-card animate-on-scroll">
                <!-- Service Image -->
                <div class="service-image">
                    @if($service->image_url)
                        <img src="{{ Storage::url('image/'.$service->image_url) }}" 
                        alt="{{ $service->name }}"
                        style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
                    @else   
                        <div class="image-placeholder">
                        <span><i class="fas fa-palette"></i> Service Image Preview</span>
                        </div>
                    @endif
                        
                            <!-- Category Badge -->
                            <div class="service-category-badge">
                                {{ $service->category->name ?? 'Service' }}
                            </div>
                        </div>
                        
                <!-- Service Content -->
                <div class="service-content">
                    <h3>{{ $service->name }}</h3>
                    <div class="price">${{ number_format($service->price, 2) }}</div>
                    <div class="duration">
                        <i class="fas fa-clock"></i>
                        {{ $service->duration }} minutes
                    </div>
                    <p class="service-description">
                        {{ Str::limit($service->description ?? 'Professional service with expert care.', 100) }}
                    </p>
                    <a href="{{ route('services.show', $service->id) }}" class="view-detail-btn">View Details</a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($services->hasPages())
            <div class="pagination-wrapper">
                {{ $services->appends(request()->query())->links() }}
            </div>
        @endif
        @else
            <div class="no-results">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Services Found</h3>
                    <p>We couldn't find any services matching your criteria. Try adjusting your filters or search terms.</p>
                    <a href="{{ route('services.index') }}" class="reset-link">
                        <span>View All Services</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Back Navigation -->
    <div class="back-navigation animate-on-scroll">
        <a href="{{ url('/') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* CSS Variables */
    :root {
        --primary-black: #000000;
        --soft-black: #333333;
        --dark-gray: #666666;
        --light-gray: #f8f9fa;
        --medium-gray: #e9ecef;
        --pure-white: #ffffff;
        --border-color: #e0e0e0;
        --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.15);
        --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
        --transition-smooth: all 0.3s ease;
        --gradient-overlay: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.3));
    }

    /* Hero Header with Background Image */
    .hero-header {
        position: relative;
        height: 400px;
        background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.3)), 
                    url('/images/salon-hero-bg.jpg') center/cover no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--pure-white);
        margin-bottom: 4rem;
        border-radius: 12px;
        overflow: hidden;
    }

    .header-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--gradient-overlay);
        z-index: 1;
    }

    .header-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        padding: 2rem;
    }

    .hero-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        animation: fadeInUp 1s ease-out;
    }

    .hero-header p {
        font-size: 1.3rem;
        opacity: 0.95;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        animation: fadeInUp 1s ease-out 0.2s both;
    }

    /* Category Tabs */
    .categories-tab-bar {
        background: var(--pure-white);
        border-radius: 8px;
        box-shadow: var(--shadow-light);
        margin-bottom: 3rem;
        overflow: hidden;
    }

    .nav-container {
        display: flex;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .nav-container::-webkit-scrollbar {
        display: none;
    }

    .nav-item {
        flex-shrink: 0;
    }

    .nav-item a {
        display: block;
        padding: 1.2rem 2rem;
        color: var(--dark-gray);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition-smooth);
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }

    .nav-item a:hover,
    .nav-item a.active {
        color: var(--primary-black);
        background: var(--light-gray);
        border-bottom-color: var(--primary-black);
    }

    /* Services Grid */
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2.5rem;
        margin-top: 2rem;
    }

    .service-card {
        position: relative;
        background: var(--pure-white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-light);
        transition: var(--transition-smooth);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    /* Service Image */
    .service-image {
        position: relative;
        height: 240px;
        overflow: hidden;
        background: var(--gradient-overlay);
    }

    .service-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition-smooth);
    }

    .service-card:hover .service-img {
        transform: scale(1.05);
    }

    .service-image-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        background: linear-gradient(135deg, var(--light-gray), var(--medium-gray));
        color: var(--dark-gray);
        font-size: 1rem;
        font-weight: 500;
    }

    .service-image-placeholder i {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.6;
    }

    /* Service Category Badge */
    .service-category-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0,0,0,0.8);
        color: var(--pure-white);
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        backdrop-filter: blur(5px);
        z-index: 2;
    }

    /* Service Content */
    .service-content {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .service-content h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-black);
        margin-bottom: 1rem;
        line-height: 1.3;
    }

    .service-content .price {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-black);
        margin-bottom: 0.5rem;
    }

    .service-content .duration {
        color: var(--dark-gray);
        margin-bottom: 1rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .service-description {
        color: var(--soft-black);
        line-height: 1.6;
        font-size: 0.95rem;
        flex-grow: 1;
        margin-bottom: 2rem;
    }

    .view-detail-btn {
        display: inline-block;
        background: var(--primary-black);
        color: var(--pure-white);
        padding: 0.8rem 2rem;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        text-align: center;
        transition: var(--transition-smooth);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }

    .view-detail-btn:hover {
        background: var(--soft-black);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Search and Filter Section */
    .search-filter-section {
        background: var(--pure-white);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: var(--shadow-light);
        margin-bottom: 3rem;
    }

    .search-bar {
        margin-bottom: 1.5rem;
    }

    .search-bar form {
        display: flex;
        gap: 1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input {
        flex: 1;
        padding: 1rem 1.5rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: var(--transition-smooth);
    }

    .search-input:focus {
        border-color: var(--primary-black);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
    }

    .search-btn {
        background: var(--primary-black);
        color: var(--pure-white);
        border: none;
        padding: 1rem 2rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: var(--transition-smooth);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .search-btn:hover {
        background: var(--soft-black);
        transform: translateY(-2px);
    }

    .filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 0.8rem;
        font-weight: 600;
        color: var(--dark-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    .filter-select {
        width: 100%;
        padding: 1rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.95rem;
        background: var(--pure-white);
        transition: var(--transition-smooth);
    }

    .filter-select:focus {
        border-color: var(--primary-black);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
    }

    .clear-filters-btn {
        display: inline-block;
        background: var(--medium-gray);
        color: var(--dark-gray);
        text-decoration: none;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition-smooth);
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }

    .clear-filters-btn:hover {
        background: var(--dark-gray);
        color: var(--pure-white);
        transform: translateY(-2px);
    }

    /* Section Title */
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 3rem;
        color: var(--primary-black);
        position: relative;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--primary-black);
        border-radius: 2px;
    }

    /* Back Navigation */
    .back-navigation {
        margin-top: 4rem;
        text-align: center;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        color: var(--primary-black);
        text-decoration: none;
        font-weight: 600;
        padding: 1rem 2rem;
        border: 2px solid var(--primary-black);
        border-radius: 8px;
        transition: var(--transition-smooth);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .back-link:hover {
        background: var(--primary-black);
        color: var(--pure-white);
        transform: translateY(-2px);
    }

    /* Empty State */
    .no-results {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state {
        max-width: 500px;
        margin: 0 auto;
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--medium-gray);
        margin-bottom: 2rem;
    }

    .empty-state h3 {
        font-size: 1.8rem;
        color: var(--dark-gray);
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: var(--dark-gray);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .reset-link {
        display: inline-block;
        background: var(--primary-black);
        color: var(--pure-white);
        text-decoration: none;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition-smooth);
    }

    .reset-link:hover {
        background: var(--soft-black);
        transform: translateY(-2px);
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

    /* Responsive Design */
    @media (max-width: 1024px) {
        .hero-header h1 {
            font-size: 2.8rem;
        }
        
        .hero-header {
            height: 350px;
        }

        .services-grid {
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }
    }

    @media (max-width: 768px) {
        .hero-header {
            height: 300px;
            margin-bottom: 2rem;
        }

        .hero-header h1 {
            font-size: 2.2rem;
        }

        .hero-header p {
            font-size: 1.1rem;
        }

        .search-bar form {
            flex-direction: column;
        }

        .filters {
            grid-template-columns: 1fr;
        }

        .services-grid {
            grid-template-columns: 1fr;
        }

        .service-image {
            height: 200px;
        }

        .nav-container {
            padding: 0 1rem;
        }

        .nav-item a {
            padding: 1rem 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .hero-header {
            height: 250px;
        }

        .hero-header h1 {
            font-size: 1.8rem;
        }

        .header-content {
            padding: 1rem;
        }

        .service-content {
            padding: 1.5rem;
        }

        .section-title {
            font-size: 2rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function submitFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.submit();
    }
}

// Simple animation observer
document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });

    // Image loading error handling
    document.querySelectorAll('.service-img').forEach(img => {
        img.addEventListener('error', function() {
            this.parentElement.innerHTML = `
                <div class="service-image-placeholder">
                    <i class="fas fa-scissors"></i>
                    <span>Image not available</span>
                </div>
            `;
        });
    });
});
</script>
@endpush