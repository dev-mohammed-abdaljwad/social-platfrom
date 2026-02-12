<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SocialHub')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <button id="menuToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="{{ url('/') }}" class="text-2xl font-bold text-blue-600">SocialHub</a>
                </div>

                <!-- Search Bar (Desktop) -->
                <div class="hidden md:flex flex-1 max-w-xs mx-4">
                    <div class="w-full relative">
                        <input 
                            type="text" 
                            placeholder="Search..." 
                            class="w-full px-4 py-2 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="searchInput"
                        >
                        <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    @auth
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- Messages -->
                    <button class="p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button onclick="toggleProfileMenu()" class="p-1 rounded-full hover:bg-gray-100 transition-colors">
                            <img 
                                src="{{ auth()->user()->avatar_url }}" 
                                alt="Profile" 
                                class="w-8 h-8 rounded-full object-cover"
                            >
                        </button>
                        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                            <a href="{{ url('/profile') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">My Profile</a>
                            <a href="{{ url('/settings') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                            <hr class="my-2">
                            <form method="POST" action="{{ url('/logout') }}" id="logoutForm">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                    @else
                    <button onclick="openLoginModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Login</button>
                    <button onclick="openRegisterModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Sign Up</button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Left Sidebar -->
        <aside id="sidebar" class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 bg-white border-r border-gray-200 shadow-sm overflow-y-auto -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40 lg:z-0">
            <!-- User Profile Section -->
            @auth
            <div class="p-4 border-b border-gray-200">
                <a href="{{ url('/profile') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <img src="{{ auth()->user()->avatar_url }}" 
                         alt="{{ auth()->user()->name }}" 
                         class="w-12 h-12 rounded-full object-cover border-2 border-blue-500">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ '@' . auth()->user()->username }}</p>
                    </div>
                </a>
            </div>
            @else
           
            @endauth

            <nav class="p-4 space-y-2">
                <!-- Home -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('/') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6 {{ request()->is('/') ? 'text-blue-600' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    <span>Home</span>
                </a>

                <!-- Friends -->
                <a href="{{ url('/friends') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('friends*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Friends</span>
                </a>

                <!-- Profile -->
                <a href="{{ url('/profile') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('profile*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Profile</span>
                </a>

                <!-- Explore -->
                <a href="{{ url('/explore') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('explore*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Explore</span>
                </a>

                <!-- Saved -->
                <a href="{{ url('/saved') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('saved*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    <span>Saved</span>
                </a>

                <!-- Settings -->
                <a href="{{ url('/settings') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('settings*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64 min-h-[calc(100vh-64px)]">
            <div class="max-w-4xl mx-auto px-4 py-6">
                @yield('content')
            </div>
        </main>

        <!-- Right Sidebar (Optional) -->
        @hasSection('rightSidebar')
        <aside class="hidden xl:block fixed right-0 top-16 h-[calc(100vh-64px)] w-80 bg-white border-l border-gray-200 shadow-sm overflow-y-auto p-4">
            @yield('rightSidebar')
        </aside>
        @endif
    </div>

    <!-- Mobile Search (shown when needed) -->
    <div id="mobileSearch" class="hidden fixed inset-0 bg-white z-50 p-4">
        <div class="flex items-center gap-4 mb-4">
            <button onclick="toggleMobileSearch()" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </button>
            <input 
                type="text" 
                placeholder="Search..." 
                class="flex-1 px-4 py-2 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                autofocus
            >
        </div>
        <div id="mobileSearchResults" class="space-y-2">
            <!-- Search results will appear here -->
        </div>
    </div>

    <!-- Login Modal -->
    @guest
    <div id="loginModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeLoginModal()"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 transform transition-all">
                <!-- Close Button -->
                <button onclick="closeLoginModal()" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome back</h2>
                <p class="text-gray-500 mb-6">Sign in to continue to SocialHub</p>

                <!-- Login Error -->
                <div id="loginError" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>

                <form id="loginForm" class="space-y-4">
                    @csrf
                    <div>
                        <label for="loginEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input 
                            type="email" 
                            id="loginEmail" 
                            name="email"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            placeholder="Enter your email"
                        >
                    </div>

                    <div>
                        <label for="loginPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="loginPassword" 
                                name="password"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Enter your password"
                            >
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility('loginPassword')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700">Forgot password?</a>
                    </div>

                    <button 
                        type="submit"
                        id="loginSubmitBtn"
                        class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span>Sign In</span>
                        <svg id="loginSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <p class="text-center text-gray-600 mt-6">
                    Don't have an account? 
                    <button onclick="switchToRegister()" class="text-blue-600 font-semibold hover:underline">Sign up</button>
                </p>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeRegisterModal()"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 transform transition-all max-h-[90vh] overflow-y-auto">
                <!-- Close Button -->
                <button onclick="closeRegisterModal()" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <h2 class="text-2xl font-bold text-gray-800 mb-2">Create account</h2>
                <p class="text-gray-500 mb-6">Join SocialHub and connect with friends</p>

                <!-- Register Error -->
                <div id="registerError" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>

                <form id="registerForm" class="space-y-4">
                    @csrf
                    <div>
                        <label for="registerName" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input 
                            type="text" 
                            id="registerName" 
                            name="name"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                            placeholder="Enter your full name"
                        >
                    </div>

                    <div>
                        <label for="registerUsername" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input 
                            type="text" 
                            id="registerUsername" 
                            name="username"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                            placeholder="Choose a username"
                        >
                    </div>

                    <div>
                        <label for="registerEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input 
                            type="email" 
                            id="registerEmail" 
                            name="email"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                            placeholder="Enter your email"
                        >
                    </div>

                    <div>
                        <label for="registerPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="registerPassword" 
                                name="password"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                placeholder="Create a password (min 8 characters)"
                            >
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility('registerPassword')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="registerPasswordConfirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input 
                            type="password" 
                            id="registerPasswordConfirm" 
                            name="password_confirmation"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                            placeholder="Confirm your password"
                        >
                    </div>

                    <div class="flex items-start gap-2">
                        <input type="checkbox" id="terms" required class="w-4 h-4 mt-1 text-purple-600 rounded focus:ring-purple-500">
                        <label for="terms" class="text-sm text-gray-600">
                            I agree to the <a href="#" class="text-purple-600 hover:underline">Terms of Service</a> and <a href="#" class="text-purple-600 hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    <button 
                        type="submit"
                        id="registerSubmitBtn"
                        class="w-full py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span>Create Account</span>
                        <svg id="registerSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <p class="text-center text-gray-600 mt-6">
                    Already have an account? 
                    <button onclick="switchToLogin()" class="text-purple-600 font-semibold hover:underline">Sign in</button>
                </p>
            </div>
        </div>
    </div>
    @endguest

    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Menu toggle button
        document.getElementById('menuToggle').addEventListener('click', toggleSidebar);

        // Toggle profile menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden');
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('profileMenu');
            const button = event.target.closest('button');
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleProfileMenu') === -1) {
                if (!menu.contains(event.target)) {
                    menu.classList.add('hidden');
                }
            }
        });

        // Toggle mobile search
        function toggleMobileSearch() {
            const mobileSearch = document.getElementById('mobileSearch');
            mobileSearch.classList.toggle('hidden');
        }

        // Auth Modal Functions
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('loginEmail').focus();
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.body.style.overflow = '';
            document.getElementById('loginError').classList.add('hidden');
            document.getElementById('loginForm').reset();
        }

        function openRegisterModal() {
            document.getElementById('registerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('registerName').focus();
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').classList.add('hidden');
            document.body.style.overflow = '';
            document.getElementById('registerError').classList.add('hidden');
            document.getElementById('registerForm').reset();
        }

        function switchToRegister() {
            closeLoginModal();
            setTimeout(openRegisterModal, 200);
        }

        function switchToLogin() {
            closeRegisterModal();
            setTimeout(openLoginModal, 200);
        }

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Login Form Submit
        document.getElementById('loginForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('loginSubmitBtn');
            const spinner = document.getElementById('loginSpinner');
            const errorDiv = document.getElementById('loginError');
            
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email: document.getElementById('loginEmail').value,
                        password: document.getElementById('loginPassword').value
                    })
                });
                
                if (response.ok || response.redirected) {
                    window.location.href = '/';
                } else {
                    const data = await response.json().catch(() => null);
                    errorDiv.textContent = data?.message || 'Invalid credentials. Please try again.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Something went wrong. Please try again.';
                errorDiv.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
            }
        });

        // Register Form Submit
        document.getElementById('registerForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('registerSubmitBtn');
            const spinner = document.getElementById('registerSpinner');
            const errorDiv = document.getElementById('registerError');
            
            const password = document.getElementById('registerPassword').value;
            const passwordConfirm = document.getElementById('registerPasswordConfirm').value;
            
            if (password !== passwordConfirm) {
                errorDiv.textContent = 'Passwords do not match.';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: document.getElementById('registerName').value,
                        username: document.getElementById('registerUsername').value,
                        email: document.getElementById('registerEmail').value,
                        password: password,
                        password_confirmation: passwordConfirm
                    })
                });
                
                if (response.ok || response.redirected) {
                    window.location.href = '/';
                } else {
                    const data = await response.json().catch(() => null);
                    if (data?.errors) {
                        const messages = Object.values(data.errors).flat().join(' ');
                        errorDiv.textContent = messages;
                    } else {
                        errorDiv.textContent = data?.message || 'Registration failed. Please try again.';
                    }
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Something went wrong. Please try again.';
                errorDiv.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
            }
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
                closeRegisterModal();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
