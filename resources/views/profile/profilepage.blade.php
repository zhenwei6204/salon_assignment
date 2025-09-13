@extends('layout.app')

@section('content')
<div class="max-w-5xl mx-auto py-12 px-6">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

        <!-- Header Banner -->
        <div class="h-40 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 relative">
            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2">
                <img src="{{ $user->profile_photo_path 
                            ? asset('storage/' . $user->profile_photo_path) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=fff&background=111111' }}"
                     alt="{{ $user->name }}"
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
            </div>
            <!-- Edit Profile Button positioned in header -->
            <div class="absolute top-6 right-6">
                <a href="{{ route('profile.edit') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white text-sm font-medium hover:bg-white/20 transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>

        <div class="mt-20 px-8 pb-12" x-data="{ tab: 'profile' }">
            <!-- Tabs -->
            <div class="flex justify-center gap-4 border-b border-gray-200 mb-6">
                <button 
                    :class="tab === 'profile' ? 'border-b-2 border-gray-900 font-semibold' : 'text-gray-500'" 
                    class="px-4 py-2" 
                    @click="tab = 'profile'">
                    Profile
                </button>
                <button 
                    :class="tab === 'appointments' ? 'border-b-2 border-gray-900 font-semibold' : 'text-gray-500'" 
                    class="px-4 py-2" 
                    @click="tab = 'appointments'">
                    Appointments
                </button>
                <button 
                    :class="tab === 'security' ? 'border-b-2 border-gray-900 font-semibold' : 'text-gray-500'" 
                    class="px-4 py-2" 
                    @click="tab = 'security'">
                    Security
                </button>
            </div>

            <!-- Profile Tab -->
            <div x-show="tab === 'profile'" x-cloak>
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    <p class="text-gray-400 text-sm">Joined on {{ $user->created_at->format('M d, Y') }}</p>
                    
                    <!-- Alternative Edit Profile Button (below user info) -->
                    <div class="mt-6">
                        <a href="{{ route('profile.edit') }}" 
                           class="inline-flex items-center px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Profile
                        </a>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-xl p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-gray-600 uppercase">Membership</h2>
                        <p class="mt-2 text-xl font-bold text-gray-900">Gold Package</p>
                        <p class="text-sm text-gray-500">Valid until Dec 2025</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-gray-600 uppercase">Loyalty Points</h2>
                        <p class="mt-2 text-xl font-bold text-gray-900">120 pts</p>
                        <p class="text-sm text-gray-500">Next reward at 150 pts</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-gray-600 uppercase">Role</h2>
                        <p class="mt-2 text-xl font-bold text-gray-900">{{ ucfirst($user->role) }}</p>
                    </div>
                </div>
            </div>

            <!-- Appointments Tab -->
            <div x-show="tab === 'appointments'" x-cloak>
                <h2 class="text-xl font-bold text-gray-800 mb-6">Recent Appointments</h2>

                @if($bookings->isEmpty())
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center text-gray-600">
                        No appointments found.
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($bookings as $booking)
                            <div class="bg-white shadow-md rounded-xl p-5 border border-gray-100 hover:shadow-lg transition">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm font-medium text-gray-500">#{{ $booking->booking_reference }}</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($booking->status === 'confirmed') bg-green-100 text-green-700
                                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-700
                                        @elseif($booking->status === 'completed') bg-blue-100 text-blue-700
                                        @else bg-red-100 text-red-700 @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $booking->service->name ?? 'N/A' }}</h3>
                                <p class="text-sm text-gray-500">With {{ $booking->stylist->name ?? 'N/A' }}</p>
                                <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                                    <div>
                                        <span class="font-medium">Date:</span> {{ $booking->formatted_date }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Time:</span> {{ $booking->formatted_time }} - {{ $booking->formatted_end_time ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="mt-4 text-right">
                                    <span class="text-pink-600 font-bold">RM {{ number_format($booking->total_price, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Security Tab -->
            <div x-show="tab === 'security'" x-cloak class="space-y-6">
                <div class="w-full">
                    @livewire('profile.update-password-form')
                </div>

                <div class="w-full">
                    @livewire('profile.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</div>
@endsection