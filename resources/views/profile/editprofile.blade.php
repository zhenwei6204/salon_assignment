@extends('layout.app')

@section('content')
<div class="max-w-4xl mx-auto py-16 px-6">
    <div class="bg-gray-50 shadow-2xl rounded-3xl overflow-hidden p-10">
        <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center">Edit Profile</h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" x-data="{ photoPreview: null }" class="space-y-12">
            @csrf
            @method('PUT')

            <!-- Profile Photo -->
            <div class="flex flex-col items-center mb-10 relative">
                <div class="relative w-36 h-36">
                    <div class="w-36 h-36 rounded-full border-4 border-gray-300 shadow-xl overflow-hidden">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Profile Photo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!photoPreview">
                            <img src="{{ $user->profile_photo_path 
                                    ? asset('storage/' . $user->profile_photo_path) 
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=fff&background=111111' }}"
                                 alt="{{ $user->name }}"
                                 class="w-full h-full object-cover">
                        </template>
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-25 flex items-center justify-center opacity-0 hover:opacity-100 transition rounded-full cursor-pointer"
                         x-on:click="$refs.photo.click()">
                        <span class="text-white font-semibold text-sm">Change</span>
                    </div>
                </div>
                <input type="file" name="profile_photo" class="hidden" x-ref="photo"
                       x-on:change="
                           const file = $refs.photo.files[0];
                           if(file) {
                               const reader = new FileReader();
                               reader.onload = e => photoPreview = e.target.result;
                               reader.readAsDataURL(file);
                           }
                       " />
            </div>

            <!-- Personal Information -->
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-1">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-gray-900 focus:border-gray-900 px-4 py-2">
                    </div>
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-gray-900 focus:border-gray-900 px-4 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label for="phone" class="block text-gray-700 font-medium mb-1">Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-gray-900 focus:border-gray-900 px-4 py-2">
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-center">
                <button type="submit"
                    class="bg-gray-900 hover:bg-gray-800 text-white font-semibold px-8 py-3 rounded-2xl shadow-lg transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
