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
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

</body>
</html>