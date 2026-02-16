<?php

namespace App\Repositories\Comment;

interface CommentRepository
{
    public function all();
    public function find($id);
    public function findByPost($postId);
    public function findRootCommentsByPost($postId, int $limit = 20);
    public function findReplies($commentId, int $limit = 10);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function getReactionsCount($commentId): int;
}
