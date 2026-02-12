<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class FriendshipPaths
{
    #[OA\Get(
        path: "/friends",
        summary: "Get friends list",
        description: "Get the authenticated user's friends",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of friends", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))]))
        ]
    )]
    public function friends() {}

    #[OA\Get(
        path: "/friends/requests/pending",
        summary: "Get pending requests",
        description: "Get friend requests received by the authenticated user",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of pending requests", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))]))
        ]
    )]
    public function pendingRequests() {}

    #[OA\Get(
        path: "/friends/requests/sent",
        summary: "Get sent requests",
        description: "Get friend requests sent by the authenticated user",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of sent requests", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))]))
        ]
    )]
    public function sentRequests() {}

    #[OA\Post(
        path: "/friends/request/{userId}",
        summary: "Send friend request",
        description: "Send a friend request to another user",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 201, description: "Friend request sent"),
            new OA\Response(response: 400, description: "Request already exists or invalid")
        ]
    )]
    public function sendRequest() {}

    #[OA\Post(
        path: "/friends/accept/{friendshipId}",
        summary: "Accept friend request",
        description: "Accept a pending friend request",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "friendshipId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Friend request accepted"),
            new OA\Response(response: 400, description: "Cannot accept this request")
        ]
    )]
    public function acceptRequest() {}

    #[OA\Post(
        path: "/friends/reject/{friendshipId}",
        summary: "Reject friend request",
        description: "Reject a pending friend request",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "friendshipId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Friend request rejected"),
            new OA\Response(response: 400, description: "Cannot reject this request")
        ]
    )]
    public function rejectRequest() {}

    #[OA\Delete(
        path: "/friends/{userId}",
        summary: "Remove friend",
        description: "Remove an existing friend",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Friend removed"),
            new OA\Response(response: 400, description: "User is not your friend")
        ]
    )]
    public function removeFriend() {}

    #[OA\Get(
        path: "/friends/status/{userId}",
        summary: "Get friendship status",
        description: "Get the friendship status between the authenticated user and another user",
        tags: ["Friendships"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "userId", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(
                response: 200,
                description: "Friendship status",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "status", type: "string", enum: ["not_friends", "pending_sent", "pending_received", "friends"]),
                        new OA\Property(property: "friendship_id", type: "integer", nullable: true)
                    ])
                ])
            )
        ]
    )]
    public function status() {}
}
