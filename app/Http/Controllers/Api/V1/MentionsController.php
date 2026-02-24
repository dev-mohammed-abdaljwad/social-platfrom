<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mentions\MentionsService;
use Illuminate\Http\JsonResponse;

class MentionsController extends Controller
{
    public function __construct(
        protected MentionsService $service
    ) {}

    public function index(): JsonResponse
    {
        $feed = $this->service->getFeed(
            userId: auth()->id(),
            perPage: 15
        );
        return response()->json([
            'success' => true,
            'message' => 'Mentions feed fetched successfully',
            'data' => $feed,
        ]);
    }
}
