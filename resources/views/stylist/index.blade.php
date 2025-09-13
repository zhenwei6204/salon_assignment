@extends('layout.app')

@section('title', 'All Stylists - Salon Good')

@section('content')
<div class="container mx-auto px-4">

    <!-- Header -->
    <div class="text-center mt-8 mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Our Stylists</h1>
        <p class="text-gray-600 mt-2">Discover our talented stylists and their specialties</p>
    </div>

    <!-- Search & Filter Section -->
    <div class="mb-8">
        <form method="GET" action="{{ route('stylist.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-2 shadow-md rounded-lg p-4 bg-white items-end max-w-4xl mx-auto">

            <!-- Search -->
            <input type="text" name="search" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-green-400 focus:outline-none" 
                   placeholder="Search stylists..." value="{{ request('search') }}">

            <!-- Experience Filter -->
            <select name="experience" class="w-full border rounded px-4 py-2">
                <option value="">Experience</option>
                <option value="0-2" {{ request('experience')=='0-2'?'selected':'' }}>0-2 years</option>
                <option value="3-5" {{ request('experience')=='3-5'?'selected':'' }}>3-5 years</option>
                <option value="5+" {{ request('experience')=='5+'?'selected':'' }}>5+ years</option>
            </select>

            <!-- Rating Filter -->
            <select name="rating" class="w-full border rounded px-4 py-2">
                <option value="">Min Rating</option>
                @for($i=1;$i<=5;$i++)
                    <option value="{{ $i }}" {{ request('rating')==$i?'selected':'' }}>{{ $i }}+</option>
                @endfor
            </select>

            <!-- Service Filter -->
            <select name="service" class="border rounded px-4 py-2 w-full sm:w-auto">
                <option value="">Service</option>
            @foreach($allServices as $service)
                <option value="{{ $service }}" {{ request('service')==$service?'selected':'' }}>{{ $service }}</option>
            @endforeach
            </select>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition font-semibold">
                Search
            </button>
        </form>
    </div>

    <!-- Stylists Grid -->
    <div class="services-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
        @forelse($stylists as $stylist)
            <div class="service-card flex flex-col min-h-[400px] p-6 bg-white rounded-xl shadow-md">
                
                <!-- Profile Photo -->
                <img src="{{ $stylist->user?->profile_photo_path ? asset('storage/'.$stylist->user->profile_photo_path) : '/images/default-avatar.png' }}" 
                     alt="{{ $stylist->name }}" class="rounded-full w-28 h-28 mx-auto mb-4 object-cover border-2 border-green-500 shadow-sm">

                <!-- Name & Title -->
                <h3 class="text-xl font-semibold text-gray-800 text-center">{{ $stylist->name }}</h3>
                <p class="text-gray-600 text-center">{{ $stylist->title ?? 'Stylist' }}</p>
                <p class="text-gray-500 text-sm text-center mb-3">{{ $stylist->experience_years ?? 0 }} years experience</p>

                <!-- Services -->
                <div class="flex flex-wrap justify-center gap-1 mb-4">
                    @foreach($stylist->services as $service)
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">{{ $service->name }}</span>
                    @endforeach
                </div>

                <!-- View More Button -->
                <div class="mt-auto text-center">
                <a href="{{ route('stylists.show', $stylist->id) }}" 
                    class="inline-block bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition font-semibold">
                    View More
                </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-600">
                <h3 class="text-lg font-semibold">No Stylists Found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        {{ $stylists->links() }}
    </div>

</div>
@endsection

@push('styles')
<style>
.service-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.service-card img {
    transition: transform 0.3s ease;
}

.service-card img:hover {
    transform: scale(1.05);
}
</style>
@endpush


@push('scripts')
<script>
// Animate cards on scroll with stagger
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.animate-on-scroll .service-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                cards.forEach((el, i) => {
                    setTimeout(() => {
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }, i * 100);
                });
            }
        });
    });

    cards.forEach(el => observer.observe(el));
});
</script>
@endpush
