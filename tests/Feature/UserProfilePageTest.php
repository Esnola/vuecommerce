<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $user = User::factory()->create();

    $this->get(route('users.edit', $user))
        ->assertRedirect(route('login'));
});

it('allows a user to view and update their own profile', function () {
    $user = User::factory()->create([
        'email' => 'before@example.com',
        'email_verified_at' => now(),
        'is_admin' => false,
    ]);

    $this->actingAs($user)
        ->get(route('users.edit', $user))
        ->assertSuccessful()
        ->assertSee('before@example.com')
        ->assertDontSee('Administration');

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $user])
        ->set('firstName', 'Maria')
        ->set('lastName', 'Lopez')
        ->set('email', 'maria@example.com')
        ->set('phone', '+34600123123')
        ->set('isAdmin', true)
        ->set('status', UserStatusEnum::SUSPEND->value)
        ->set('password', 'new-password')
        ->set('passwordConfirmation', 'new-password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    $user->refresh();

    expect($user->first_name)->toBe('Maria')
        ->and($user->last_name)->toBe('Lopez')
        ->and($user->email)->toBe('maria@example.com')
        ->and($user->phone)->toBe('+34600123123')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->is_admin)->toBeFalse()
        ->and($user->status)->toBe(UserStatusEnum::PENDING)
        ->and(Hash::check('new-password', $user->password))->toBeTrue();
});

it('forbids a user from viewing or updating another user', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.edit', $otherUser))
        ->assertForbidden();

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $otherUser])
        ->assertForbidden();
});

it('forbids pending and suspended users from accessing their profile', function (UserStatusEnum $status, bool $verified) {
    $user = User::factory()->create([
        'email_verified_at' => $verified ? now() : null,
        'status' => $status,
    ]);

    $this->actingAs($user)
        ->get(route('users.edit', $user))
        ->assertForbidden();
})->with([
    'pending' => [UserStatusEnum::PENDING, false],
    'suspended' => [UserStatusEnum::SUSPEND, true],
]);

it('allows an administrator to update every editable user field', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->unverified()->create([
        'is_admin' => false,
        'status' => UserStatusEnum::PENDING,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::users.edit', ['user' => $user])
        ->set('firstName', 'Admin edited')
        ->set('lastName', 'User')
        ->set('email', 'edited@example.com')
        ->set('phone', '611222333')
        ->set('status', UserStatusEnum::ACTIVE->value)
        ->set('isAdmin', true)
        ->set('emailVerified', true)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->first_name)->toBe('Admin edited')
        ->and($user->last_name)->toBe('User')
        ->and($user->email)->toBe('edited@example.com')
        ->and($user->phone)->toBe('611222333')
        ->and($user->status)->toBe(UserStatusEnum::ACTIVE)
        ->and($user->is_admin)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();
});

it('requires a unique email and a confirmed password', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $user])
        ->set('email', $otherUser->email)
        ->set('password', 'new-password')
        ->set('passwordConfirmation', 'different-password')
        ->call('save')
        ->assertHasErrors(['email' => 'unique', 'password' => 'same']);
});

it('only accepts statuses defined by the user status enum', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test('pages::users.edit', ['user' => $user])
        ->assertSet('canManageUser', true)
        ->set('phone', '')
        ->set('status', 'inactive')
        ->call('save')
        ->assertHasErrors(['status']);
});

it('only accepts digits with an optional leading plus sign in the phone', function (string $phone) {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $user])
        ->set('phone', $phone)
        ->call('save')
        ->assertHasErrors(['phone']);
})->with([
    'letters' => '+34phone',
    'spaces' => '+34 600 123 123',
    'hyphens' => '+34-600-123-123',
    'plus sign after digits' => '34+600123123',
    'multiple plus signs' => '++34600123123',
]);

it('uploads an avatar to the public avatars directory', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $avatar = UploadedFile::fake()->image('avatar.jpg', 680, 680)->size(1000);

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $user])
        ->set('phone', '')
        ->set('avatar', $avatar)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar)->toStartWith('avatars/');
    Storage::disk('public')->assertExists($user->avatar);
});

it('replaces the previous avatar when a new one is uploaded', function () {
    Storage::fake('public');
    Storage::disk('public')->put('avatars/old-avatar.jpg', 'old avatar');

    $user = User::factory()->create(['avatar' => 'avatars/old-avatar.jpg']);
    $avatar = UploadedFile::fake()->image('new-avatar.jpg', 400, 400);

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $user])
        ->set('phone', '')
        ->set('avatar', $avatar)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');
    Storage::disk('public')->assertExists($user->avatar);
});

it('rejects invalid avatar uploads', function (UploadedFile $avatar) {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::users.edit', ['user' => $user])
        ->set('phone', '')
        ->set('avatar', $avatar)
        ->call('save')
        ->assertHasErrors(['avatar']);

    expect($user->refresh()->avatar)->toBeNull();
})->with([
    'non-image file' => [fn () => UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf')],
    'file over one megabyte' => [fn () => UploadedFile::fake()->image('avatar.jpg', 400, 400)->size(1001)],
    'image wider than 680 pixels' => [fn () => UploadedFile::fake()->image('avatar.jpg', 681, 680)],
    'image taller than 680 pixels' => [fn () => UploadedFile::fake()->image('avatar.jpg', 680, 681)],
]);
