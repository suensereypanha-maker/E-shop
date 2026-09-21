<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\ModulePlaceholderController;
use App\Http\Controllers\Backend\SizeController;
use App\Http\Controllers\Backend\ColorController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SupplierController;



Route::prefix('backend')->name('backend.')->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/menu/json', [MenuController::class, 'getJson'])->name('menu.json');
    Route::get('/menu/download', [MenuController::class, 'downloadJson'])->name('menu.download');

    Route::get('/role/json', function () {
        return response()->file(database_path('data/role.json'), ['Content-Type' => 'application/json']);
    })->name('role.json');


    Route::get('/sizes', [SizeController::class, 'index'])->name('size.index');
    Route::post('/sizes', [SizeController::class, 'store'])->name('size.store');
    Route::put('/sizes/{size}', [SizeController::class, 'update'])->name('size.update');
    Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('size.destroy');
    Route::post('/sizes/{size}/toggle-status', [SizeController::class, 'toggleStatus'])->name('size.toggle-status');

    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::put('/colors/{color}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');

    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Stakeholders & Contacts - Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('/registered-customers', [ModulePlaceholderController::class, 'show'])->defaults('module', 'registered-customers')->name('registered-customers.index');

    // Example route protected by role middleware
    Route::get('/admin-only', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Authorized! You are logged in as Admin: ' . auth()->user()->name,
            'role_title' => auth()->user()->role_title,
            'role_slug' => auth()->user()->role_slug,
            'role_table_id' => auth()->user()->role_id,
        ]);
    })->middleware(['auth', 'role:admin'])->name('admin.only');

});
