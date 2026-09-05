<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OwnerAdvance;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class OwnerAdvancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OwnerAdvance');
    }

    public function view(AuthUser $authUser, OwnerAdvance $ownerAdvance): bool
    {
        return $authUser->can('View:OwnerAdvance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OwnerAdvance');
    }

    public function update(AuthUser $authUser, OwnerAdvance $ownerAdvance): bool
    {
        return $authUser->can('Update:OwnerAdvance');
    }

    public function delete(AuthUser $authUser, OwnerAdvance $ownerAdvance): bool
    {
        return $authUser->can('Delete:OwnerAdvance');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OwnerAdvance');
    }

    public function restore(AuthUser $authUser, OwnerAdvance $ownerAdvance): bool
    {
        return $authUser->can('Restore:OwnerAdvance');
    }

    public function forceDelete(AuthUser $authUser, OwnerAdvance $ownerAdvance): bool
    {
        return $authUser->can('ForceDelete:OwnerAdvance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OwnerAdvance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OwnerAdvance');
    }

    public function replicate(AuthUser $authUser, OwnerAdvance $ownerAdvance): bool
    {
        return $authUser->can('Replicate:OwnerAdvance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OwnerAdvance');
    }
}
