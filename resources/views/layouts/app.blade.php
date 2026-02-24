<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SocialTime')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
    <style>
        @keyframes bounce-in {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
            }
        }

        .animate-bounce-in {
            animation: bounce-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
    </style>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <svg viewBox="0 0 200 200" width="36" height="36">
                            <defs>
                                <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#2563eb;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#1e40af;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <circle cx="60" cy="70" r="35" fill="url(#logoGradient)" opacity="0.9" />
                            <circle cx="140" cy="100" r="40" fill="url(#logoGradient)" opacity="0.8" />
                            <circle cx="100" cy="30" r="25" fill="url(#logoGradient)" opacity="0.95" />
                            <line x1="85" y1="50" x2="100" y2="35" stroke="url(#logoGradient)" stroke-width="3"
                                stroke-linecap="round" opacity="0.6" />
                            <line x1="90" y1="100" x2="115" y2="90" stroke="url(#logoGradient)" stroke-width="3"
                                stroke-linecap="round" opacity="0.6" />
                            <path d="M 35 100 L 25 115 L 40 105 Z" fill="url(#logoGradient)" opacity="0.9" />
                            <circle cx="100" cy="80" r="8" fill="white" opacity="0.3" />
                        </svg>
                        <span class="text-2xl font-bold text-blue-600">SocialTime</span>
                    </a>
                </div>

                <!-- Search Bar (Desktop) -->
                <div class="hidden md:flex flex-1 max-w-xs mx-4">
                    <div class="w-full relative" id="searchContainer">
                        <form action="{{ route('search') }}" method="GET" id="searchForm">
                            <input type="text" name="q" placeholder="Search users..."
                                class="w-full px-4 py-2 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                id="searchInput" autocomplete="off">
                        </form>
                        <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <!-- Search Suggestions Dropdown -->
                        <div id="searchSuggestions"
                            class="hidden absolute left-0 right-0 top-full mt-1 bg-white rounded-lg shadow-xl border border-gray-200 max-h-80 overflow-y-auto z-50">
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    @auth
                        <!-- Notifications -->
                        <div class="relative">
                            <button onclick="toggleNotifications()"
                                class="relative p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                id="notificationBtn">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <span id="notificationBadge"
                                    class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] bg-red-500 rounded-full text-white text-xs flex items-center justify-center font-medium">0</span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div id="notificationDropdown"
                                class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-gray-200 max-h-[80vh] overflow-hidden z-50">
                                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                                    <button onclick="markAllNotificationsRead()"
                                        class="text-sm text-blue-600 hover:text-blue-700">Mark all read</button>
                                </div>
                                <div id="notificationList" class="overflow-y-auto max-h-[400px]">
                                    <div class="p-4 text-center text-gray-500">Loading...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages with unread badge -->
                        <a href="{{ route('chat.index') }}"
                            class="relative p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            <span id="chatUnreadBadge"
                                class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] bg-blue-600 rounded-full text-white text-xs flex items-center justify-center font-medium">0</span>
                        </a>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button onclick="toggleProfileMenu()"
                                class="p-1 rounded-full hover:bg-gray-100 transition-colors">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Profile"
                                    class="w-8 h-8 rounded-full object-cover">
                            </button>
                            <div id="profileMenu"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                                <a href="{{ url('/profile') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">My
                                    Profile</a>
                                <a href="{{ url('/settings') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                                <hr class="my-2">
                                <form method="POST" action="{{ url('/logout') }}" id="logoutForm">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <button onclick="openLoginModal()"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Login</button>
                        <button onclick="openRegisterModal()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Sign
                            Up</button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Left Sidebar -->
        <aside id="sidebar"
            class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 bg-white border-r border-gray-200 shadow-sm overflow-y-auto -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40 lg:z-0">
            <!-- User Profile Section -->
            @auth
                <div class="p-4 border-b border-gray-200">
                    <a href="{{ url('/profile') }}"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
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
                <a href="{{ url('/') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('/') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6 {{ request()->is('/') ? 'text-blue-600' : '' }}" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path
                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                        </path>
                    </svg>
                    <span>Home</span>
                </a>

                <!-- Friends -->
                <a href="{{ url('/friends') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('friends*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span>Friends</span>
                </a>

                <!-- Find People -->
                <a href="{{ url('/search') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('search*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                    <span>Find People</span>
                </a>

                <!-- Profile -->
                <a href="{{ url('/profile') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('profile*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Profile</span>
                </a>

                <!-- Explore -->
                <a href="{{ url('/explore') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('explore*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Explore</span>
                </a>

                <!-- Saved -->
                <a href="{{ url('/saved') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('saved*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    <span>Saved</span>
                </a>

                <!-- Messages -->
                <a href="{{ route('chat.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('chat*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <div class="relative">
                        <svg class="w-6 h-6 {{ request()->is('chat*') ? 'text-blue-600' : '' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        <span id="sidebarChatBadge"
                            class="hidden absolute -top-1 -right-1 min-w-[16px] h-4 bg-blue-600 rounded-full text-white text-[10px] flex items-center justify-center font-bold"></span>
                    </div>
                    <span>Messages</span>
                </a>

                <!-- Settings -->
                <a href="{{ url('/settings') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors font-semibold {{ request()->is('settings*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"
            onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64 min-h-[calc(100vh-64px)]">
            <div class="max-w-4xl mx-auto px-2 sm:px-4 py-3 sm:py-6">
                @yield('content')
            </div>
        </main>

        <!-- Right Sidebar (Optional) -->
        @hasSection('rightSidebar')
            <aside
                class="hidden xl:block fixed right-0 top-16 h-[calc(100vh-64px)] w-80 bg-white border-l border-gray-200 shadow-sm overflow-y-auto p-4">
                @yield('rightSidebar')
            </aside>
        @endif
    </div>

    <!-- Mobile Search (shown when needed) -->
    <div id="mobileSearch" class="hidden fixed inset-0 bg-white z-50 p-4">
        <div class="flex items-center gap-4 mb-4">
            <button onclick="toggleMobileSearch()" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </button>
            <input type="text" placeholder="Search..."
                class="flex-1 px-4 py-2 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                autofocus>
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
                    <button onclick="closeLoginModal()"
                        class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>

                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome back</h2>
                    <p class="text-gray-500 mb-6">Sign in to continue to SocialTime</p>

                    <!-- Login Error -->
                    <div id="loginError"
                        class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>

                    <form id="loginForm" class="space-y-4">
                        @csrf
                        <div>
                            <label for="loginEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="loginEmail" name="email" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Enter your email">
                        </div>

                        <div>
                            <label for="loginPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input type="password" id="loginPassword" name="password" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                    placeholder="Enter your password">
                                <button type="button" onclick="togglePasswordVisibility('loginPassword')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember"
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-700">Forgot password?</a>
                        </div>

                        <button type="submit" id="loginSubmitBtn"
                            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                            <span>Sign In</span>
                            <svg id="loginSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </button>
                    </form>

                    <p class="text-center text-gray-600 mt-6">
                        Don't have an account?
                        <button onclick="switchToRegister()" class="text-blue-600 font-semibold hover:underline">Sign
                            up</button>
                    </p>
                </div>
            </div>
        </div>

        <!-- Register Modal -->
        <div id="registerModal" class="fixed inset-0 z-[100] hidden">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeRegisterModal()"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 transform transition-all max-h-[90vh] overflow-y-auto">
                    <!-- Close Button -->
                    <button onclick="closeRegisterModal()"
                        class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>

                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Create account</h2>
                    <p class="text-gray-500 mb-6">Join SocialTime and connect with friends</p>

                    <!-- Register Error -->
                    <div id="registerError"
                        class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>

                    <form id="registerForm" class="space-y-4">
                        @csrf
                        <div>
                            <label for="registerName" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="registerName" name="name" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                placeholder="Enter your full name">
                        </div>

                        <div>
                            <label for="registerUsername"
                                class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" id="registerUsername" name="username" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                placeholder="Choose a username">
                        </div>

                        <div>
                            <label for="registerEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="registerEmail" name="email" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                placeholder="Enter your email">
                        </div>

                        <div>
                            <label for="registerPassword"
                                class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input type="password" id="registerPassword" name="password" required minlength="8"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                    placeholder="Create a password (min 8 characters)">
                                <button type="button" onclick="togglePasswordVisibility('registerPassword')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="registerPasswordConfirm"
                                class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" id="registerPasswordConfirm" name="password_confirmation" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                                placeholder="Confirm your password">
                        </div>

                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="terms" required
                                class="w-4 h-4 mt-1 text-purple-600 rounded focus:ring-purple-500">
                            <label for="terms" class="text-sm text-gray-600">
                                I agree to the <a href="#" class="text-purple-600 hover:underline">Terms of Service</a> and
                                <a href="#" class="text-purple-600 hover:underline">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" id="registerSubmitBtn"
                            class="w-full py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                            <span>Create Account</span>
                            <svg id="registerSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </button>
                    </form>

                    <p class="text-center text-gray-600 mt-6">
                        Already have an account?
                        <button onclick="switchToLogin()" class="text-purple-600 font-semibold hover:underline">Sign
                            in</button>
                    </p>
                </div>
            </div>
        </div>
    @endguest

    <script>
        window.CURRENT_USER_ID = {{ auth()->id() ?? 'null' }};
        window.CURRENT_CONVERSATION_ID = {{ $activeConversation ?? 'null' }};

    </script>

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

        // ==================== SEARCH FUNCTIONALITY ====================
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        let searchTimeout = null;

        if (searchInput && searchSuggestions) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const query = this.value.trim();

                if (query.length < 2) {
                    searchSuggestions.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`/search/suggestions?q=${encodeURIComponent(query)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();

                        if (data.users && data.users.length > 0) {
                            searchSuggestions.innerHTML = data.users.map(user => `
                                <a href="${user.profile_url}" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                                    <img src="${user.avatar_url}" alt="${user.name}" class="w-10 h-10 rounded-full object-cover">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800 truncate">${user.name}</p>
                                        <p class="text-sm text-gray-500 truncate">@${user.username}</p>
                                    </div>
                                </a>
                            `).join('');
                            searchSuggestions.innerHTML += `
                                <a href="/search?q=${encodeURIComponent(query)}" class="block p-3 text-center text-blue-600 hover:bg-blue-50 border-t border-gray-100 font-medium">
                                    See all results for "${query}"
                                </a>
                            `;
                            searchSuggestions.classList.remove('hidden');
                        } else {
                            searchSuggestions.innerHTML = `
                                <div class="p-4 text-center text-gray-500">
                                    No users found for "${query}"
                                </div>
                                <a href="/search?q=${encodeURIComponent(query)}" class="block p-3 text-center text-blue-600 hover:bg-blue-50 border-t border-gray-100 font-medium">
                                    Search for "${query}"
                                </a>
                            `;
                            searchSuggestions.classList.remove('hidden');
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                    }
                }, 300);
            });

            // Close suggestions when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#searchContainer')) {
                    searchSuggestions.classList.add('hidden');
                }
            });

            // Handle keyboard navigation
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    searchSuggestions.classList.add('hidden');
                }
            });
        }
        // ==================== END SEARCH FUNCTIONALITY ====================

        // Toggle profile menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden');
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', function (event) {
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
        document.getElementById('loginForm')?.addEventListener('submit', async function (e) {
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
        document.getElementById('registerForm')?.addEventListener('submit', async function (e) {
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
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeLoginModal();
                closeRegisterModal();
            }
        });

        // ========== NOTIFICATIONS ==========
        let notificationsOpen = false;

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            notificationsOpen = !notificationsOpen;

            if (notificationsOpen) {
                dropdown.classList.remove('hidden');
                loadNotifications();
            } else {
                dropdown.classList.add('hidden');
            }
        }

        function loadNotifications() {
            fetch('/notifications')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderNotifications(data.notifications);
                        updateBadge(data.unread_count);
                    }
                })
                .catch(err => console.error('Error loading notifications:', err));
        }

        function renderNotifications(notifications) {
            const list = document.getElementById('notificationList');

            if (!notifications || notifications.length === 0) {
                list.innerHTML = '<div class="p-8 text-center text-gray-500">No notifications yet</div>';
                return;
            }

            list.innerHTML = notifications.map(n => `
                <div class="flex items-start gap-3 p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 ${!n.read_at ? 'bg-blue-50' : ''}" 
                     onclick="handleNotificationClick(${n.id}, '${n.url || '/'}')">
                    <img src="${n.from_user.avatar_url}" alt="${n.from_user.name}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800">${n.message}</p>
                        <p class="text-xs text-gray-500 mt-1">${n.created_at}</p>
                    </div>
                    ${!n.read_at ? '<div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>' : ''}
                </div>
            `).join('');
        }

        function updateBadge(count) {
            const badge = document.getElementById('notificationBadge');
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        function handleNotificationClick(id, url) {
            // Mark as read
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(res => res.json()).then(response => {
                if (response.success) {
                    updateBadge(response.unread_count);
                }
            });

            // Navigate to the notification URL
            toggleNotifications();
            window.location.href = url;
        }

        function markAllNotificationsRead() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(res => res.json()).then(response => {
                if (response.success) {
                    updateBadge(0);
                    loadNotifications();
                }
            });
        }

        // Close notification dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const dropdown = document.getElementById('notificationDropdown');
            const btn = document.getElementById('notificationBtn');
            if (notificationsOpen && !dropdown?.contains(e.target) && !btn?.contains(e.target)) {
                dropdown?.classList.add('hidden');
                notificationsOpen = false;
            }
        });

        // Load initial unread count
        @auth
            fetch('/notifications/unread-count')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateBadge(data.count);
                    }
                });
        @endauth
    </script>

    @auth
        <!-- Pusher/Echo Real-time Notifications -->
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script>
                                                                                // Initialize Pusher for real-time notifications
                                                                                const pusherKey = '{{ env("PUSHER_APP_KEY") }}';
            const pusherCluster = '{{ env("PUSHER_APP_CLUSTER", "mt1") }}';

            if (pusherKey && pusherKey !== '') {
                window.pusher = new Pusher(pusherKey, {
                    cluster: pusherCluster,
                    forceTLS: true,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }
                });

                // Notification sound - using base64 encoded audio
                const notificationSoundData = 'data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU' + 'tvT19' + 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

                // Create audio element for notification sound
                let audioContext = null;
                let audioUnlocked = false;

                // Unlock audio on first user interaction
                document.addEventListener('click', function unlockAudio() {
                    if (!audioUnlocked) {
                        audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        if (audioContext.state === 'suspended') {
                            audioContext.resume();
                        }
                        audioUnlocked = true;
                        console.log('Audio unlocked');
                    }
                }, { once: false });

                function playNotificationSound() {
                    try {
                        // Use Web Audio API if unlocked
                        if (audioUnlocked && audioContext) {
                            const now = audioContext.currentTime;

                            // Classic bell/chime notification sound
                            const frequencies = [523.25, 659.25, 783.99]; // C5, E5, G5 - major chord

                            frequencies.forEach((freq, i) => {
                                const oscillator = audioContext.createOscillator();
                                const gainNode = audioContext.createGain();

                                oscillator.connect(gainNode);
                                gainNode.connect(audioContext.destination);

                                oscillator.frequency.value = freq;
                                oscillator.type = 'sine';

                                // Staggered start for chime effect
                                const startTime = now + (i * 0.08);
                                gainNode.gain.setValueAtTime(0, startTime);
                                gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.02);
                                gainNode.gain.exponentialRampToValueAtTime(0.001, startTime + 0.5);

                                oscillator.start(startTime);
                                oscillator.stop(startTime + 0.5);
                            });
                            return;
                        }

                        // Fallback - create simple beep
                        const tempContext = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = tempContext.createOscillator();
                        const gain = tempContext.createGain();
                        osc.connect(gain);
                        gain.connect(tempContext.destination);
                        osc.frequency.value = 660;
                        osc.type = 'sine';
                        gain.gain.setValueAtTime(0.3, tempContext.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, tempContext.currentTime + 0.3);
                        osc.start();
                        osc.stop(tempContext.currentTime + 0.3);
                    } catch (e) {
                        console.log('Could not play notification sound:', e);
                    }
                }

                // Make function globally accessible for testing
                window.playNotificationSound = playNotificationSound;

                // Subscribe to private notifications channel
                const channel = window.pusher.subscribe('private-notifications.{{ auth()->id() }}');

                channel.bind('notification.new', function (data) {
                    console.log('New notification received:', data);

                    // Play notification sound
                    if (data.type === 'message' &&
                        window.CURRENT_CONVERSATION_ID == data.data.conversation_id) {
                        return;
                    }

                    playNotificationSound();

                    // Update badge count
                    const badge = document.getElementById('notificationBadge');
                    let currentCount = parseInt(badge.textContent) || 0;
                    updateBadge(currentCount + 1);

                    // Show toast notification
                    showNotificationToast(data);

                    // Reload notifications list if dropdown is open
                    if (notificationsOpen) {
                        loadNotifications();
                    }
                });

                channel.bind('pusher:subscription_error', function (status) {
                    console.error('Pusher subscription error:', status);
                });
                // handle frindship notifications

            }


            // Toast notification
            function showNotificationToast(data) {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 max-w-sm z-50 animate-slide-in cursor-pointer hover:bg-gray-50 transition-colors';
                toast.innerHTML = `
                                                                                        <div class="flex items-start gap-3">
                                                                                            <img src="${data.from_user.avatar_url}" alt="${data.from_user.name}" class="w-10 h-10 rounded-full object-cover">
                                                                                            <div class="flex-1">
                                                                                                <p class="font-medium text-gray-800">${data.from_user.name}</p>
                                                                                                <p class="text-sm text-gray-600">${data.message}</p>
                                                                                                <p class="text-xs text-blue-500 mt-1">Click to view</p>
                                                                                            </div>
                                                                                            <button class="toast-close text-gray-400 hover:text-gray-600">
                                                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                                                </svg>
                                                                                            </button>
                                                                                        </div>
                                                                                    `;

                // Click to navigate to notification URL
                toast.addEventListener('click', function (e) {
                    if (!e.target.closest('.toast-close')) {
                        // Mark as read and navigate
                        fetch(`/notifications/${data.id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        window.location.href = data.url || '/';
                    }
                });

                // Close button
                toast.querySelector('.toast-close').addEventListener('click', function (e) {
                    e.stopPropagation();
                    toast.remove();
                });

                document.body.appendChild(toast);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    toast.classList.add('animate-fade-out');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            }


            // ====== FRIEND ACTION HELPERS ======
            function _getCsrf() {
                return document.querySelector('meta[name="csrf-token"]').content;
            }

            function sendFriendRequest(userId) {
                const container = document.getElementById(`user-actions-${userId}`);
                if (container) {
                    container.innerHTML = `<button disabled class="px-4 py-2 bg-blue-400 text-white rounded-lg opacity-70 cursor-not-allowed">Sending...</button>`;
                }
                fetch(`/friends/send/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _getCsrf(), 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (!data.success) {
                        renderFriendActions('none', userId, '');
                    }
                    // Success: Pusher event will update the button in real-time
                }).catch(() => renderFriendActions('none', userId, ''));
            }

            function acceptFriendRequest(friendshipId, friendId) {
                const container = document.getElementById(`user-actions-${friendId}`);
                if (container) {
                    container.innerHTML = `<button disabled class="px-4 py-2 bg-green-400 text-white rounded-lg opacity-70 cursor-not-allowed">Accepting...</button>`;
                }
                fetch(`/friends/${friendshipId}/accept`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _getCsrf(), 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (!data.success) {
                        renderFriendActions('pending_received', friendId, friendshipId);
                    }
                    // Success: Pusher event will update the button
                }).catch(() => renderFriendActions('pending_received', friendId, friendshipId));
            }

            function rejectFriendRequest(friendshipId, friendId) {
                const container = document.getElementById(`user-actions-${friendId}`);
                if (container) {
                    container.innerHTML = `<button disabled class="px-4 py-2 bg-gray-300 text-gray-500 rounded-lg opacity-70 cursor-not-allowed">Declining...</button>`;
                }
                fetch(`/friends/${friendshipId}/reject`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _getCsrf(), 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (!data.success) {
                        renderFriendActions('pending_received', friendId, friendshipId);
                    }
                }).catch(() => renderFriendActions('pending_received', friendId, friendshipId));
            }

            function cancelFriendRequest(friendshipId, friendId) {
                const container = document.getElementById(`user-actions-${friendId}`);
                if (container) {
                    container.innerHTML = `<button disabled class="px-4 py-2 bg-gray-300 text-gray-500 rounded-lg opacity-70 cursor-not-allowed">Cancelling...</button>`;
                }
                fetch(`/friends/${friendshipId}/cancel`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _getCsrf(), 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (!data.success) {
                        renderFriendActions('pending_sent', friendId, friendshipId);
                    }
                }).catch(() => renderFriendActions('pending_sent', friendId, friendshipId));
            }

            function removeFriend(friendId) {
                const container = document.getElementById(`user-actions-${friendId}`);
                if (container) {
                    container.innerHTML = `<button disabled class="px-4 py-2 bg-gray-300 text-gray-500 rounded-lg opacity-70 cursor-not-allowed">Removing...</button>`;
                }
                fetch(`/friends/${friendId}/remove`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _getCsrf(), 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (!data.success) {
                        renderFriendActions('friends', friendId, '');
                    }
                }).catch(() => renderFriendActions('friends', friendId, ''));
            }

            /**
             * Render the friendship action buttons inside #user-actions-{friendId}.
             * @param {string} status  'none' | 'pending_sent' | 'pending_received' | 'friends'
             * @param {number|string} friendId   Profile user's ID
             * @param {number|string} friendshipId  Friendship record ID (needed for accept/reject/cancel)
             */
            function renderFriendActions(status, friendId, friendshipId) {
                const container = document.getElementById(`user-actions-${friendId}`);
                if (!container) return;

                // Update stored data so Pusher can re-use them
                container.dataset.initialStatus = status;
                if (friendshipId) container.dataset.friendshipId = friendshipId;

                switch (status) {
                    case 'friends':
                        container.innerHTML = `
                                                                                                <button onclick="removeFriend(${friendId})"
                                                                                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors font-medium">
                                                                                                    Friends
                                                                                                </button>
                                                                                                <button onclick="sendMessage(${friendId})"
                                                                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                                                                                    Message
                                                                                                </button>       
                                                                                            `
                            ;
                        break;

                    case 'pending_sent':
                        container.innerHTML = `
                                                                                                <button onclick="cancelFriendRequest(${friendshipId}, ${friendId})"
                                                                                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                                                                                    Cancel Request
                                                                                                </button>
                                                                                                <button onclick="sendMessage(${friendId})"
                                                                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                                                                                    Message
                                                                                                </button>       

                                                                                            `;
                        break;

                    case 'pending_received':
                        container.innerHTML = `
                                                                                                <button onclick="acceptFriendRequest(${friendshipId}, ${friendId})"
                                                                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                                                                                    Accept
                                                                                                </button>
                                                                                                <button onclick="rejectFriendRequest(${friendshipId}, ${friendId})"
                                                                                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium ml-2">
                                                                                                    Decline
                                                                                                </button>
                                                                                            `;
                        break;

                    default: // 'none'
                        container.innerHTML = `
                                                                                                <button onclick="sendFriendRequest(${friendId})"
                                                                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                                                                                    Add Friend
                                                                                                </button>
                                                                                                <button onclick="sendMessage(${friendId})"
                                                                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                                                                                    Message
                                                                                                </button>   
                                                                                            `;
                }
            }

            // Initialise all user-action containers immediately (DOM is ready at this point)
            (function initFriendButtons() {
                document.querySelectorAll('[id^="user-actions-"]').forEach(el => {
                    const status = el.dataset.initialStatus || 'none';
                    const friendId = el.dataset.friendId;
                    const friendshipId = el.dataset.friendshipId || '';
                    renderFriendActions(status, friendId, friendshipId);
                });
            })();

            // ====== PUSHER: FRIENDSHIP REAL-TIME UPDATES ======
            // Run immediately — DOM is already parsed at this point in the body
            (function setupFriendshipChannel() {
                if (typeof window.pusher === 'undefined') return;

                console.log('Setting up friendship Pusher channel for user:', CURRENT_USER_ID);

                try {
                    const friendshipChannel = window.pusher.subscribe(`private-friendships.${CURRENT_USER_ID}`);

                    friendshipChannel.bind('pusher:subscription_succeeded', function () {
                        console.log('✅ Subscribed to private-friendships.' + CURRENT_USER_ID);
                    });

                    friendshipChannel.bind('pusher:subscription_error', function (error) {
                        console.error('❌ Friendship channel subscription error:', error);
                    });

                    friendshipChannel.bind('friendship.updated', function (data) {
                        console.log('📡 Friendship event received:', data);

                        const { status, sender_id, receiver_id, from_user } = data;

                        // Find which profile-user card is on this page
                        const otherUserId = sender_id === CURRENT_USER_ID ? receiver_id : sender_id;
                        const container = document.getElementById(`user-actions-${otherUserId}`);

                        if (container) {
                            let resolvedStatus;

                            if (status === 'friends') {
                                resolvedStatus = 'friends';
                            } else if (status === 'pending') {
                                resolvedStatus = (sender_id === CURRENT_USER_ID) ? 'pending_sent' : 'pending_received';
                            } else {
                                resolvedStatus = 'none';
                            }

                            const friendshipId = container.dataset.friendshipId || '';
                            renderFriendActions(resolvedStatus, otherUserId, friendshipId);
                        }

                        // Show toast only when the OTHER person accepted YOUR request
                        if (status === 'friends' && from_user && from_user.id !== CURRENT_USER_ID) {
                            showNotificationToast({
                                id: null,
                                from_user: from_user,
                                message: from_user.name + ' accepted your friend request 🎉',
                                url: '/friends'
                            });
                            if (notificationsOpen) loadNotifications();
                        }
                    });

                } catch (error) {
                    console.error('Error setting up friendship Pusher channel:', error);
                }
            })();

        </script>

        <style>
            @keyframes slide-in {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes fade-out {
                from {
                    opacity: 1;
                }

                to {
                    opacity: 0;
                }
            }

            .animate-slide-in {
                animation: slide-in 0.3s ease-out;
            }

            .animate-fade-out {
                animation: fade-out 0.3s ease-out;
            }
        </style>
    @endauth

    @auth
        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- POPUP CHAT CONTAINER + ENGINE --}}
        {{-- ═══════════════════════════════════════════════════ --}}

        {{-- Popup dock: bottom-right, stacks horizontally --}}
        <div id="chatPopupDock" class="fixed bottom-0 right-4 flex items-end gap-2 z-[200]" style="pointer-events:none;">
        </div>

        <style>
            /* ── Popup chat window ── */
            .chat-popup {
                width: 320px;
                height: 440px;
                display: flex;
                flex-direction: column;
                background: #fff;
                border-radius: 16px 16px 0 0;
                box-shadow: 0 -4px 32px rgba(0, 0, 0, .15);
                border: 1px solid #e5e7eb;
                border-bottom: none;
                pointer-events: all;
                animation: popupSlideUp 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
                overflow: hidden;
                transition: height .2s ease;
            }

            .chat-popup.minimized {
                height: 52px;
            }

            @keyframes popupSlideUp {
                from {
                    transform: translateY(60px);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            /* ── Popup header ── */
            .popup-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 8px 10px;
                background: linear-gradient(135deg, #2563eb, #4f46e5);
                cursor: pointer;
                user-select: none;
                flex-shrink: 0;
                min-height: 52px;
            }

            .popup-header-left {
                display: flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
            }

            .popup-avatar-wrap {
                position: relative;
                flex-shrink: 0;
            }

            .popup-avatar-img {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid rgba(255, 255, 255, .4);
            }

            .popup-online-dot {
                position: absolute;
                bottom: 0;
                right: 0;
                width: 9px;
                height: 9px;
                background: #22c55e;
                border-radius: 50%;
                border: 2px solid #fff;
            }

            .popup-header-info {
                min-width: 0;
            }

            .popup-name {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #fff;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 150px;
            }

            .popup-typing-label {
                display: none;
                font-size: 10px;
                color: rgba(255, 255, 255, .75);
                font-style: italic;
            }

            .popup-header-actions {
                display: flex;
                align-items: center;
                gap: 2px;
                flex-shrink: 0;
            }

            .popup-action-btn {
                background: none;
                border: none;
                cursor: pointer;
                color: rgba(255, 255, 255, .8);
                padding: 4px;
                border-radius: 6px;
                transition: background .15s, color .15s;
            }

            .popup-action-btn:hover {
                background: rgba(255, 255, 255, .15);
                color: #fff;
            }

            /* ── Messages area ── */
            .popup-messages {
                flex: 1;
                overflow-y: auto;
                padding: 12px;
                display: flex;
                flex-direction: column;
                gap: 8px;
                background: #f9fafb;
            }

            .popup-messages::-webkit-scrollbar {
                width: 3px;
            }

            .popup-messages::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 3px;
            }

            .popup-loading,
            .popup-empty {
                text-align: center;
                font-size: 12px;
                color: #9ca3af;
                padding: 16px;
            }

            /* ── Message bubbles ── */
            .popup-msg {
                max-width: 85%;
            }

            .popup-msg.mine {
                align-self: flex-end;
            }

            .popup-msg.other {
                align-self: flex-start;
            }

            .popup-bubble {
                padding: 8px 12px;
                border-radius: 16px;
                font-size: 13px;
                line-height: 1.4;
                word-break: break-word;
            }

            .popup-msg.mine .popup-bubble {
                background: linear-gradient(135deg, #2563eb, #4f46e5);
                color: #fff;
                border-bottom-right-radius: 4px;
            }

            .popup-msg.other .popup-bubble {
                background: #fff;
                color: #1f2937;
                border: 1px solid #e5e7eb;
                border-bottom-left-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
            }

            .popup-time {
                font-size: 10px;
                opacity: .6;
                margin-top: 2px;
                text-align: right;
            }

            .popup-msg.other .popup-time {
                text-align: left;
            }

            /* ── Input bar ── */
            .popup-input-bar {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 8px 10px;
                background: #fff;
                border-top: 1px solid #f3f4f6;
                flex-shrink: 0;
            }

            .popup-input {
                flex: 1;
                padding: 7px 12px;
                font-size: 13px;
                background: #f3f4f6;
                border: 1.5px solid transparent;
                border-radius: 20px;
                outline: none;
                font-family: inherit;
                transition: border-color .2s, background .2s;
            }

            .popup-input:focus {
                background: #fff;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
            }

            .popup-send-btn {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                flex-shrink: 0;
                background: linear-gradient(135deg, #2563eb, #4f46e5);
                color: #fff;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform .15s, box-shadow .15s;
                box-shadow: 0 2px 8px rgba(37, 99, 235, .35);
            }

            .popup-send-btn:hover {
                transform: scale(1.1);
            }

            .popup-send-btn:active {
                transform: scale(.9);
            }
        </style>

        <script src="{{ asset('js/chat/PopupManager.js') }}"></script>
        <script>
            (function () {
                'use strict';

                const CSRF = document.querySelector('meta[name="csrf-token"]').content;
                const ME = {{ auth()->id() }};

                /* ── 1. Instantiate PopupManager ── */
                const _pm = new PopupManager({
                    currentUserId: ME,
                    csrf: CSRF,
                    maxPopups: 3,
                });

                /* ── 2. Global bridge used by onclick handlers in PopupManager HTML ── */
                window.ChatPopupEngine = {
                    toggle: (id) => _pm.toggle(id),
                    close: (id) => _pm.close(id),
                    send: (id) => _pm.send(id),
                };

                /* ── 3. Global sendMessage() — called from profile/friends page buttons ── */
                window.sendMessage = function (userId) {
                    const actionsDiv = document.getElementById(`user-actions-${userId}`);
                    let userName = actionsDiv?.dataset.friendName || null;
                    let userAvatar = actionsDiv?.dataset.friendAvatar || null;

                    if (!userName) {
                        const h1 = document.querySelector('h1.text-2xl, h1.font-bold');
                        userName = h1 ? h1.textContent.trim() : 'User';
                    }
                    if (!userAvatar) {
                        const avatarEl = document.querySelector('img#profilePictureImage')
                            || document.querySelector('.profile-picture img');
                        userAvatar = avatarEl ? avatarEl.src : null;
                    }

                    _pm.openByUserId(userId, userName, userAvatar);
                };

                /* ── 4. Backwards-compat alias (used in old onclick attributes) ── */
                window.openChatPopup = (userId, userName, userAvatar) =>
                    _pm.openByUserId(userId, userName, userAvatar);

                /* ── 5. Unread badge helper ── */
                function updateChatBadge(count) {
                    ['chatUnreadBadge', 'sidebarChatBadge'].forEach(id => {
                        const b = document.getElementById(id);
                        if (!b) return;
                        if (count > 0) {
                            b.textContent = count > 99 ? '99+' : count;
                            b.classList.remove('hidden');
                            b.classList.add('flex');
                        } else {
                            b.classList.add('hidden');
                            b.classList.remove('flex');
                        }
                    });
                }

                /* ── 6. Load initial unread count ── */
                @if(auth()->check())
                    fetch('/chat/unread-count')
                        .then(r => r.json())
                        .then(d => { if (d.success) updateChatBadge(d.count); })
                        .catch(() => { });
                @endif

                        /* ── 7. Listen on notification channel for message events & auto-open popup ── */
                        if (typeof window.pusher !== 'undefined') {
                    const userCh = window.pusher.channel('private-notifications.{{ auth()->id() }}');
                    if (userCh) {
                        // Chat badge update
                        userCh.bind('chat.unread', (data) => updateChatBadge(data.count));

                        // Auto-open popup when a message arrives and user is NOT in that conversation
                        // Payload shape from NewNotification::broadcastWith():
                        //   { id, type, message, data: { conversation_id, message_id }, url, from_user, ... }
                        userCh.bind('notification.new', (payload) => {
                            try {
                                if (payload.type !== 'message') return;

                                // The nested meta lives under 'data', not 'meta'
                                const meta = payload.data || {};
                                const fromUser = payload.from_user;
                                const convId = meta.conversation_id;

                                if (!convId || !fromUser) return;

                                // Don't open popup if user is already viewing that conversation
                                if (window.CURRENT_CONVERSATION_ID == convId) return;

                                _pm.onIncomingMessage(
                                    convId,
                                    {
                                        id: meta.message_id ?? null,
                                        sender_id: fromUser.id,
                                        body: payload.message || '…',
                                        created_at: new Date().toISOString(),
                                    },
                                    fromUser.name,
                                    fromUser.avatar_url
                                );
                            } catch (e) {
                                console.warn('[ChatPopup] notification.new handler error:', e, payload);
                            }
                        });
                    }
                }

            })();
        </script>
    @endauth

    <script src="{{ asset('js/modules/mentions.js') }}"></script>
    @stack('scripts')
</body>

</html>