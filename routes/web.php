<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Budget Routes
    Route::prefix('budget')->name('budget.')->group(function () {
        // Incomes (Revenus)
        Route::get('/incomes', [\App\Http\Controllers\Budget\IncomeController::class, 'index'])->name('incomes.index');
        Route::post('/incomes', [\App\Http\Controllers\Budget\IncomeController::class, 'store'])->name('incomes.store');
        Route::put('/incomes/{id}', [\App\Http\Controllers\Budget\IncomeController::class, 'update'])->name('incomes.update');
        Route::delete('/incomes/{id}', [\App\Http\Controllers\Budget\IncomeController::class, 'destroy'])->name('incomes.destroy');

        // Settings (Configuration)
        Route::get('/settings', [\App\Http\Controllers\Budget\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Budget\SettingsController::class, 'update'])->name('settings.update');

        // Income Categories
        Route::resource('income-categories', \App\Http\Controllers\Budget\IncomeCategoryController::class);
        Route::post('income-categories/{id}/toggle-active', [
            \App\Http\Controllers\Budget\IncomeCategoryController::class,
            'toggleActive'
        ])->name('income-categories.toggle-active');
        Route::post('income-categories/update-order', [
            \App\Http\Controllers\Budget\IncomeCategoryController::class,
            'updateOrder'
        ])->name('income-categories.update-order');

        // Expense Categories
        Route::resource('expense-categories', \App\Http\Controllers\Budget\ExpenseCategoryController::class);
        Route::post('expense-categories/{id}/toggle-active', [
            \App\Http\Controllers\Budget\ExpenseCategoryController::class,
            'toggleActive'
        ])->name('expense-categories.toggle-active');
        Route::post('expense-categories/update-order', [
            \App\Http\Controllers\Budget\ExpenseCategoryController::class,
            'updateOrder'
        ])->name('expense-categories.update-order');

        // Expense Subcategories
        Route::get('expense-categories/{categoryId}/subcategories', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'index'
        ])->name('expense-subcategories.index');
        Route::get('expense-categories/{categoryId}/subcategories/create', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'create'
        ])->name('expense-subcategories.create');
        Route::post('expense-subcategories', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'store'
        ])->name('expense-subcategories.store');
        Route::get('expense-subcategories/{id}', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'show'
        ])->name('expense-subcategories.show');
        Route::get('expense-subcategories/{id}/edit', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'edit'
        ])->name('expense-subcategories.edit');
        Route::put('expense-subcategories/{id}', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'update'
        ])->name('expense-subcategories.update');
        Route::delete('expense-subcategories/{id}', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'destroy'
        ])->name('expense-subcategories.destroy');
        Route::post('expense-subcategories/{id}/toggle-active', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'toggleActive'
        ])->name('expense-subcategories.toggle-active');
        Route::post('expense-categories/{categoryId}/subcategories/update-order', [
            \App\Http\Controllers\Budget\ExpenseSubcategoryController::class,
            'updateOrder'
        ])->name('expense-subcategories.update-order');
    });
});

require __DIR__.'/settings.php';
