<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class ReactionPaths
{
    #[OA\Post(
        path: "/posts/{postId}/react",
        summary: "React to post",
        description: "Add a reaction (like, love, haha, etc.) to a post. Sending the same type toggles it off.",
        tags: ["Reactions"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type"],
                properties: [
                    new OA\Property(property: "type", type: "string", example: "like", enum: ["like", "love", "haha", "wow", "sad", "angry"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Reaction processed successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "action", type: "string", enum: ["added", "removed"]),
                    new OA\Property(property: "counts", type: "object")
                ])
            )
        ]
    )]
    public function reactToPost() {}
}
