<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AppointmentPhoto;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AppointmentPhotoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AppointmentPhoto');
    }

    public function view(AuthUser $authUser, AppointmentPhoto $appointmentPhoto): bool
    {
        return $authUser->can('View:AppointmentPhoto');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AppointmentPhoto');
    }

    public function update(AuthUser $authUser, AppointmentPhoto $appointmentPhoto): bool
    {
        return $authUser->can('Update:AppointmentPhoto');
    }

    public function delete(AuthUser $authUser, AppointmentPhoto $appointmentPhoto): bool
    {
        return $authUser->can('Delete:AppointmentPhoto');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AppointmentPhoto');
    }

    public function restore(AuthUser $authUser, AppointmentPhoto $appointmentPhoto): bool
    {
        return $authUser->can('Restore:AppointmentPhoto');
    }

    public function forceDelete(AuthUser $authUser, AppointmentPhoto $appointmentPhoto): bool
    {
        return $authUser->can('ForceDelete:AppointmentPhoto');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AppointmentPhoto');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AppointmentPhoto');
    }

    public function replicate(AuthUser $authUser, AppointmentPhoto $appointmentPhoto): bool
    {
        return $authUser->can('Replicate:AppointmentPhoto');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AppointmentPhoto');
    }
}
