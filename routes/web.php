<?php
  
  use App\Http\Controllers\ProductController;
  use Illuminate\Support\Facades\Route;
  
  /*
    Route::get('/welcome', function () {
      return view('welcome');
    });
    */
  Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
  });
  
  /*  Route::get('/', function () {
      return view('index');
    });*/
  Route::livewire('/', 'pages::index')->name('pages.index');
  Route::livewire('/products', 'pages::products.index')->name('products.index');
  Route::livewire('/contact', 'pages::contact')->name('pages.contact');
