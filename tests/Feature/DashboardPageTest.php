<?php

use App\Enums\UserStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('shows profile and purchases actions to customers', function () {
    $user = User::factory()->create(['is_admin' => false]);
    Order::factory()->count(2)->create(['created_by' => $user]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertSee(route('users.edit', $user))
        ->assertSee(route('purchases.index'))
        ->assertSee('2 orders')
        ->assertDontSee('href="'.route('users.index').'"', false)
        ->assertDontSee('href="'.route('orders.index').'"', false);
});

it('shows administration actions and totals to admins', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->count(2)->create();
    $buyer = User::factory()->create();
    Order::factory()->count(3)->create(['created_by' => $buyer]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(route('users.index'))
        ->assertSee(route('orders.index'))
        ->assertSee('4 registered users')
        ->assertSee('3 orders in the store');
});

it('forbids accounts without access', function (UserStatusEnum $status, bool $verified) {
    $user = User::factory()->create([
        'email_verified_at' => $verified ? now() : null,
        'status' => $status,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
})->with([
    'unverified' => [UserStatusEnum::ACTIVE, false],
    'pending' => [UserStatusEnum::PENDING, true],
    'suspended' => [UserStatusEnum::SUSPEND, true],
]);
