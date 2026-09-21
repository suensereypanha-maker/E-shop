<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\ModulePlaceholderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Dashboard routes (Sleek dark theme)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// Root JSON endpoints
Route::get('/menu.json', [MenuController::class, 'getJson'])->name('menu.json');
Route::get('/role.json', function () {
    return response()->file(database_path('data/role.json'), ['Content-Type' => 'application/json']);
})->name('role.json');

// Quick Role Switcher for easy demo / testing
Route::get('/switch-role/{role}', function ($role) {
    $user = \App\Models\User::whereHas('role', function ($q) use ($role) {
        $q->where('slug', $role);
    })->first();

    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
    }
    return redirect()->back();
})->name('switch.role');

require __DIR__.'/auth.php';

