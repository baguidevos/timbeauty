<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LoyaltyRule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LoyaltyRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LoyaltyRule');
    }

    public function view(AuthUser $authUser, LoyaltyRule $loyaltyRule): bool
    {
        return $authUser->can('View:LoyaltyRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LoyaltyRule');
    }

    public function update(AuthUser $authUser, LoyaltyRule $loyaltyRule): bool
    {
        return $authUser->can('Update:LoyaltyRule');
    }

    public function delete(AuthUser $authUser, LoyaltyRule $loyaltyRule): bool
    {
        return $authUser->can('Delete:LoyaltyRule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LoyaltyRule');
    }

    public function restore(AuthUser $authUser, LoyaltyRule $loyaltyRule): bool
    {
        return $authUser->can('Restore:LoyaltyRule');
    }

    public function forceDelete(AuthUser $authUser, LoyaltyRule $loyaltyRule): bool
    {
        return $authUser->can('ForceDelete:LoyaltyRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LoyaltyRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LoyaltyRule');
    }

    public function replicate(AuthUser $authUser, LoyaltyRule $loyaltyRule): bool
    {
        return $authUser->can('Replicate:LoyaltyRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LoyaltyRule');
    }
}
