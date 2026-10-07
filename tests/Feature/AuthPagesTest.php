<?php

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'user@example.com',
        'phone' => '+228 90 00 00 00',
        'password' => Hash::make('password123'),
        'active' => true,
    ]);
});

it('displays password reset link on login page', function () {
    $this->get(route('filament.admin.auth.login'))
        ->assertSuccessful()
        ->assertSee(route('filament.admin.auth.password-reset.request'));
});

it('can render password reset request page for guest', function () {
    $this->get(route('filament.admin.auth.password-reset.request'))
        ->assertSuccessful();

    Livewire::test(RequestPasswordReset::class)
        ->assertSuccessful();
});

it('can request a password reset link', function () {
    Password::shouldReceive('broker')
        ->once()
        ->andReturn($broker = Mockery::mock());

    $broker->shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => $this->user->email], Mockery::type('Closure'))
        ->andReturn(Password::RESET_LINK_SENT);

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $this->user->email,
        ])
        ->call('request')
        ->assertHasNoFormErrors();
});

it('redirects guest trying to visit profile page to login', function () {
    $this->get(route('filament.admin.auth.profile'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('can render profile page for authenticated user', function () {
    $this->actingAs($this->user);

    $this->get(route('filament.admin.auth.profile'))
        ->assertSuccessful();

    Livewire::test(EditProfile::class)
        ->assertSuccessful()
        ->assertFormSet([
            'name' => 'Original Name',
            'email' => 'user@example.com',
            'phone' => '+228 90 00 00 00',
        ]);
});

it('can update profile information', function () {
    $this->actingAs($this->user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => 'Updated Name',
            'email' => 'user@example.com',
            'phone' => '+228 91 11 11 11',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->user->refresh();

    expect($this->user->name)->toBe('Updated Name')
        ->and($this->user->phone)->toBe('+228 91 11 11 11');
});

it('can update password from profile page', function () {
    $this->actingAs($this->user);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'currentPassword' => 'password123',
            'password' => 'newpassword123',
            'passwordConfirmation' => 'newpassword123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->user->refresh();

    expect(Hash::check('newpassword123', $this->user->password))->toBeTrue();
});
