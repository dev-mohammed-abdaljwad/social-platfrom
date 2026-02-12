<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class CommentPaths
{
    #[OA\Get(
        path: "/posts/{postId}/comments",
        summary: "List comments",
        description: "Get all root-level comments for a post",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "List of comments", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))]))
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/posts/{postId}/comments",
        summary: "Create comment",
        description: "Add a comment to a post (can also create replies)",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Great post!"),
                    new OA\Property(property: "parent_id", type: "integer", description: "Parent comment ID for replies", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Comment created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/posts/{postId}/comments/{commentId}",
        summary: "Get comment",
        description: "Get a specific comment",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "commentId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Comment details"),
            new OA\Response(response: 404, description: "Comment not found")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/posts/{postId}/comments/{commentId}",
        summary: "Update comment",
        description: "Update a comment (must be the owner)",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "commentId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [new OA\Property(property: "content", type: "string", example: "Updated comment!")]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Comment updated"),
            new OA\Response(response: 403, description: "Unauthorized")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/posts/{postId}/comments/{commentId}",
        summary: "Delete comment",
        description: "Delete a comment (must be the owner)",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "commentId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Comment deleted"),
            new OA\Response(response: 403, description: "Unauthorized")
        ]
    )]
    public function destroy() {}

    #[OA\Get(
        path: "/posts/{postId}/comments/{commentId}/replies",
        summary: "Get replies",
        description: "Get all replies to a specific comment",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "commentId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "List of replies", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))]))
        ]
    )]
    public function replies() {}
}
