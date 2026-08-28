<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuditController;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::post('/products/{id}/search-youtube', [ProductController::class, 'searchYoutube'])->name('products.search-youtube');
Route::post('/products/{product}/override/{video}', [App\Http\Controllers\ProductController::class, 'manualOverride'])->name('products.override');
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
