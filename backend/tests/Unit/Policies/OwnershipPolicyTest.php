<?php

namespace Tests\Unit\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use PHPUnit\Framework\TestCase;

class OwnershipPolicyTest extends TestCase
{
    public function test_only_the_post_owner_can_update_or_delete(): void
    {
        $owner = (new User())->setAttribute('id', 10);
        $otherUser = (new User())->setAttribute('id', 20);
        $post = (new Post())->setAttribute('user_id', 10);
        $policy = new PostPolicy();

        $this->assertTrue($policy->update($owner, $post));
        $this->assertTrue($policy->delete($owner, $post));
        $this->assertFalse($policy->update($otherUser, $post));
        $this->assertFalse($policy->delete($otherUser, $post));
    }

    public function test_only_the_comment_owner_can_update_or_delete(): void
    {
        $owner = (new User())->setAttribute('id', 10);
        $otherUser = (new User())->setAttribute('id', 20);
        $comment = (object) ['user_id' => 10];
        $policy = new CommentPolicy();

        $this->assertTrue($policy->update($owner, $comment));
        $this->assertTrue($policy->delete($owner, $comment));
        $this->assertFalse($policy->update($otherUser, $comment));
        $this->assertFalse($policy->delete($otherUser, $comment));
    }
}