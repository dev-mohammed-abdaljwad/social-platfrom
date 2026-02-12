<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post Card</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-8 px-4">
    <div class="max-w-md mx-auto">
        <!-- Create Post Card -->
        <div class="bg-white rounded-lg shadow-md p-4 space-y-4">
            <!-- Header with Avatar -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-sm">JD</span>
                </div>
                <div class="flex-1">
                    <input 
                        type="text" 
                        placeholder="What's on your mind?" 
                        class="w-full bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            <!-- Textarea for longer posts -->
            <div class="relative">
                <textarea 
                    placeholder="Write something..." 
                    class="w-full h-24 p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none text-sm"
                ></textarea>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex flex-wrap gap-2 justify-between items-center py-2 border-t border-gray-100">
                <div class="flex gap-2">
                    <!-- Image Upload Button -->
                    <button class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path>
                        </svg>
                        <span class="hidden sm:inline">Photo</span>
                    </button>

                    <!-- Video Upload Button -->
                    <button class="flex items-center gap-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                        </svg>
                        <span class="hidden sm:inline">Video</span>
                    </button>
                </div>

                <!-- Privacy Selector -->
                <div class="relative group">
                    <button class="flex items-center gap-1 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 7H7v6h6V7z"></path>
                            <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2V2a1 1 0 112 0v1h1a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2h1a2 2 0 01-2 2h-1v1a1 1 0 11-2 0v-1h-2v1a1 1 0 11-2 0v-1H7a2 2 0 01-2-2v-1H4a1 1 0 110-2h1V9H4a1 1 0 110-2h1V5a2 2 0 012-2h1V2zM9 5H7v2h2V5zm0 4H7v2h2V9zm4-4h-2v2h2V5zm0 4h-2v2h2V9z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="hidden sm:inline">Public</span>
                    </button>

                    <!-- Privacy Dropdown Menu -->
                    <div class="absolute right-0 mt-0 w-40 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                        <button class="w-full flex items-center gap-2 px-4 py-2 hover:bg-gray-100 text-gray-800 text-sm">
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Public
                        </button>
                        <button class="w-full flex items-center gap-2 px-4 py-2 hover:bg-gray-100 text-gray-800 text-sm">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                            </svg>
                            Friends
                        </button>
                        <button class="w-full flex items-center gap-2 px-4 py-2 hover:bg-gray-100 text-gray-800 text-sm">
                            <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Private
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button class="w-full bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                Post
            </button>
        </div>
    </div>
</body>
</html>