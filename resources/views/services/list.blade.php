@extends('layout.app')

@section('title', 'All Services - Salon Good')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>Our Services</h1>
        <p>Discover our complete range of premium beauty and wellness services</p>
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
    /* Category Tabs Wrapper */
    .category-tabs-wrapper {
        margin: 2rem 0 3rem 0;
        background: var(--light-gray);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: var(--shadow-light);
    }

    .category-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }

    .category-tab {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        padding: 1rem 1.5rem;
        background: var(--pure-white);
        border: 2px solid var(--border-color);
        border-radius: 8px;
        text-decoration: none;
        color: var(--dark-gray);
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-width: 120px;
    }

    .category-tab span {
        font-size: 0.95rem;
        margin-bottom: 0.3rem;
    }

    .category-tab small {
        background: var(--medium-gray);
        color: var(--dark-gray);
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .category-tab:hover,
    .category-tab.active {
        background: var(--primary-black);
        color: var(--pure-white);
        border-color: var(--primary-black);
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }

    .category-tab:hover small,
    .category-tab.active small {
        background: rgba(255, 255, 255, 0.2);
        color: var(--pure-white);
    }

    /* Active Filters Display */
    .active-filters {
        background: var(--medium-gray);
        padding: 1rem 1.5rem;
        border-radius: 6px;
        margin-bottom: 2rem;
    }

    .active-filters h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
        color: var(--dark-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .filter-tag {
        background: var(--primary-black);
        color: var(--pure-white);
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-tag a {
        color: var(--pure-white);
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1rem;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .filter-tag a:hover {
        opacity: 1;
    }

    /* Service category badge */
    .service-category-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--primary-black);
        color: var(--pure-white);
        padding: 0.3rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Results count */
    .results-count {
        font-size: 1rem;
        font-weight: 400;
        color: var(--dark-gray);
        margin-left: 0.5rem;
    }

    /* Services Grid */
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .service-card {
        position: relative;
        background: var(--pure-white);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 2.5rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .service-card h3 {
        margin: 0 0 1rem 0;
        font-size: 1.4rem;
        color: var(--primary-black);
    }

    .service-card .price {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--primary-black);
        margin-bottom: 0.5rem;
    }

    .service-card .duration {
        color: var(--dark-gray);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .service-description {
        color: var(--soft-black);
        margin-bottom: 2rem;
        line-height: 1.6;
        font-size: 0.95rem;
        flex-grow: 1;
    }

    .view-detail-btn {
        display: inline-block;
        background: var(--primary-black);
        color: var(--pure-white);
        padding: 0.8rem 2rem;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .view-detail-btn:hover {
        background: var(--soft-black);
        transform: translateY(-2px);
    }

    /* Existing styles for filters, pagination, etc. */
    .search-filter-section {
        background: var(--pure-white);
        padding: 2rem;
        border-radius: 8px;
        box-shadow: var(--shadow-light);
        margin-bottom: 3rem;
    }

    .search-bar {
        margin-bottom: 1.5rem;
    }

    .search-bar form {
        display: flex;
        gap: 1rem;
        max-width: 500px;
        margin: 0 auto;
    }

    .search-input {
        flex: 1;
        padding: 0.8rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 1rem;
    }

    .search-btn {
        background: var(--primary-black);
        color: var(--pure-white);
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        background: var(--soft-black);
    }

    .filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark-gray);
    }

    .filter-select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 0.95rem;
        background: var(--pure-white);
    }

    .clear-filters-btn {
        display: inline-block;
        background: var(--medium-gray);
        color: var(--dark-gray);
        text-decoration: none;
        padding: 0.8rem 1rem;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-align: center;
    }

    .clear-filters-btn:hover {
        background: var(--dark-gray);
        color: var(--pure-white);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .category-tabs {
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .category-tab {
            flex-shrink: 0;
            min-width: 100px;
            padding: 0.8rem 1rem;
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
    }

    /* Variables - make sure these are defined */
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
        --transition-smooth: all 0.3s ease;
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
    });

    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
});
</script>
@endpush