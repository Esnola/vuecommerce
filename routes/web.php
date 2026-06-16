<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\FavoriteController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::index')->name('pages.index');
Route::livewire('/contact', 'pages::contact')->name('pages.contact');
Route::livewire('/preview', 'pages::product-view')->name('page.preview');

Route::prefix('products')->group(function () {
    Route::livewire('/', 'pages::products.index')->name('products.index');
    Route::livewire('/{slug}', 'pages::products.show')->name('products.show');
    // Route::livewire('/{slug}', 'pages::products.product-show')->name('products.show');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
    Route::livewire('/register', 'pages::auth.register')->name('register');
});

Route::get('/email/verify/{id}/{hash}', function (string $id, string $hash) {
    $user = User::query()->findOrFail($id);

    abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect()->route('login')->with(
        'registration-status',
        __('Your email address has been verified. You can now sign in.'),
    );
})->middleware('signed')->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::post('/logout', AuthenticatedSessionController::class)->name('logout');
    Route::post('/favorites/sync', [FavoriteController::class, 'sync'])->name('favorites.sync');
    Route::post('/favorites/{product}/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/cart/{product}', [CartItemController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{product}', [CartItemController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartItemController::class, 'destroy'])->name('cart.destroy');
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/cart', 'pages::cart.index')->name('cart.index');
    Route::livewire('/favorites', 'pages::favorites.index')->name('favorites.index');
    Route::livewire('/users', 'pages::users.index')->name('users.index');
    Route::livewire('/users/{user}/edit', 'pages::users.edit')->name('users.edit');
    Route::livewire('/purchases', 'pages::purchases.index')->name('purchases.index');

    Route::middleware('can:view-orders')->group(function () {
        Route::livewire('/orders', 'pages::orders.index')->name('orders.index');
    });
});
