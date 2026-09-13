<?php

namespace App\Policies;

use App\Models\User;

class CommentPolicy
{
    public function update(User $user, object $comment): bool
    {
        return (int) $comment->user_id === (int) $user->id;
    }

    public function delete(User $user, object $comment): bool
    {
        return (int) $comment->user_id === (int) $user->id;
    }
}