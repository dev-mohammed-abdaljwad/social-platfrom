<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\FriendshipController;
use App\Http\Controllers\Api\V1\ShareController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded within "api" middleware group.
|
*/

// API Version 1
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes (Public)
    |--------------------------------------------------------------------------
    */
    // Auth routes with rate limiting + honeypot protection
    Route::middleware(['throttle:auth', \App\Http\Middleware\HoneypotMiddleware::class])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
        });
    });
    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Require Authentication)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        // User routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/profile', [AuthController::class, 'me']);
            Route::put('/profile', [UserController::class, 'updateProfile']);
            Route::post('/profile/picture', [UserController::class, 'updateProfilePicture']);
            Route::get('/username/{username}', [UserController::class, 'showByUsername']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::get('/{id}/posts', [PostController::class, 'userPosts']);
        });

        // Post routes
        Route::prefix('posts')->group(function () {
            Route::get('/feed', [PostController::class, 'feed']);
            Route::get('/', [PostController::class, 'index']);
            Route::post('/', [PostController::class, 'store']);
            Route::get('/{id}', [PostController::class, 'show']);
            Route::put('/{id}', [PostController::class, 'update']);
            Route::delete('/{id}', [PostController::class, 'destroy']);

            // Post likes
            Route::get('/{postId}/likes', [LikeController::class, 'getPostLikes']);
            Route::post('/{postId}/like', [LikeController::class, 'togglePostLike']);
            Route::get('/{postId}/liked', [LikeController::class, 'hasLikedPost']);

            // Post comments
            Route::get('/{postId}/comments', [CommentController::class, 'index']);
            Route::post('/{postId}/comments', [CommentController::class, 'store']);
            Route::get('/{postId}/comments/{commentId}', [CommentController::class, 'show']);
            Route::put('/{postId}/comments/{commentId}', [CommentController::class, 'update']);
            Route::delete('/{postId}/comments/{commentId}', [CommentController::class, 'destroy']);
            Route::get('/{postId}/comments/{commentId}/replies', [CommentController::class, 'replies']);

            // Post shares
            Route::get('/{postId}/shares', [ShareController::class, 'index']);
            Route::post('/{postId}/shares', [ShareController::class, 'store']);
        });

        // Share routes
        Route::prefix('shares')->group(function () {
            Route::get('/my', [ShareController::class, 'myShares']);
            Route::get('/{id}', [ShareController::class, 'show']);
            Route::put('/{id}', [ShareController::class, 'update']);
            Route::delete('/{id}', [ShareController::class, 'destroy']);
        });

        // Comment likes
        Route::post('/comments/{commentId}/like', [LikeController::class, 'toggleCommentLike']);
        Route::get('/comments/{commentId}/liked', [LikeController::class, 'hasLikedComment']);

        // Friendship routes
        Route::prefix('friendships')->group(function () {
            Route::get('/friends', [FriendshipController::class, 'friends']);
            Route::get('/pending', [FriendshipController::class, 'pendingRequests']);
            Route::get('/sent', [FriendshipController::class, 'sentRequests']);
            Route::post('/{friendshipId}/accept', [FriendshipController::class, 'acceptRequest']);
            Route::post('/{friendshipId}/reject', [FriendshipController::class, 'rejectRequest']);
            Route::delete('/remove/{userId}', [FriendshipController::class, 'removeFriend']);
            Route::get('/status/{userId}', [FriendshipController::class, 'status']);

            // Send friend request with rate limiting (20/min per user)
            Route::middleware('throttle:friend-request')->group(function () {
                Route::post('/send/{userId}', [FriendshipController::class, 'sendRequest']);
            });
        });
    });
});
