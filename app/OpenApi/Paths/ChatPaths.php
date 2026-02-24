<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class ChatPaths
{
    #[OA\Get(
        path: "/chat/conversations",
        summary: "List conversations",
        description: "Get the authenticated user's conversations (inbox)",
        tags: ["Chat"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of conversations",
                content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))])
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/chat/conversations",
        summary: "Start/Get conversation",
        description: "Start a new conversation or get existing one with another user",
        tags: ["Chat"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Conversation data"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/chat/conversations/{conversation}/messages",
        summary: "Get messages",
        description: "Get paginated messages for a conversation",
        tags: ["Chat"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "conversation", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of messages",
                content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))])
            )
        ]
    )]
    public function messages() {}

    #[OA\Post(
        path: "/chat/conversations/{conversation}/messages",
        summary: "Send message",
        description: "Send a new message in a conversation",
        tags: ["Chat"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "conversation", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Hello!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Message sent"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function sendMessage() {}

    #[OA\Post(
        path: "/chat/conversations/{conversation}/read",
        summary: "Mark as read",
        description: "Mark all messages in a conversation as read",
        tags: ["Chat"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "conversation", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Messages marked as read")
        ]
    )]
    public function markAsRead() {}
}
