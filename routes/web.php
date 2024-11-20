<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MovieController;

Route::get('/', [MovieController::class, 'index'])->name('movies.index');
Route::post('/search', [MovieController::class, 'search'])->name('movies.search');
Route::get('/load-more', [MovieController::class, 'loadMore'])->name('movies.loadMore');
Route::delete('/history/{id}', [MovieController::class, 'deleteHistory'])->name('movies.deleteHistory');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';