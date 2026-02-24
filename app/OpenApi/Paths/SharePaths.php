<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class SharePaths
{
    #[OA\Get(
        path: "/posts/{postId}/shares",
        summary: "List shares for post",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "List of shares")
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/posts/{postId}/shares",
        summary: "Share post",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Check this out!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Post shared")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/shares/my",
        summary: "List my shares",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of my shares")
        ]
    )]
    public function myShares() {}
}
