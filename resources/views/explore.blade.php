@extends('layouts.app')

@section('title', 'Explore - SocialTime')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Search Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Explore</h1>
            <div class="relative">
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Search posts, people, or topics..." 
                    class="w-full px-4 py-3 pl-12 bg-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors"
                >
                <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Categories -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <button class="category-btn px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium whitespace-nowrap" data-category="all">
                All
            </button>
            <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-200 transition-colors" data-category="people">
                People
            </button>
            <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-200 transition-colors" data-category="posts">
                Posts
            </button>
            <button class="category-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-200 transition-colors" data-category="trending">
                Trending
            </button>
        </div>

        <!-- Trending Topics -->
        <div id="trendingSection" class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Trending Topics</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div id="trendingList" class="space-y-3">
                    <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div>
                            <span class="text-xs text-gray-500">Trending</span>
                            <h3 class="font-medium text-gray-800">#WebDevelopment</h3>
                            <span class="text-sm text-gray-500">1.2K posts</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div>
                            <span class="text-xs text-gray-500">Trending</span>
                            <h3 class="font-medium text-gray-800">#Laravel</h3>
                            <span class="text-sm text-gray-500">856 posts</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <a href="#" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div>
                            <span class="text-xs text-gray-500">Trending</span>
                            <h3 class="font-medium text-gray-800">#TechNews</h3>
                            <span class="text-sm text-gray-500">654 posts</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- People to Follow -->
        <div id="peopleSection" class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">People to Follow</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div id="peopleList" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse p-4 flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Posts -->
        <div id="postsSection">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Popular Posts</h2>
            <div id="postsList" class="space-y-4">
                <!-- Loading skeleton -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 animate-pulse">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/6 mb-4"></div>
                            <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results (hidden by default) -->
        <div id="searchResults" class="hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Search Results</h2>
            <div id="resultsList" class="space-y-4"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/modules/explore.js') }}"></script>
@endpush
