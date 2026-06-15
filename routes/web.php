<?php

use App\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::index')->name('pages.index');
Route::livewire('/contact', 'pages::contact')->name('pages.contact');

Route::prefix('products')->group(function () {
    Route::livewire('/', 'pages::products.index')->name('products.index');
    Route::livewire('/{slug}', 'pages::products.show')->name('products.show');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', AuthenticatedSessionController::class)->name('logout');
    Route::livewire('/users/{user}/edit', 'pages::users.edit')->name('users.edit');
    Route::livewire('/purchases', 'pages::purchases.index')->name('purchases.index');
    
    Route::middleware('can:view-orders')->group(function () {
    Route::livewire('/orders', 'pages::orders.index')->name('orders.index');
});
});
