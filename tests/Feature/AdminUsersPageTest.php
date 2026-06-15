<?php

use App\Enums\UserStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

it('forbids customers from listing users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

it('allows admins to list users and open their edit form', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create([
        'first_name' => 'Lucia',
        'last_name' => 'Martin',
        'email' => 'lucia@example.com',
        'phone' => '600123123',
        'status' => UserStatusEnum::ACTIVE,
    ]);
    Order::factory()->count(2)->create(['created_by' => $customer]);

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('Lucia Martin')
        ->assertSee('lucia@example.com')
        ->assertSee('600123123')
        ->assertSee(route('users.edit', $customer))
        ->assertSee('2');
});

it('allows admins to search and filter users', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $activeUser = User::factory()->create([
        'first_name' => 'Searchable',
        'email' => 'active@example.com',
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $suspendedUser = User::factory()->create([
        'first_name' => 'Hidden',
        'email' => 'suspended@example.com',
        'status' => UserStatusEnum::SUSPEND,
    ]);

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->set('search', 'active@example.com')
        ->assertSee($activeUser->email)
        ->assertDontSee($suspendedUser->email)
        ->set('search', '')
        ->set('status', UserStatusEnum::SUSPEND->value)
        ->assertSee($suspendedUser->email)
        ->assertDontSee($activeUser->email);
});

it('shows the users navigation link only to admins', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)
        ->get(route('pages.index'))
        ->assertSee(route('users.index'));

    $this->actingAs($user)
        ->get(route('pages.index'))
        ->assertDontSee('href="'.route('users.index').'"', false);
});
