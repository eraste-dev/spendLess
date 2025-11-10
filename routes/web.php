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
        // Income Entries (API for individual income entries)
        Route::get('/income-entries', [\App\Http\Controllers\Budget\IncomeEntryController::class, 'index'])->name('income-entries.index');
        Route::post('/income-entries', [\App\Http\Controllers\Budget\IncomeEntryController::class, 'store'])->name('income-entries.store');
        Route::put('/income-entries/{id}', [\App\Http\Controllers\Budget\IncomeEntryController::class, 'update'])->name('income-entries.update');
        Route::delete('/income-entries/{id}', [\App\Http\Controllers\Budget\IncomeEntryController::class, 'destroy'])->name('income-entries.destroy');
        Route::post('/income-entries/plan-months', [\App\Http\Controllers\Budget\IncomeEntryController::class, 'planMonths'])->name('income-entries.plan-months');

        // Settings (Configuration)
        Route::get('/settings', [\App\Http\Controllers\Budget\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Budget\SettingsController::class, 'update'])->name('settings.update');

        // Incomes
        Route::resource('incomes', \App\Http\Controllers\Budget\IncomeController::class);
        Route::post('incomes/{id}/toggle-active', [
            \App\Http\Controllers\Budget\IncomeController::class,
            'toggleActive'
        ])->name('incomes.toggle-active');
        Route::post('incomes/update-order', [
            \App\Http\Controllers\Budget\IncomeController::class,
            'updateOrder'
        ])->name('incomes.update-order');
        Route::post('incomes/plan-months', [
            \App\Http\Controllers\Budget\IncomeController::class,
            'planMonths'
        ])->name('incomes.plan-months');

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
