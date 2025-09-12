<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-gray-100">

@auth
    @if(auth()->user()->role === 'stylist')
        <div class="flex w-full">

            <!-- Sidebar -->
            <aside class="fixed top-0 left-0 h-full w-72 bg-gray-800 text-gray-100 p-6 shadow-lg flex flex-col">
                
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-green-500 text-center">🌟 Salon Good</h2>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1">
                    <ul class="space-y-3">
                        <li>
                            <a href="{{ route('stylist.dashboard') }}" 
                               class="block px-4 py-2 rounded hover:bg-gray-900 transition 
                               {{ request()->routeIs('stylist.dashboard') ? 'bg-green-500' : '' }}">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 rounded hover:bg-gray-900 transition">
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <!-- Optional Footer -->
                <div class="text-gray-400 text-sm mt-6 text-center">
                    &copy; {{ date('Y') }} Salon Good
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 ml-72 p-8 bg-gray-100 min-h-screen">
                @yield('content')
            </main>

        </div>
    @else
        <!-- Other authenticated users -->
        <nav class="bg-gray-800 text-white p-4">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <h1 class="text-xl font-bold">Salon Good</h1>
                <div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded hover:bg-gray-700">Logout</button>
                    </form>
                </div>
            </div>
        </nav>
        <main class="p-8">
            @yield('content')
        </main>
    @endif
@endauth

@guest
    <!-- Guest view -->
    <nav class="bg-gray-800 text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Salon Good</h1>
            <div>
                <a href="{{ route('login') }}" class="px-3 py-1 rounded hover:bg-gray-700">Login</a>
                <a href="{{ route('register') }}" class="px-3 py-1 rounded hover:bg-gray-700">Register</a>
            </div>
        </div>
    </nav>
    <main class="p-8">
        @yield('content')
    </main>
@endguest

</body>
</html>
