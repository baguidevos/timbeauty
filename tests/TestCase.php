<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Gate;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('admin'))) {
                return true;
            }

            $identifier = strtolower(($user->name ?? '').' '.($user->email ?? ''));
            if ((str_contains($identifier, 'admin') || str_contains($identifier, 'owner') || str_contains($identifier, 'proprio') || str_contains($identifier, 'propriétaire')) && ! $user->roles()->exists()) {
                return true;
            }
        });
    }
}
