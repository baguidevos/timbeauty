<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LoyaltyPointTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LoyaltyPointTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LoyaltyPointTransaction');
    }

    public function view(AuthUser $authUser, LoyaltyPointTransaction $loyaltyPointTransaction): bool
    {
        return $authUser->can('View:LoyaltyPointTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LoyaltyPointTransaction');
    }

    public function update(AuthUser $authUser, LoyaltyPointTransaction $loyaltyPointTransaction): bool
    {
        return $authUser->can('Update:LoyaltyPointTransaction');
    }

    public function delete(AuthUser $authUser, LoyaltyPointTransaction $loyaltyPointTransaction): bool
    {
        return $authUser->can('Delete:LoyaltyPointTransaction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LoyaltyPointTransaction');
    }

    public function restore(AuthUser $authUser, LoyaltyPointTransaction $loyaltyPointTransaction): bool
    {
        return $authUser->can('Restore:LoyaltyPointTransaction');
    }

    public function forceDelete(AuthUser $authUser, LoyaltyPointTransaction $loyaltyPointTransaction): bool
    {
        return $authUser->can('ForceDelete:LoyaltyPointTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LoyaltyPointTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LoyaltyPointTransaction');
    }

    public function replicate(AuthUser $authUser, LoyaltyPointTransaction $loyaltyPointTransaction): bool
    {
        return $authUser->can('Replicate:LoyaltyPointTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LoyaltyPointTransaction');
    }
}
