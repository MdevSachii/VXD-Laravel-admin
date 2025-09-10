<?php

namespace App\Contracts;

use App\Services\WPService;

interface IWP
{
    public function me(): array;

    public function listPosts(array $params = []): array;

    public function getPost(int|string $postId): array;

    public function createPost(array $data): array;

    public function updatePost(int|string $postId, array $data): array;

    public function deletePost(int|string $postId): array;
}
