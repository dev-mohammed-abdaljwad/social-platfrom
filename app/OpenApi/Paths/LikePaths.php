<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class LikePaths
{
    #[OA\Post(
        path: "/posts/{postId}/like",
        summary: "Toggle post like",
        description: "Like or unlike a post. If already liked, it will unlike.",
        tags: ["Likes"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(
                response: 200,
                description: "Like toggled",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "message", type: "string", example: "Post liked"),
                    new OA\Property(property: "action", type: "string", enum: ["liked", "unliked"], example: "liked")
                ])
            )
        ]
    )]
    public function togglePostLike() {}

    #[OA\Post(
        path: "/comments/{commentId}/like",
        summary: "Toggle comment like",
        description: "Like or unlike a comment. If already liked, it will unlike.",
        tags: ["Likes"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "commentId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(
                response: 200,
                description: "Like toggled",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "message", type: "string", example: "Comment liked"),
                    new OA\Property(property: "action", type: "string", enum: ["liked", "unliked"], example: "liked")
                ])
            )
        ]
    )]
    public function toggleCommentLike() {}

    #[OA\Get(
        path: "/posts/{postId}/like/check",
        summary: "Check if post is liked",
        description: "Check if the authenticated user has liked a post",
        tags: ["Likes"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Like status", content: new OA\JsonContent(properties: [new OA\Property(property: "liked", type: "boolean", example: true)]))
        ]
    )]
    public function hasLikedPost() {}

    #[OA\Get(
        path: "/comments/{commentId}/like/check",
        summary: "Check if comment is liked",
        description: "Check if the authenticated user has liked a comment",
        tags: ["Likes"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "commentId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Like status", content: new OA\JsonContent(properties: [new OA\Property(property: "liked", type: "boolean", example: false)]))
        ]
    )]
    public function hasLikedComment() {}
}
