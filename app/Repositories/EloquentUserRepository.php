<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByGraphNodeId(string $graphNodeId): ?User
    {
        return User::whereHas('profile', fn($q) => $q->where('graph_node_id', $graphNodeId))->first();
    }

    public function all(): Collection
    {
        return User::all();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return (bool) User::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) User::destroy($id);
    }
}
