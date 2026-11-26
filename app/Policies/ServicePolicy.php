<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

/**
 * Penjual hanya boleh mengelola jasanya sendiri.
 */
class ServicePolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'seller';
    }

    public function update(User $user, Service $service): bool
    {
        return $user->id === $service->seller_id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->id === $service->seller_id;
    }
}
