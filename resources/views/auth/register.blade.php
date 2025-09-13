<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-700">
        <div class="w-full max-w-md p-10 bg-white shadow-2xl rounded-2xl border border-gray-100">

            <!-- Salon Logo -->
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/salon-logo.png') }}" alt="Salon Logo" class="h-16 w-auto">
            </div>

            <!-- Title -->
            <h2 class="text-center text-2xl font-bold text-gray-900 mb-8">
                Create Your Account ✨
            </h2>

            <!-- Validation Errors -->
            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Name -->
                <div>
                    <x-label for="name" value="{{ __('Name') }}" class="text-gray-700 font-medium" />
                    <x-input id="name"
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-gray-500 focus:ring focus:ring-gray-200"
                        type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                </div>

                <!-- Email -->
                <div>
                    <x-label for="email" value="{{ __('Email') }}" class="text-gray-700 font-medium" />
                    <x-input id="email"
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-gray-500 focus:ring focus:ring-gray-200"
                        type="email" name="email" :value="old('email')" required autocomplete="username" />
                </div>

                <!-- Password -->
                <div>
                    <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 font-medium" />
                    <x-input id="password"
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-gray-500 focus:ring focus:ring-gray-200"
                        type="password" name="password" required autocomplete="new-password" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class="text-gray-700 font-medium" />
                    <x-input id="password_confirmation"
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-gray-500 focus:ring focus:ring-gray-200"
                        type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                <!-- Terms -->
                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <div class="flex items-center">
                        <x-checkbox name="terms" id="terms" required class="text-gray-600 focus:ring-gray-500" />
                        <label for="terms" class="ms-2 text-sm text-gray-600">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                    'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-gray-700 hover:text-gray-900">'.__('Terms of Service').'</a>',
                                    'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-gray-700 hover:text-gray-900">'.__('Privacy Policy').'</a>',
                            ]) !!}
                        </label>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('login') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 transition underline">
                        {{ __('Already registered?') }}
                    </a>

                    <x-button class="bg-gray-900 hover:bg-gray-800 text-white px-6 py-2 rounded-lg shadow-md transition font-semibold">
                        {{ __('Register') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
