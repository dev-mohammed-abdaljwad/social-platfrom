<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class FollowPaths
{
    #[OA\Post(
        path: "/users/{userId}/follow",
        summary: "Follow user",
        description: "Follow another user or send a follow request",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Follow successful"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function follow() {}

    #[OA\Delete(
        path: "/users/{userId}/follow",
        summary: "Unfollow user",
        description: "Unfollow a user",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Unfollowed successful")
        ]
    )]
    public function unfollow() {}

    #[OA\Get(
        path: "/users/{userId}/followers",
        summary: "Get followers",
        description: "Get list of users following this user",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "List of followers")
        ]
    )]
    public function followers() {}

    #[OA\Get(
        path: "/users/{userId}/following",
        summary: "Get following",
        description: "Get list of users this user is following",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "List of following")
        ]
    )]
    public function following() {}

    #[OA\Get(
        path: "/follow-requests",
        summary: "List follow requests",
        description: "Get pending follow requests for the authenticated user",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of follow requests")
        ]
    )]
    public function followRequests() {}

    #[OA\Post(
        path: "/follow-requests/{userId}/accept",
        summary: "Accept follow request",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Request accepted")
        ]
    )]
    public function acceptRequest() {}

    #[OA\Delete(
        path: "/follow-requests/{userId}/decline",
        summary: "Decline follow request",
        tags: ["Follows"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Request declined")
        ]
    )]
    public function declineRequest() {}
}
