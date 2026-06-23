<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;



Route::get('/', [AuthController::class, 'showlogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');



Route::middleware('auth:admin')->group(function () {

    Route::get('/dashboard', [authController::class, 'dashboard'])->name('dashboard');
    // categories
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoriesController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoriesController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}', [CategoriesController::class, 'edit'])->name('categories.edit');
    Route::patch('/categories/{id}', [CategoriesController::class, 'update'])->name('categories.update');

    Route::delete('/categories/{id}', [CategoriesController::class, 'destroy'])->name('categories.destroy');

    // products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{id}', [ProductController::class, 'update'])->name('products.update');

    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');


    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});