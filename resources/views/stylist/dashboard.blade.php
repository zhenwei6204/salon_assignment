@extends('layout.stylist')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="flex space-x-4" id="tabs">
            <button class="tab-btn px-4 py-2 text-green-600 border-b-2 border-green-600 font-medium" data-tab="main">
                Main
            </button>
            <button class="tab-btn px-4 py-2 text-gray-600 hover:text-green-600" data-tab="profile">
                Profile
            </button>
            <button class="tab-btn px-4 py-2 text-gray-600 hover:text-green-600" data-tab="schedule">
                Schedule
            </button>
        </nav>
    </div>

    <!-- ================= MAIN ================= -->
    <div id="main" class="tab-content">
        <!-- Profile Display -->
        <div class="bg-white p-6 rounded-xl shadow">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <img src="{{ $stylist->user->profile_photo_path ? asset('storage/'.$stylist->user->profile_photo_path) : '/images/default-avatar.png' }}" 
                     alt="{{ $stylist->name }}" 
                     class="w-28 h-28 rounded-full object-cover border-2 border-green-500">

                <div class="flex-1 space-y-2">
                    <h2 class="text-3xl font-bold text-gray-900">{{ $stylist->name }}</h2>
                    <p class="text-gray-600"><strong>Title:</strong> {{ $stylist->title ?? '-' }}</p>
                    <p class="text-gray-600"><strong>Experience:</strong> {{ $stylist->experience_years }} years</p>
                    <p class="text-gray-600"><strong>Specializations:</strong> {{ $stylist->specializations ?? '-' }}</p>
                    @if($stylist->bio)
                        <p class="text-gray-700"><strong>Bio:</strong> {{ $stylist->bio }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white p-4 rounded-xl shadow text-center">
                <h4 class="text-gray-500">Services</h4>
                <p class="text-2xl font-bold">{{ $stylist->services->count() }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow text-center">
                <h4 class="text-gray-500">Reviews</h4>
                <p class="text-2xl font-bold">{{ $stylist->review_count }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow text-center">
                <h4 class="text-gray-500">Rating</h4>
                <p class="text-2xl font-bold">{{ number_format($stylist->rating,1) }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow text-center">
                <h4 class="text-gray-500">Status</h4>
                <span class="{{ $stylist->is_active ? 'bg-green-500' : 'bg-red-500' }} text-white px-3 py-1 rounded-full text-sm">
                    {{ $stylist->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <!-- Services Offered -->
        <div class="bg-white p-6 rounded-xl shadow mt-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Services Offered</h3>
            <ul class="space-y-3">
                @foreach($stylist->services as $service)
                    <li class="flex justify-between items-center bg-gray-50 p-4 rounded shadow hover:shadow-md transition">
                        <span class="text-gray-800 font-medium">{{ $service->name }}</span>
                        <span class="text-green-500 font-bold">${{ $service->price }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- ================= PROFILE (Edit + Password) ================= -->
<div id="profile" class="tab-content hidden">
    <div class="bg-white p-6 rounded-xl shadow space-y-10">

        <!-- Profile Edit -->
        <div>
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Profile Information</h3>
            <form action="{{ route('stylist.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Profile Photo -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Profile Photo</label>
                    <div class="flex items-center gap-6">
                        <!-- Preview -->
                        <div class="relative">
                            <img src="{{ $stylist->user->profile_photo_path ? asset('storage/'.$stylist->user->profile_photo_path) : '/images/default-avatar.png' }}" 
                                class="w-24 h-24 rounded-full object-cover border shadow">

                            <!-- Upload Overlay -->
                            <label for="profile_photo" 
                                class="absolute bottom-0 right-0 bg-green-500 hover:bg-green-600 text-white p-2 rounded-full cursor-pointer shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <input id="profile_photo" type="file" name="profile_photo" class="hidden">
                            </label>
                        </div>

                        <!-- Info -->
                        <p class="text-sm text-gray-500">
                            Upload a clear photo of yourself. <br>
                            PNG, JPG up to 2MB.
                        </p>
                    </div>
                </div>

                <!-- Name & Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $stylist->name) }}" class="w-full border rounded px-3 py-2 focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $stylist->user->email) }}" class="w-full border rounded px-3 py-2 focus:ring focus:ring-green-200">
                    </div>
                </div>

                <!-- Title (read-only) & Experience -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Title</label>
                        <p class="px-3 py-2 bg-gray-100 rounded border text-gray-700">
                            {{ $stylist->title }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Title is based on experience years and cannot be changed manually.</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Experience (Years)</label>
                        <input type="number" name="experience_years" value="{{ old('experience_years', $stylist->experience_years) }}" class="w-full border rounded px-3 py-2 focus:ring focus:ring-green-200">
                    </div>
                </div>

                <!-- Specializations -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Specializations</label>
                    <textarea name="specializations" rows="2" class="w-full border rounded px-3 py-2 focus:ring focus:ring-green-200">{{ old('specializations', $stylist->specializations) }}</textarea>
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Bio</label>
                    <textarea name="bio" rows="3" class="w-full border rounded px-3 py-2 focus:ring focus:ring-green-200">{{ old('bio', $stylist->bio) }}</textarea>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-500 text-white px-5 py-2 rounded hover:bg-green-600 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div>
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Change Password</h3>
            @livewire('profile.update-password-form')
        </div>
    </div>
</div>

    <!-- ================= SCHEDULE ================= -->
    <div id="schedule" class="tab-content hidden">
        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Manage Schedule</h3>

            <form action="{{ route('stylist.schedule.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-gray-700">Start Time</label>
                        <input type="time" name="start_time" value="{{ old('start_time', '10:00') }}" class="border rounded px-2 py-1 w-full">
                    </div>
                    <div>
                        <label class="block text-gray-700">End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', '20:00') }}" class="border rounded px-2 py-1 w-full">
                    </div>
                    <div>
                        <label class="block text-gray-700">Lunch Start</label>
                        <input type="time" name="lunch_start" value="{{ old('lunch_start', '13:00') }}" class="border rounded px-2 py-1 w-full">
                    </div>
                    <div>
                        <label class="block text-gray-700">Lunch End</label>
                        <input type="time" name="lunch_end" value="{{ old('lunch_end', '14:00') }}" class="border rounded px-2 py-1 w-full">
                    </div>
                </div>

                <button type="submit" class="bg-green-500 text-white px-5 py-2 rounded hover:bg-green-600">Save Schedule</button>
            </form>
        </div>

        <!-- Upcoming Bookings -->
        <div class="bg-white p-6 rounded-xl shadow mt-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Upcoming Bookings</h3>
            @if($upcomingBookings->count() > 0)
                <ul class="space-y-3">
                    @foreach($upcomingBookings as $booking)
                        <li class="bg-gray-50 p-4 rounded shadow">
                            <p class="font-medium text-gray-800">{{ $booking->customer_name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $booking->service->name ?? 'N/A' }} • 
                                {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }} 
                                ({{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }})
                            </p>
                            <span class="text-xs px-2 py-1 rounded {{ $booking->status == 'confirmed' ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 text-center">No upcoming bookings.</p>
            @endif
        </div>
    </div>
</div>

<!-- Tab Switcher JS -->
<script>
    const tabBtns = document.querySelectorAll(".tab-btn");
    const tabContents = document.querySelectorAll(".tab-content");

    tabBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            tabBtns.forEach(b => b.classList.remove("text-green-600", "border-b-2", "border-green-600", "font-medium"));
            tabContents.forEach(c => c.classList.add("hidden"));

            btn.classList.add("text-green-600", "border-b-2", "border-green-600", "font-medium");
            document.getElementById(btn.dataset.tab).classList.remove("hidden");
        });
    });
</script>
@endsection
