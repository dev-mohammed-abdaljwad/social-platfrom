<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class AuthPaths
{
    #[OA\Post(
        path: "/auth/register",
        summary: "Register a new user",
        description: "Create a new user account and receive an access token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "username", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "username", type: "string", example: "johndoe"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "user", type: "object"),
                            new OA\Property(property: "token", type: "string", example: "1|abc123..."),
                            new OA\Property(property: "token_type", type: "string", example: "Bearer")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/auth/login",
        summary: "Login",
        description: "Authenticate a user and receive an access token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Login successful"),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "user", type: "object"),
                            new OA\Property(property: "token", type: "string", example: "1|abc123..."),
                            new OA\Property(property: "token_type", type: "string", example: "Bearer")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/auth/logout",
        summary: "Logout",
        description: "Revoke the current access token",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logged out successfully",
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: "message", type: "string", example: "Logged out successfully")]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function logout() {}

    #[OA\Post(
        path: "/auth/logout-all",
        summary: "Logout from all devices",
        description: "Revoke all access tokens for the authenticated user",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logged out from all devices",
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: "message", type: "string", example: "Logged out from all devices successfully")]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function logoutAll() {}

    #[OA\Post(
        path: "/auth/refresh",
        summary: "Refresh token",
        description: "Revoke current token and issue a new one",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token refreshed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Token refreshed successfully"),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "token", type: "string", example: "2|xyz789..."),
                            new OA\Property(property: "token_type", type: "string", example: "Bearer")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function refresh() {}

    #[OA\Get(
        path: "/auth/me",
        summary: "Get current user",
        description: "Get the authenticated user's profile information",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "User profile",
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: "data", type: "object")]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function me() {}

    #[OA\Post(
        path: "/auth/change-password",
        summary: "Change password",
        description: "Update the authenticated user's password",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "newpassword123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "newpassword123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password changed",
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: "message", type: "string", example: "Password changed successfully")]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function changePassword() {}
}
