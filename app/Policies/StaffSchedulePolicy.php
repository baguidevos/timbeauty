<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StaffSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StaffSchedule');
    }

    public function view(AuthUser $authUser, StaffSchedule $staffSchedule): bool
    {
        return $authUser->can('View:StaffSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StaffSchedule');
    }

    public function update(AuthUser $authUser, StaffSchedule $staffSchedule): bool
    {
        return $authUser->can('Update:StaffSchedule');
    }

    public function delete(AuthUser $authUser, StaffSchedule $staffSchedule): bool
    {
        return $authUser->can('Delete:StaffSchedule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StaffSchedule');
    }

    public function restore(AuthUser $authUser, StaffSchedule $staffSchedule): bool
    {
        return $authUser->can('Restore:StaffSchedule');
    }

    public function forceDelete(AuthUser $authUser, StaffSchedule $staffSchedule): bool
    {
        return $authUser->can('ForceDelete:StaffSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StaffSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StaffSchedule');
    }

    public function replicate(AuthUser $authUser, StaffSchedule $staffSchedule): bool
    {
        return $authUser->can('Replicate:StaffSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StaffSchedule');
    }
}
