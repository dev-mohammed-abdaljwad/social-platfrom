<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Feed</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .reaction-button {
            transition: all 0.2s ease;
        }

        .reaction-button:hover {
            transform: scale(1.1);
        }

        .reaction-button.active {
            color: #ec4899;
            font-weight: 600;
        }

        .like-button:hover {
            color: #ef4444;
        }

        .love-button:hover {
            color: #ec4899;
        }

        .haha-button:hover {
            color: #f97316;
        }

        .wow-button:hover {
            color: #eab308;
        }

        .sad-button:hover {
            color: #06b6d4;
        }

        .angry-button:hover {
            color: #dc2626;
        }

        .post-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(71, 85, 105, 0.5);
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }

        .avatar.alt {
            background: linear-gradient(135deg, #ec4899, #f97316);
        }

        .media-preview {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.3);
            border-radius: 8px;
        }

        .video-play {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .media-preview:hover .video-play {
            background: rgba(255, 255, 255, 1);
            transform: translate(-50%, -50%) scale(1.1);
        }

        .location-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 6px;
            font-size: 12px;
            color: #60a5fa;
        }

        .text-balance {
            text-wrap: balance;
        }

        @media (max-width: 768px) {
            .avatar {
                width: 40px;
                height: 40px;
            }

            .post-card {
                margin: 12px;
                border-radius: 12px;
            }
        }
    </style>
