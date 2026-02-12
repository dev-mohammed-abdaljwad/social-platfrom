<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class PostPaths
{
    #[OA\Get(
        path: "/posts/feed",
        summary: "Get feed",
        description: "Get the authenticated user's feed (own posts + friends' posts)",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "limit", in: "query", description: "Number of posts", schema: new OA\Schema(type: "integer", default: 20))],
        responses: [
            new OA\Response(response: 200, description: "Feed posts", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function feed() {}

    #[OA\Get(
        path: "/posts",
        summary: "List posts",
        description: "Get all public posts",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of posts", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/posts",
        summary: "Create post",
        description: "Create a new post",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Hello world!"),
                    new OA\Property(property: "content_type", type: "string", enum: ["text", "image", "video"], example: "text"),
                    new OA\Property(property: "privacy", type: "string", enum: ["public", "private", "friends"], example: "public"),
                    new OA\Property(property: "media_url", type: "string", example: "https://example.com/image.jpg")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Post created", content: new OA\JsonContent(properties: [
                new OA\Property(property: "message", type: "string", example: "Post created successfully"),
                new OA\Property(property: "data", type: "object")
            ])),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/posts/{id}",
        summary: "Get post",
        description: "Get a specific post by ID",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Post details", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "object")])),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/posts/{id}",
        summary: "Update post",
        description: "Update an existing post (must be the owner)",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Updated content!"),
                    new OA\Property(property: "privacy", type: "string", enum: ["public", "private", "friends"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Post updated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/posts/{id}",
        summary: "Delete post",
        description: "Delete a post (must be the owner)",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Post deleted", content: new OA\JsonContent(properties: [new OA\Property(property: "message", type: "string")])),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function destroy() {}

    #[OA\Get(
        path: "/users/{userId}/posts",
        summary: "Get user's posts",
        description: "Get all posts from a specific user",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "User's posts", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))]))
        ]
    )]
    public function userPosts() {}
}
