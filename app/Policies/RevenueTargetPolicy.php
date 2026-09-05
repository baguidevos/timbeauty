<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RevenueTarget;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RevenueTargetPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RevenueTarget');
    }

    public function view(AuthUser $authUser, RevenueTarget $revenueTarget): bool
    {
        return $authUser->can('View:RevenueTarget');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RevenueTarget');
    }

    public function update(AuthUser $authUser, RevenueTarget $revenueTarget): bool
    {
        return $authUser->can('Update:RevenueTarget');
    }

    public function delete(AuthUser $authUser, RevenueTarget $revenueTarget): bool
    {
        return $authUser->can('Delete:RevenueTarget');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RevenueTarget');
    }

    public function restore(AuthUser $authUser, RevenueTarget $revenueTarget): bool
    {
        return $authUser->can('Restore:RevenueTarget');
    }

    public function forceDelete(AuthUser $authUser, RevenueTarget $revenueTarget): bool
    {
        return $authUser->can('ForceDelete:RevenueTarget');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RevenueTarget');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RevenueTarget');
    }

    public function replicate(AuthUser $authUser, RevenueTarget $revenueTarget): bool
    {
        return $authUser->can('Replicate:RevenueTarget');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RevenueTarget');
    }
}
