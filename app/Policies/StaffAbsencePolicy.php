<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffAbsence;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StaffAbsencePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StaffAbsence');
    }

    public function view(AuthUser $authUser, StaffAbsence $staffAbsence): bool
    {
        return $authUser->can('View:StaffAbsence');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StaffAbsence');
    }

    public function update(AuthUser $authUser, StaffAbsence $staffAbsence): bool
    {
        return $authUser->can('Update:StaffAbsence');
    }

    public function delete(AuthUser $authUser, StaffAbsence $staffAbsence): bool
    {
        return $authUser->can('Delete:StaffAbsence');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StaffAbsence');
    }

    public function restore(AuthUser $authUser, StaffAbsence $staffAbsence): bool
    {
        return $authUser->can('Restore:StaffAbsence');
    }

    public function forceDelete(AuthUser $authUser, StaffAbsence $staffAbsence): bool
    {
        return $authUser->can('ForceDelete:StaffAbsence');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StaffAbsence');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StaffAbsence');
    }

    public function replicate(AuthUser $authUser, StaffAbsence $staffAbsence): bool
    {
        return $authUser->can('Replicate:StaffAbsence');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StaffAbsence');
    }
}
