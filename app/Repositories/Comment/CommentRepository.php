<?php

namespace App\Repositories\Comment;

interface CommentRepository
{
    public function all();
    public function find($id);
    public function findByPost($postId);
    public function findRootCommentsByPost($postId);
    public function findReplies($commentId);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
}
