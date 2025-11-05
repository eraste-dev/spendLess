<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreExpenseCategoryRequest;
use App\Http\Requests\Budget\UpdateExpenseCategoryRequest;
use App\Repositories\Budget\ExpenseCategoryRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        protected ExpenseCategoryRepository $expenseCategoryRepo
    ) {}

    /**
     * Display a listing of expense categories.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $withSubcategories = $request->input('with_subcategories', true);
        $categories = $this->expenseCategoryRepo->getAllForUser($userId, $withSubcategories);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Categories found',
                'data' => ['categories' => $categories],
                'status' => 200,
            ]);
        }

        return Inertia::render('budget/expense-categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new expense category.
     */
    public function create()
    {
        return Inertia::render('budget/expense-categories/create');
    }

    /**
     * Store a newly created expense category.
     */
    public function store(StoreExpenseCategoryRequest $request)
    {
        $userId = auth()->id();
        $category = $this->expenseCategoryRepo->createForUser($userId, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category created successfully',
                'data' => ['category' => $category],
                'status' => 201,
            ], 201);
        }

        return redirect()
            ->route('budget.expense-categories.index')
            ->with('success', 'Catégorie de dépense créée avec succès.');
    }

    /**
     * Display the specified expense category.
     */
    public function show(int $id)
    {
        $userId = auth()->id();
        $category = $this->expenseCategoryRepo->findWithSubcategoriesForUser($id, $userId);

        if (!$category) {
            abort(404, 'Category not found');
        }

        return Inertia::render('budget/expense-categories/show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified expense category.
     */
    public function edit(int $id)
    {
        $userId = auth()->id();
        $category = $this->expenseCategoryRepo->findWithSubcategoriesForUser($id, $userId);

        if (!$category) {
            abort(404, 'Category not found');
        }

        return Inertia::render('budget/expense-categories/edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified expense category.
     */
    public function update(UpdateExpenseCategoryRequest $request, int $id)
    {
        $userId = auth()->id();
        $category = $this->expenseCategoryRepo->find($id);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $updated = $this->expenseCategoryRepo->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category updated successfully',
                'data' => ['category' => $updated],
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.expense-categories.index')
            ->with('success', 'Catégorie de dépense modifiée avec succès.');
    }

    /**
     * Remove the specified expense category.
     */
    public function destroy(int $id, Request $request)
    {
        $userId = auth()->id();
        $category = $this->expenseCategoryRepo->find($id);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $this->expenseCategoryRepo->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category deleted successfully',
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.expense-categories.index')
            ->with('success', 'Catégorie de dépense supprimée avec succès.');
    }

    /**
     * Toggle the active status of an expense category.
     */
    public function toggleActive(int $id, Request $request)
    {
        $userId = auth()->id();
        $category = $this->expenseCategoryRepo->toggleActive($id, $userId);

        if (!$category) {
            abort(404, 'Category not found');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category status toggled successfully',
                'data' => ['category' => $category],
                'status' => 200,
            ]);
        }

        return back()->with('success', 'Statut de la catégorie modifié avec succès.');
    }

    /**
     * Update the order of expense categories.
     */
    public function updateOrder(Request $request)
    {
        $userId = auth()->id();
        $orderData = $request->validate([
            '*.id' => 'required|integer',
            '*.order' => 'required|integer',
        ]);

        $this->expenseCategoryRepo->updateOrder($userId, $orderData);

        return response()->json([
            'message' => 'Order updated successfully',
            'status' => 200,
        ]);
    }
}
