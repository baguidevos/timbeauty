<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffAttendance;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StaffAttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StaffAttendance');
    }

    public function view(AuthUser $authUser, StaffAttendance $staffAttendance): bool
    {
        return $authUser->can('View:StaffAttendance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StaffAttendance');
    }

    public function update(AuthUser $authUser, StaffAttendance $staffAttendance): bool
    {
        return $authUser->can('Update:StaffAttendance');
    }

    public function delete(AuthUser $authUser, StaffAttendance $staffAttendance): bool
    {
        return $authUser->can('Delete:StaffAttendance');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StaffAttendance');
    }

    public function restore(AuthUser $authUser, StaffAttendance $staffAttendance): bool
    {
        return $authUser->can('Restore:StaffAttendance');
    }

    public function forceDelete(AuthUser $authUser, StaffAttendance $staffAttendance): bool
    {
        return $authUser->can('ForceDelete:StaffAttendance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StaffAttendance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StaffAttendance');
    }

    public function replicate(AuthUser $authUser, StaffAttendance $staffAttendance): bool
    {
        return $authUser->can('Replicate:StaffAttendance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StaffAttendance');
    }
}
