<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Social Platform API",
    description: "API documentation for the Social Platform application",
    contact: new OA\Contact(email: "support@social-platform.com")
)]
#[OA\Server(url: "/api/v1", description: "API Server")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Enter your Bearer token"
)]
#[OA\Tag(name: "Authentication", description: "User registration, login, and token management")]
#[OA\Tag(name: "Users", description: "User profile management")]
#[OA\Tag(name: "Posts", description: "Create and manage posts")]
#[OA\Tag(name: "Comments", description: "Comments on posts")]
#[OA\Tag(name: "Likes", description: "Like posts and comments")]
#[OA\Tag(name: "Friendships", description: "Friend requests and friendships")]
#[OA\Tag(name: "Follows", description: "User following and follower management")]
#[OA\Tag(name: "Chat", description: "Real-time messaging and conversations")]
#[OA\Tag(name: "Mentions", description: "Manage and search user mentions")]

class ApiInfo {}
