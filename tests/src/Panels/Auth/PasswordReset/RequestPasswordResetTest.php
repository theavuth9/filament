<?php

use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use function Filament\Tests\livewire;
use Filament\Tests\Models\User;
use Filament\Tests\TestCase;
use Illuminate\Support\Facades\Notification;

uses(TestCase::class);

it('can render page', function () {
    $this->get(Filament::getRequestPasswordResetUrl())
        ->assertSuccessful();
});

it('can request password reset', function () {
    Notification::fake();

    $this->assertGuest();

    $userToResetPassword = User::factory()->create();

    livewire(RequestPasswordReset::class)
        ->fillForm([
            'email' => $userToResetPassword->email,
        ])
        ->call('request')
        ->assertNotified();

    Notification::assertSentTo($userToResetPassword, ResetPassword::class);
});

it('can throttle requests', function () {
    Notification::fake();

    $this->assertGuest();

    foreach (range(1, 2) as $i) {
        $userToResetPassword = User::factory()->create();

        livewire(RequestPasswordReset::class)
            ->fillForm([
                'email' => $userToResetPassword->email,
            ])
            ->call('request')
            ->assertNotified();

        Notification::assertSentToTimes($userToResetPassword, ResetPassword::class, times: 1);
    }

    $userToResetPassword = User::factory()->create();

    livewire(RequestPasswordReset::class)
        ->fillForm([
            'email' => $userToResetPassword->email,
        ])
        ->call('request')
        ->assertNotified();

    Notification::assertNotSentTo($userToResetPassword, ResetPassword::class);
});

it('can validate `email` is required', function () {
    livewire(RequestPasswordReset::class)
        ->fillForm([
            'email' => '',
        ])
        ->call('request')
        ->assertHasFormErrors(['email' => ['required']]);
});

it('can validate `email` is valid email', function () {
    livewire(RequestPasswordReset::class)
        ->fillForm([
            'email' => 'invalid-email',
        ])
        ->call('request')
        ->assertHasFormErrors(['email' => ['email']]);
});
