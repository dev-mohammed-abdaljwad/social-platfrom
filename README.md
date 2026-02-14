# SocialTime - Social Platform

A full-featured social media platform built with Laravel 12, featuring real-time notifications, posts, comments, friendships, and more.

## Features

### Core Features
- **User Authentication** - Register, login, logout with session and API token support
- **User Profiles** - Customizable profiles with pictures, cover photos, and bio
- **Posts** - Create, edit, delete posts with text, images, or videos
- **Comments** - Nested comments with replies support
- **Likes** - Like/unlike posts and comments
- **Shares** - Share posts with optional commentary
- **Saved Posts** - Save posts for later viewing
- **Friendships** - Send, accept, reject, and cancel friend requests

### User Discovery
- **User Search** - Search users by name or username with live autocomplete
- **Friend Suggestions** - Discover people you may know
- **Explore Page** - Browse public posts from all users

### Real-time Features
- **Push Notifications** - Real-time notifications via Pusher
- **Notification Types** - Friend requests, likes, comments, shares

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade Templates, Tailwind CSS v4, Vite 7
- **Database**: SQLite (default), MySQL/PostgreSQL supported
- **Real-time**: Pusher for WebSocket notifications
- **API**: RESTful API with Laravel Sanctum authentication
- **Testing**: Pest PHP

## Architecture

The project follows a clean architecture pattern:

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/       # API Controllers (versioned)
│   │   └── Web/          # Web Controllers
│   └── Requests/         # Form Request validation
├── Models/               # Eloquent models
├── Repositories/         # Data access layer (interfaces + Eloquent implementations)
├── Services/             # Business logic layer
├── Transformers/         # API response transformers (JSON Resources)
└── Enums/                # PHP Enums for types
```

### Design Patterns
- **Repository Pattern** - Abstracted data access with interfaces
- **Service Layer** - Business logic separated from controllers
- **Controller as Orchestrator** - Controllers only coordinate between services
- **Form Requests** - Validation logic separated from controllers
- **Transformers** - Consistent API response formatting

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd social-platfrom

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start development server
composer dev
```

## Development Commands

```bash
# Full project setup
composer setup

# Start dev server with Vite hot reload
composer dev

# Run tests
composer test

# Code style fixes
./vendor/bin/pint

# Generate API documentation
php artisan l5-swagger:generate
```

## API Endpoints

### Authentication
- `POST /api/v1/register` - Register new user
- `POST /api/v1/login` - Login
- `POST /api/v1/logout` - Logout
- `GET /api/v1/me` - Get authenticated user

### Posts
- `GET /api/v1/posts` - List all posts
- `GET /api/v1/posts/feed` - Get personalized feed
- `POST /api/v1/posts` - Create post
- `GET /api/v1/posts/{id}` - Get post
- `PUT /api/v1/posts/{id}` - Update post
- `DELETE /api/v1/posts/{id}` - Delete post

### Comments
- `GET /api/v1/posts/{postId}/comments` - List comments
- `POST /api/v1/posts/{postId}/comments` - Create comment
- `PUT /api/v1/posts/{postId}/comments/{id}` - Update comment
- `DELETE /api/v1/posts/{postId}/comments/{id}` - Delete comment

### Likes
- `POST /api/v1/posts/{id}/like` - Toggle post like
- `POST /api/v1/comments/{id}/like` - Toggle comment like
- `GET /api/v1/posts/{id}/likes` - Get post likes

### Friendships
- `GET /api/v1/friends` - List friends
- `POST /api/v1/friends/{userId}/request` - Send friend request
- `POST /api/v1/friends/{id}/accept` - Accept request
- `POST /api/v1/friends/{id}/reject` - Reject request
- `DELETE /api/v1/friends/{userId}` - Remove friend

### Users
- `GET /api/v1/users` - List/search users
- `GET /api/v1/users/{id}` - Get user profile
- `PUT /api/v1/profile` - Update profile

### Shares
- `GET /api/v1/posts/{postId}/shares` - List shares
- `POST /api/v1/posts/{postId}/shares` - Share post
- `PUT /api/v1/shares/{id}` - Update share
- `DELETE /api/v1/shares/{id}` - Delete share

## Web Routes

- `/` - Home (feed)
- `/explore` - Explore public posts
- `/search` - Search users
- `/friends` - Friends management
- `/profile` - User profile
- `/profile/{user}` - View other profiles
- `/saved` - Saved posts
- `/settings` - Account settings

## Environment Variables

```env
# Pusher (for real-time notifications)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

# Database
DB_CONNECTION=mysql
```

## API Documentation

Swagger/OpenAPI documentation is available at `/api/documentation` when running locally.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
