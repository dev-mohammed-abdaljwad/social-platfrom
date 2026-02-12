<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class UserPaths
{
    #[OA\Get(
        path: "/users",
        summary: "List users",
        description: "Get all users or search by query",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "q", in: "query", description: "Search query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of users",
                content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))])
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: "/users/{id}",
        summary: "Get user by ID",
        description: "Get a specific user's profile by their ID",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "User ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User profile", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "object")])),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function show() {}

    #[OA\Get(
        path: "/users/username/{username}",
        summary: "Get user by username",
        description: "Get a specific user's profile by their username",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "username", in: "path", description: "Username", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User profile", content: new OA\JsonContent(properties: [new OA\Property(property: "data", type: "object")])),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function showByUsername() {}

    #[OA\Put(
        path: "/users/profile",
        summary: "Update profile",
        description: "Update the authenticated user's profile information",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Updated"),
                    new OA\Property(property: "bio", type: "string", example: "Updated bio")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profile updated", content: new OA\JsonContent(properties: [
                new OA\Property(property: "message", type: "string", example: "Profile updated successfully"),
                new OA\Property(property: "data", type: "object")
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function updateProfile() {}

    #[OA\Post(
        path: "/users/profile/picture",
        summary: "Update profile picture",
        description: "Upload a new profile picture for the authenticated user",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["profile_picture"],
                    properties: [new OA\Property(property: "profile_picture", type: "string", format: "binary", description: "Profile picture (max 2MB)")]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profile picture updated"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function updateProfilePicture() {}
}
