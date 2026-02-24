<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class MentionPaths
{
    #[OA\Get(
        path: "/mentions",
        summary: "Search mentions",
        description: "Search for users to mention (@mentions)",
        tags: ["Mentions"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "q", in: "query", description: "Search query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of users for mentions",
                content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))])
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index() {}
}
