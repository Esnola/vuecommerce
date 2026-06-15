<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the registration page to guests', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Create account');

    $this->get(route('login'))
        ->assertSee(route('register'));
});

it('creates a pending account and redirects to login', function () {
    Livewire::test('pages::auth.register')
        ->set('firstName', 'Taylor')
        ->set('lastName', 'Otwell')
        ->set('email', 'taylor@example.com')
        ->set('phone', '+34600111222')
        ->set('password', 'secret-password')
        ->set('passwordConfirmation', 'secret-password')
        ->call('register')
        ->assertHasNoErrors()
        ->assertSessionHas('registration-status')
        ->assertRedirect(route('login'));

    $user = User::query()->where('email', 'taylor@example.com')->firstOrFail();

    expect($user->first_name)->toBe('Taylor')
        ->and($user->last_name)->toBe('Otwell')
        ->and($user->phone)->toBe('+34600111222')
        ->and($user->status)->toBe(UserStatusEnum::PENDING)
        ->and($user->email_verified_at)->toBeNull()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();

    $this->assertGuest();
});

it('requires a unique email and matching password confirmation', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test('pages::auth.register')
        ->set('firstName', 'Taylor')
        ->set('lastName', 'Otwell')
        ->set('email', 'taken@example.com')
        ->set('password', 'secret-password')
        ->set('passwordConfirmation', 'different-password')
        ->call('register')
        ->assertHasErrors(['email' => 'unique', 'password' => 'same']);
});

it('validates required fields and the phone format', function () {
    Livewire::test('pages::auth.register')
        ->set('phone', '+34 600 111 222')
        ->call('register')
        ->assertHasErrors([
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required',
            'phone' => 'regex',
            'password' => 'required',
            'passwordConfirmation' => 'required',
        ]);
});

it('redirects authenticated users away from registration', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('register'))
        ->assertRedirect(route('pages.index'));
});
