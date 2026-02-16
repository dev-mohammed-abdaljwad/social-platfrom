<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Reaction\CreateReactionRequest;
use App\Http\Requests\Api\V1\Reaction\UpdateReactionRequest;
use App\Services\Reaction\ReactionService;
use App\Transformers\Reaction\ReactionTransformer;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    public function __construct(
        protected ReactionService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
   
 

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateReactionRequest $request): JsonResponse
    {
        $reaction = $this->service->create($request->validated());
       
        return response()->json([
            'message' => 'Reaction created successfully',
            'data' => new ReactionTransformer($reaction),
        ], 201);
    }

    public  function reactToPost(CreateReactionRequest $request, int $postId): JsonResponse
    {
        $result = $this->service->reactToPost($request->user(), $postId, $request->type);

        return response()->json([
            'message' => 'Reaction processed successfully',
            'action' => $result['action'],
            'counts' => $result['counts'],
            'user_reaction' => $result['reaction'] ? new ReactionTransformer($result['reaction']) : null,
        ]);
     }

    /**
     * Display the specified resource.
     */
  
    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
 
}