<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\CommentController;
use App\Http\Controllers\Web\FriendController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ReactionController;
use App\Http\Controllers\Web\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

    // Login & Register POST routes with rate limiting + honeypot protection
    Route::middleware(['throttle:auth', \App\Http\Middleware\HoneypotMiddleware::class])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/explore', [PageController::class, 'explore'])->name('explore');
Route::get('/posts/feed', [PageController::class, 'fetchPosts'])->name('posts.feed');

// Search routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Profile routes
Route::get('/profile', [PageController::class, 'profile'])->name('profile');
Route::get('/profile/{user}', [PageController::class, 'showProfile'])->name('profile.show');

// Public: Get comments for a post
// Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->name('comments.index');

// Public: Get likes for a post
// Route::get('/posts/{post}/likes', [PostController::class, 'getLikes'])->name('posts.likes');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');
    Route::post('/posts/{post}/save', [PostController::class, 'toggleSave'])->name('posts.save');
    Route::post('/posts/{postId}/react', [ReactionController::class, 'reactToPost'])->middleware('auth')->name('posts.react');    // Shares
    Route::post('/comments/{comment}/react', [ReactionController::class, 'reactToComment'])->name('comments.react');
    Route::put('/shares/{share}', [PostController::class, 'updateShare'])->name('shares.update');
    Route::delete('/shares/{share}', [PostController::class, 'destroyShare'])->name('shares.destroy');

    // Saved Posts Page
    Route::get('/saved', [PageController::class, 'saved'])->name('saved');

    // Comments
        Route::get('/posts/{post}/comments', [CommentController::class, 'getComments'])->name('comments.index');
    
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/react', [CommentController::class, 'reactToComment'])->name('comments.react');

    // Pages
    Route::get('/friends', [PageController::class, 'friends'])->name('friends');
    Route::get('/settings', [PageController::class, 'settings'])->name('settings');

    // Friendship actions
    Route::post('/friends/{friendship}/accept', [FriendController::class, 'accept'])->name('friends.accept');
    Route::post('/friends/{friendship}/reject', [FriendController::class, 'reject'])->name('friends.reject');
    Route::post('/friends/{friendship}/cancel', [FriendController::class, 'cancel'])->name('friends.cancel');
    Route::post('/friends/{user}/remove', [FriendController::class, 'remove'])->name('friends.remove');

    // Send friend request with rate limiting (20/min per user)
    Route::middleware('throttle:friend-request')->group(function () {
        Route::post('/friends/{user}/send', [FriendController::class, 'send'])->name('friends.send');
    });

    // Profile settings
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'deleteAccount'])->name('profile.delete');

    // Profile image uploads
    Route::post('/profile/picture', [ProfileController::class, 'updateProfilePicture'])->name('profile.picture.update');
    Route::post('/profile/cover', [ProfileController::class, 'updateCoverPhoto'])->name('profile.cover.update');
    Route::delete('/profile/picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::delete('/profile/cover', [ProfileController::class, 'removeCoverPhoto'])->name('profile.cover.remove');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});


Route::get('/logo', function () {
    return view('welcome');
});
