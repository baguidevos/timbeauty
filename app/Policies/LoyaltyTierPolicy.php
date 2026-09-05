<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LoyaltyTier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LoyaltyTierPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LoyaltyTier');
    }

    public function view(AuthUser $authUser, LoyaltyTier $loyaltyTier): bool
    {
        return $authUser->can('View:LoyaltyTier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LoyaltyTier');
    }

    public function update(AuthUser $authUser, LoyaltyTier $loyaltyTier): bool
    {
        return $authUser->can('Update:LoyaltyTier');
    }

    public function delete(AuthUser $authUser, LoyaltyTier $loyaltyTier): bool
    {
        return $authUser->can('Delete:LoyaltyTier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LoyaltyTier');
    }

    public function restore(AuthUser $authUser, LoyaltyTier $loyaltyTier): bool
    {
        return $authUser->can('Restore:LoyaltyTier');
    }

    public function forceDelete(AuthUser $authUser, LoyaltyTier $loyaltyTier): bool
    {
        return $authUser->can('ForceDelete:LoyaltyTier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LoyaltyTier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LoyaltyTier');
    }

    public function replicate(AuthUser $authUser, LoyaltyTier $loyaltyTier): bool
    {
        return $authUser->can('Replicate:LoyaltyTier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LoyaltyTier');
    }
}
