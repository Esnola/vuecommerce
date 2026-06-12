<?php
  
  use App\Http\Controllers\ProductController;
  use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
  
  Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
  });