</head>
<body class="min-h-screen py-4 md:py-8">
    <div class="max-w-2xl mx-auto px-0 md:px-4">
        <!-- Post 1: Image Post -->
        <div class="post-card rounded-lg md:rounded-xl p-4 md:p-6 mb-4 md:mb-6 shadow-lg">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="avatar flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm md:text-base font-semibold text-white truncate">Sarah Anderson</h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-xs md:text-sm text-gray-400">2 hours ago</p>
                            <span class="location-tag">
                                <span>📍</span>
                                San Francisco, CA
                            </span>
                        </div>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-white text-lg flex-shrink-0">⋮</button>
            </div>

            <!-- Post Content -->
            <p class="text-white text-sm md:text-base leading-relaxed mb-4 text-balance">
                Just finished an amazing sunset hike at Twin Peaks! The views were absolutely breathtaking. Who else loves weekend adventures? 🌄✨
            </p>

            <!-- Image Preview -->
            <div class="media-preview mb-4 overflow-hidden">
                <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center relative">
                    <svg class="w-12 h-12 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                    </svg>
                </div>
            </div>

            <!-- Engagement Summary -->
            <div class="flex items-center justify-between text-xs md:text-sm text-gray-400 mb-4 pb-4 border-b border-gray-700">
                <div class="flex items-center gap-2">
                    <span>❤️ 👍 😊</span>
                    <span>1.2K reactions</span>
                </div>
                <div class="flex gap-4">
                    <span>342 comments</span>
                    <span>89 shares</span>
                </div>
            </div>

            <!-- Reaction Buttons -->
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 mb-4">
                <button class="reaction-button like-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">👍</span>
                    <span class="hidden md:inline">Like</span>
                </button>
                <button class="reaction-button love-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">❤️</span>
                    <span class="hidden md:inline">Love</span>
                </button>
                <button class="reaction-button haha-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😊</span>
                    <span class="hidden md:inline">Haha</span>
                </button>
                <button class="reaction-button wow-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😮</span>
                    <span class="hidden md:inline">Wow</span>
                </button>
                <button class="reaction-button sad-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😢</span>
                    <span class="hidden md:inline">Sad</span>
                </button>
                <button class="reaction-button angry-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😠</span>
                    <span class="hidden md:inline">Angry</span>
                </button>
            </div>

            <!-- Comments Section -->
            <button class="w-full text-left text-sm md:text-base text-blue-400 hover:text-blue-300 transition font-medium">
                View all 342 comments →
            </button>
        </div>

        <!-- Post 2: Video Post -->
        <div class="post-card rounded-lg md:rounded-xl p-4 md:p-6 mb-4 md:mb-6 shadow-lg">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="avatar alt flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm md:text-base font-semibold text-white truncate">Marcus Chen</h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-xs md:text-sm text-gray-400">4 hours ago</p>
                            <span class="location-tag">
                                <span>📍</span>
                                New York, NY
                            </span>
                        </div>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-white text-lg flex-shrink-0">⋮</button>
            </div>

            <!-- Post Content -->
            <p class="text-white text-sm md:text-base leading-relaxed mb-4 text-balance">
                Check out this amazing coffee art! ☕ The barista at this new café deserves a standing ovation. Have you tried latte art before?
            </p>

            <!-- Video Preview -->
            <div class="media-preview mb-4 overflow-hidden relative group cursor-pointer">
                <div class="aspect-video bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center relative">
                    <svg class="w-12 h-12 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                    </svg>
                    <div class="video-play">
                        <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Engagement Summary -->
            <div class="flex items-center justify-between text-xs md:text-sm text-gray-400 mb-4 pb-4 border-b border-gray-700">
                <div class="flex items-center gap-2">
                    <span>❤️ 👍</span>
                    <span>856 reactions</span>
                </div>
                <div class="flex gap-4">
                    <span>124 comments</span>
                    <span>43 shares</span>
                </div>
            </div>

            <!-- Reaction Buttons -->
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 mb-4">
                <button class="reaction-button like-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">👍</span>
                    <span class="hidden md:inline">Like</span>
                </button>
                <button class="reaction-button love-button active flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-sm md:text-base">
                    <span class="text-base">❤️</span>
                    <span class="hidden md:inline">Love</span>
                </button>
                <button class="reaction-button haha-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😊</span>
                    <span class="hidden md:inline">Haha</span>
                </button>
                <button class="reaction-button wow-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😮</span>
                    <span class="hidden md:inline">Wow</span>
                </button>
                <button class="reaction-button sad-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😢</span>
                    <span class="hidden md:inline">Sad</span>
                </button>
                <button class="reaction-button angry-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😠</span>
                    <span class="hidden md:inline">Angry</span>
                </button>
            </div>

            <!-- Comments Section -->
            <button class="w-full text-left text-sm md:text-base text-blue-400 hover:text-blue-300 transition font-medium">
                View all 124 comments →
            </button>
        </div>

        <!-- Post 3: Text-only Post -->
        <div class="post-card rounded-lg md:rounded-xl p-4 md:p-6 shadow-lg">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="avatar flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm md:text-base font-semibold text-white truncate">Elena Rodriguez</h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-xs md:text-sm text-gray-400">Just now</p>
                            <span class="location-tag">
                                <span>📍</span>
                                Austin, TX
                            </span>
                        </div>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-white text-lg flex-shrink-0">⋮</button>
            </div>

            <!-- Post Content -->
            <p class="text-white text-sm md:text-base leading-relaxed mb-4 text-balance">
                Excited to announce that I just launched my new project! 🎉 It's been months of hard work, and I can't wait to share it with the community. Special thanks to everyone who supported me along the way. Follow along for updates and behind-the-scenes content!
            </p>

            <!-- Engagement Summary -->
            <div class="flex items-center justify-between text-xs md:text-sm text-gray-400 mb-4 pb-4 border-b border-gray-700">
                <div class="flex items-center gap-2">
                    <span>❤️ 👍 😮</span>
                    <span>2.1K reactions</span>
                </div>
                <div class="flex gap-4">
                    <span>567 comments</span>
                    <span>234 shares</span>
                </div>
            </div>

            <!-- Reaction Buttons -->
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 mb-4">
                <button class="reaction-button like-button active flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-sm md:text-base">
                    <span class="text-base">👍</span>
                    <span class="hidden md:inline">Like</span>
                </button>
                <button class="reaction-button love-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">❤️</span>
                    <span class="hidden md:inline">Love</span>
                </button>
                <button class="reaction-button haha-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😊</span>
                    <span class="hidden md:inline">Haha</span>
                </button>
                <button class="reaction-button wow-button active flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-sm md:text-base">
                    <span class="text-base">😮</span>
                    <span class="hidden md:inline">Wow</span>
                </button>
                <button class="reaction-button sad-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😢</span>
                    <span class="hidden md:inline">Sad</span>
                </button>
                <button class="reaction-button angry-button flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-800 transition text-gray-300 text-sm md:text-base">
                    <span class="text-base">😠</span>
                    <span class="hidden md:inline">Angry</span>
                </button>
            </div>

            <!-- Comments Section -->
            <button class="w-full text-left text-sm md:text-base text-blue-400 hover:text-blue-300 transition font-medium">
                View all 567 comments →
            </button>
        </div>
    </div>
</body>
</html>
