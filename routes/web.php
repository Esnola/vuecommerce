<?php
  
  use Illuminate\Support\Facades\Route;
  
  /*
    Route::get('/welcome', function () {
      return view('welcome');
    });
   
  Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
  });
   */
  /*  Route::get('/', function () {
      return view('index');
    });*/
  Route::livewire('/', 'pages::index')->name('pages.index');
  Route::prefix('products')->group(function(){
    Route::livewire('/', 'pages::products.index')->name('products.index');
    Route::livewire('/{slug}', 'pages::products.show')->name('products.show');
  });
  Route::livewire('/contact', 'pages::contact')->name('pages.contact');
