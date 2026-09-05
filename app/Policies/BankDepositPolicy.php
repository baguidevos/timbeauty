<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BankDeposit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BankDepositPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BankDeposit');
    }

    public function view(AuthUser $authUser, BankDeposit $bankDeposit): bool
    {
        return $authUser->can('View:BankDeposit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BankDeposit');
    }

    public function update(AuthUser $authUser, BankDeposit $bankDeposit): bool
    {
        return $authUser->can('Update:BankDeposit');
    }

    public function delete(AuthUser $authUser, BankDeposit $bankDeposit): bool
    {
        return $authUser->can('Delete:BankDeposit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BankDeposit');
    }

    public function restore(AuthUser $authUser, BankDeposit $bankDeposit): bool
    {
        return $authUser->can('Restore:BankDeposit');
    }

    public function forceDelete(AuthUser $authUser, BankDeposit $bankDeposit): bool
    {
        return $authUser->can('ForceDelete:BankDeposit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BankDeposit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BankDeposit');
    }

    public function replicate(AuthUser $authUser, BankDeposit $bankDeposit): bool
    {
        return $authUser->can('Replicate:BankDeposit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BankDeposit');
    }
}
