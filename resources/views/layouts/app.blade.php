<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <!-- Tailwind CSS via CDN (Or use your compiled Vite build assets) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full font-sans text-gray-900 antialiased">

    <div class="flex min-h-screen flex-col md:flex-row">
        
        <!-- 1. SIDEBAR / MOBILE NAV CONTAINER -->
        <!-- Hidden on small screens, becomes a 64px (w-64) sidebar on md sizes up -->
        <aside class="w-full md:w-64 md:fixed md:inset-y-0 md:flex md:flex-col bg-slate-900 text-white z-10 shadow-lg">
            @include('partials.nav')
        </aside>

        <!-- 2. MAIN CONTENT AREA -->
        <!-- Offsets left margin on desktop so it doesn't hide behind the fixed sidebar -->
        <div class="flex flex-1 flex-col md:pl-64">
            
            <!-- Optional Top Header bar for profile/search (Recommended) -->
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6 lg:px-8">
                <div class="font-semibold text-lg text-gray-800">
                    @yield('page-title', 'Overview')
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">Admin User</span>
                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs">U</div>
                </div>
            </header>

            <!-- Dynamic Content Window -->
            <main class="py-6 sm:py-8">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    @if (session('success'))
                        <div
                            class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

        </div>
    </div>

</body>
</html>