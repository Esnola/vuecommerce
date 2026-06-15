<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the login page to guests', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Sign In')
        ->assertSee('test@example.com');

    $this->get(route('pages.index'))
        ->assertSee(route('login'));
});

it('authenticates a user and regenerates the session', function () {
    $user = User::factory()->create([
        'email' => 'buyer@example.com',
        'password' => 'password',
    ]);
    $previousSessionId = session()->getId();

    Livewire::test('pages::auth.login')
        ->set('email', 'buyer@example.com')
        ->set('password', 'password')
        ->set('remember', true)
        ->call('login')
        ->assertRedirect(route('pages.index'));

    $this->assertAuthenticatedAs($user);
    expect(session()->getId())->not->toBe($previousSessionId);
});

it('rejects invalid credentials', function () {
    User::factory()->create([
        'email' => 'buyer@example.com',
        'password' => 'password',
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', 'buyer@example.com')
        ->set('password', 'incorrect-password')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

it('rejects a pending user and explains that administrator review is required', function () {
    User::factory()->create([
        'email' => 'pending@example.com',
        'password' => 'password',
        'status' => UserStatusEnum::PENDING,
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', 'pending@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors([
            'email' => trans('auth.pending'),
        ]);

    $this->assertGuest();
});

it('rejects an active user whose email is not verified', function () {
    User::factory()->create([
        'email' => 'unverified@example.com',
        'password' => 'password',
        'email_verified_at' => null,
        'status' => UserStatusEnum::ACTIVE,
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', 'unverified@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors([
            'email' => trans('auth.unverified'),
        ]);

    $this->assertGuest();
});

it('prioritizes the email verification message when a pending user is unverified', function () {
    User::factory()->unverified()->create([
        'email' => 'pending-unverified@example.com',
        'password' => 'password',
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', 'pending-unverified@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors([
            'email' => trans('auth.unverified'),
        ]);

    $this->assertGuest();
});

it('rejects a suspended user and explains that the account is suspended', function () {
    User::factory()->create([
        'email' => 'suspended@example.com',
        'password' => 'password',
        'status' => UserStatusEnum::SUSPEND,
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', 'suspended@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors([
            'email' => trans('auth.suspended'),
        ]);

    $this->assertGuest();
});

it('redirects authenticated users away from login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('pages.index'));
});

it('logs out the current user and invalidates the session', function () {
    $user = User::factory()->create();
    $previousSessionId = session()->getId();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('pages.index'));

    $this->assertGuest();
    expect(session()->getId())->not->toBe($previousSessionId);
});

it('redirects guests to login before accessing orders', function () {
    $this->get(route('orders.index'))
        ->assertRedirect(route('login'));
});

it('redirects an admin to the originally requested orders page after login', function () {
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
        'is_admin' => true,
    ]);

    $this->get(route('orders.index'))
        ->assertRedirect(route('login'));

    Livewire::test('pages::auth.login')
        ->set('email', 'admin@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('orders.index'));
});
