<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Barber;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BarberPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Barber');
    }

    public function view(AuthUser $authUser, Barber $barber): bool
    {
        return $authUser->can('View:Barber');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Barber');
    }

    public function update(AuthUser $authUser, Barber $barber): bool
    {
        return $authUser->can('Update:Barber');
    }

    public function delete(AuthUser $authUser, Barber $barber): bool
    {
        return $authUser->can('Delete:Barber');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Barber');
    }

    public function restore(AuthUser $authUser, Barber $barber): bool
    {
        return $authUser->can('Restore:Barber');
    }

    public function forceDelete(AuthUser $authUser, Barber $barber): bool
    {
        return $authUser->can('ForceDelete:Barber');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Barber');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Barber');
    }

    public function replicate(AuthUser $authUser, Barber $barber): bool
    {
        return $authUser->can('Replicate:Barber');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Barber');
    }
}
