<?php

use App\Enums\UserStatusEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $this->get(route('favorites.index'))
        ->assertRedirect(route('login'));
});

it('forbids users whose account cannot be accessed', function (UserStatusEnum $status, bool $verified) {
    $user = User::factory()->create([
        'email_verified_at' => $verified ? now() : null,
        'status' => $status,
    ]);

    $this->actingAs($user)
        ->get(route('favorites.index'))
        ->assertForbidden();
})->with([
    'pending' => [UserStatusEnum::PENDING, true],
    'unverified' => [UserStatusEnum::ACTIVE, false],
    'suspended' => [UserStatusEnum::SUSPEND, true],
]);

it('only displays favorites belonging to the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $favorite = Product::factory()->create([
        'title' => 'Favorite Keyboard',
        'slug' => 'favorite-keyboard',
    ]);
    $otherFavorite = Product::factory()->create([
        'title' => 'Private Monitor',
        'slug' => 'private-monitor',
    ]);

    $user->favoriteProducts()->attach($favorite);
    $otherUser->favoriteProducts()->attach($otherFavorite);

    $this->actingAs($user)
        ->get(route('favorites.index'))
        ->assertSuccessful()
        ->assertSee('My favorites')
        ->assertSee('Favorite Keyboard')
        ->assertSee(route('products.show', $favorite->slug))
        ->assertDontSee('Private Monitor');
});

it('removes a favorite from the page', function () {
    $user = User::factory()->create();
    $favorite = Product::factory()->create([
        'title' => 'Removable Keyboard',
    ]);
    $user->favoriteProducts()->attach($favorite);

    $this->actingAs($user);

    Livewire::test('pages::favorites.index')
        ->assertSee('Removable Keyboard')
        ->call('removeFavorite', $favorite->id)
        ->assertDontSee('Removable Keyboard')
        ->assertSee('You have no favorite products yet.');

    expect($user->favoriteProducts()->whereKey($favorite->id)->exists())->toBeFalse();
});

it('shows an empty state and catalog link', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('favorites.index'))
        ->assertSuccessful()
        ->assertSee('You have no favorite products yet.')
        ->assertSee(route('products.index'));
});

it('shows favorites links to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pages.index'))
        ->assertSee(route('favorites.index'));
});
