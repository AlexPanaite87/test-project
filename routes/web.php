<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::post('/products/{id}/search-youtube', [ProductController::class, 'searchYoutube'])->name('products.search-youtube');
