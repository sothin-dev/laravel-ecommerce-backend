<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;



Route::get('/', [AuthController::class, 'showlogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');



Route::middleware('auth:admin')->group(function () {

    Route::get('/dashboard', [authController::class, 'dashboard'])->name('dashboard');
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoriesController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoriesController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}', [CategoriesController::class, 'edit'])->name('categories.edit');
    Route::patch('/categories/{id}', [CategoriesController::class, 'update'])->name('categories.update');

    Route::delete('/categories/{id}', [CategoriesController::class, 'destroy'])->name('categories.destroy');



    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});