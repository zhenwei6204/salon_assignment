<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-700">
        <div class="w-full max-w-md p-10 bg-white shadow-2xl rounded-2xl border border-gray-100">

            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/salon-logo.png') }}" alt="Salon Logo" class="h-16 w-auto">
            </div>

            <!-- Title -->
            <h2 class="text-center text-2xl font-bold text-gray-900 mb-8">
                Welcome Back ✨
            </h2>

            <!-- Validation Errors -->
            <x-validation-errors class="mb-4" />

            @session('status')
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ $value }}
                </div>
            @endsession

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <x-label for="email" value="{{ __('Email') }}" class="text-gray-700 font-medium" />
                    <x-input id="email" 
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-gray-500 focus:ring focus:ring-gray-200"
                        type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                </div>

                <!-- Password -->
                <div>
                    <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 font-medium" />
                    <x-input id="password" 
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-gray-500 focus:ring focus:ring-gray-200"
                        type="password" name="password" required autocomplete="current-password" />
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <x-checkbox id="remember_me" name="remember" class="text-gray-600 focus:ring-gray-500" />
                        <span class="ms-2 text-gray-600">Remember me</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-gray-500 hover:text-gray-700 transition underline">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Login Button -->
                <x-button class="w-full bg-gray-900 hover:bg-gray-800 text-white px-5 py-3 rounded-xl shadow-lg transition text-base font-semibold">
                    {{ __('Log in') }}
                </x-button>
            </form>

            <!-- Divider -->
            <div class="flex items-center gap-4 my-8">
                <div class="h-px flex-1 bg-gray-200"></div>
                <span class="text-gray-400 text-sm">or</span>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <!-- Sign Up -->
            <div class="text-center">
                <a href="{{ route('register') }}"
                   class="inline-block w-full bg-white border border-gray-400 text-gray-800 px-6 py-3 rounded-xl font-semibold hover:bg-gray-100 transition shadow-sm">
                    Create an Account
                </a>
            </div>

            <!-- Admin Login -->
            <div class="mt-6 text-center">
                <a href="{{ url('/admin/login') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 transition underline">
                    Admin Login
                </a>
            </div>

        </div>
    </div>
</x-guest-layout>
