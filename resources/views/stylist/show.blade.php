@extends('layout.app')

@section('title', ($stylist->name ?? 'Stylist') . ' - Stylist Profile')

@section('content')
<div class="container mx-auto px-4">

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('stylist.index') }}" 
           class="inline-block bg-gray-200 text-gray-800 px-4 py-2 rounded-full font-medium hover:bg-gray-300 transition">
            ← Back to Stylists
        </a>
    </div>

    <!-- Header -->
    <div class="text-center mt-8 mb-6">
        <h1 class="text-3xl font-bold text-black">{{ $stylist->name ?? 'Unknown Stylist' }}</h1>
        <p class="text-gray-600 mt-2">{{ $stylist->title ?? 'Stylist' }}</p>

        <!-- Average Rating -->
        @if($stylist->reviews->count() > 0)
            <p class="text-black mt-2">
                ★ {{ number_format($stylist->reviews->avg('rating'), 1) }}
                <span class="text-gray-600 text-sm">
                    ({{ $stylist->reviews->count() }} reviews)
                </span>
            </p>
        @else
            <p class="text-gray-500 mt-2">No ratings yet</p>
        @endif
    </div>

    <!-- Profile Section -->
    <div class="flex flex-col md:flex-row items-center md:items-start gap-8 bg-white rounded-xl shadow-md p-6">

        <!-- Profile Photo -->
        <div class="flex-shrink-0">
            <img src="{{ $stylist->user?->profile_photo_path 
                ? asset('storage/' . $stylist->user->profile_photo_path) 
                : ($stylist->image_url ? asset('storage/' . $stylist->image_url) : '/images/default-avatar.png') }}" 
                alt="{{ $stylist->name }}"
                class="rounded-full w-36 h-36 object-cover border-4 border-black shadow-lg mx-auto md:mx-0">
        </div>

        <!-- Info -->
        <div class="flex-1 text-center md:text-left">
            <p class="text-gray-700 mb-2 font-medium">
                Experience: <span class="font-semibold">{{ $stylist->experience_years ?? 0 }} years</span>
            </p>
            
            <p class="text-gray-700 mb-4">
                Specialties: 
                @if($stylist->services->count() > 0)
                    @foreach($stylist->services as $service)
                        <span class="inline-block bg-gray-200 text-gray-800 px-2 py-1 rounded-full text-sm mr-1 mb-1">{{ $service->name }}</span>
                    @endforeach
                @else
                    <span class="text-gray-500">No services listed</span>
                @endif
            </p>

            <!-- Book Button -->
            <a href="#contact" 
               class="bg-black text-white px-6 py-2 rounded-full font-medium hover:bg-gray-800 transition">
                Book Appointment
            </a>
        </div>
    </div>

    <!-- About / Bio Section -->
    @if(!empty($stylist->bio))
        <div class="mt-8 bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-semibold mb-4">About {{ $stylist->name }}</h2>
            <p class="text-gray-700 leading-relaxed">{{ $stylist->bio }}</p>
        </div>
    @endif

    <!-- Services Offered -->
    <div class="mt-8 bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-4">Services Offered</h2>

        @if($stylist->services->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($stylist->services as $service)
                    <div class="p-4 bg-gray-50 rounded-lg text-center hover:shadow-lg transition">
                        <h3 class="font-semibold text-black">{{ $service->name }}</h3>
                        <p class="text-gray-600 text-sm">{{ $service->description ?? 'No description available' }}</p>
                        @if(!empty($service->price))
                            <p class="text-black font-medium mt-2">${{ number_format($service->price, 2) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">This stylist has not added any services yet.</p>
        @endif
    </div>

    <!-- Reviews Section -->
    <div class="mt-8 bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
            <span class="text-black">★</span> Customer Reviews
        </h2>

        <!-- Review Form (only if logged in) -->
        @auth
            <form action="{{ route('reviews.store', $stylist->id) }}" method="POST" class="mb-8">
                @csrf

                <!-- Rating Stars -->
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-gray-700">Your Rating</label>
                    <div class="flex items-center space-x-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="hidden peer/star{{ $i }}" required>
                            <label for="star{{ $i }}" class="cursor-pointer text-3xl text-gray-300 hover:text-black peer-checked/star{{ $i }}:text-black transition">
                                ★
                            </label>
                        @endfor
                    </div>
                </div>

                <!-- Comment -->
                <div class="mb-4">
                    <label for="comment" class="block mb-2 font-medium text-gray-700">Your Comment</label>
                    <textarea name="comment" id="comment" rows="3" 
                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-black focus:border-black"
                        placeholder="Write your feedback..."></textarea>
                </div>

                <!-- Submit -->
                <button type="submit" 
                    class="bg-black text-white px-6 py-2 rounded-full font-medium hover:bg-gray-800 shadow-md transition">
                    Submit Review
                </button>
            </form>
        @else
            <p class="text-gray-500 mb-6">You must 
                <a href="{{ route('login') }}" class="text-black underline hover:text-gray-700">log in</a> 
                to leave a review.
            </p>
        @endauth

        <!-- Display Existing Reviews -->
        @if($stylist->reviews->count() > 0)
            <div class="space-y-6">
                @foreach($stylist->reviews as $review)
                    <div class="p-5 border rounded-xl bg-gray-50 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-700">
                                    {{ strtoupper(substr($review->user?->name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-black">{{ $review->user?->name ?? 'Anonymous User' }}</p>
                                    <p class="text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="text-black text-lg">
                                {!! str_repeat('★', $review->rating) !!}{!! str_repeat('☆', 5 - $review->rating) !!}
                            </div>
                        </div>
                        @if($review->comment)
                            <p class="text-gray-700 leading-relaxed">{{ $review->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No reviews yet. Be the first to leave one!</p>
        @endif
    </div>
</div>
@endsection


@push('styles')
<style>
.profile-section img {
    transition: transform 0.3s ease;
}
.profile-section:hover img {
    transform: scale(1.05);
}
.service-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.service-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
// Animate cards and sections on scroll
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.animate-on-scroll');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(el => observer.observe(el));
});
</script>
@endpush
