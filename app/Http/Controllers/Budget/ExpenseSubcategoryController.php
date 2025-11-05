<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreExpenseSubcategoryRequest;
use App\Http\Requests\Budget\UpdateExpenseSubcategoryRequest;
use App\Repositories\Budget\ExpenseCategoryRepository;
use App\Repositories\Budget\ExpenseSubcategoryRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseSubcategoryController extends Controller
{
    public function __construct(
        protected ExpenseSubcategoryRepository $subcategoryRepo,
        protected ExpenseCategoryRepository $categoryRepo
    ) {}

    /**
     * Display a listing of subcategories for a category.
     */
    public function index(int $categoryId, Request $request)
    {
        $userId = auth()->id();
        $category = $this->categoryRepo->find($categoryId);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $subcategories = $this->subcategoryRepo->getAllForCategory($categoryId);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Subcategories found',
                'data' => ['subcategories' => $subcategories],
                'status' => 200,
            ]);
        }

        return Inertia::render('budget/expense-subcategories/index', [
            'category' => $category,
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Show the form for creating a new subcategory.
     */
    public function create(int $categoryId)
    {
        $userId = auth()->id();
        $category = $this->categoryRepo->find($categoryId);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        return Inertia::render('budget/expense-subcategories/create', [
            'category' => $category,
        ]);
    }

    /**
     * Store a newly created subcategory.
     */
    public function store(StoreExpenseSubcategoryRequest $request)
    {
        $categoryId = $request->validated()['expense_category_id'];
        $userId = auth()->id();
        $category = $this->categoryRepo->find($categoryId);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $subcategory = $this->subcategoryRepo->createForCategory($categoryId, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Subcategory created successfully',
                'data' => ['subcategory' => $subcategory],
                'status' => 201,
            ], 201);
        }

        return redirect()
            ->route('budget.expense-categories.show', $categoryId)
            ->with('success', 'Sous-catégorie créée avec succès.');
    }

    /**
     * Display the specified subcategory.
     */
    public function show(int $id)
    {
        $subcategory = $this->subcategoryRepo->findWithCategory($id);

        if (!$subcategory) {
            abort(404, 'Subcategory not found');
        }

        $userId = auth()->id();
        if ($subcategory->category->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('budget/expense-subcategories/show', [
            'subcategory' => $subcategory,
        ]);
    }

    /**
     * Show the form for editing the specified subcategory.
     */
    public function edit(int $id)
    {
        $subcategory = $this->subcategoryRepo->findWithCategory($id);

        if (!$subcategory) {
            abort(404, 'Subcategory not found');
        }

        $userId = auth()->id();
        if ($subcategory->category->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('budget/expense-subcategories/edit', [
            'subcategory' => $subcategory,
        ]);
    }

    /**
     * Update the specified subcategory.
     */
    public function update(UpdateExpenseSubcategoryRequest $request, int $id)
    {
        $subcategory = $this->subcategoryRepo->findWithCategory($id);

        if (!$subcategory) {
            abort(404, 'Subcategory not found');
        }

        $userId = auth()->id();
        if ($subcategory->category->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $updated = $this->subcategoryRepo->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Subcategory updated successfully',
                'data' => ['subcategory' => $updated],
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.expense-categories.show', $subcategory->expense_category_id)
            ->with('success', 'Sous-catégorie modifiée avec succès.');
    }

    /**
     * Remove the specified subcategory.
     */
    public function destroy(int $id, Request $request)
    {
        $subcategory = $this->subcategoryRepo->findWithCategory($id);

        if (!$subcategory) {
            abort(404, 'Subcategory not found');
        }

        $userId = auth()->id();
        if ($subcategory->category->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $categoryId = $subcategory->expense_category_id;
        $this->subcategoryRepo->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Subcategory deleted successfully',
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.expense-categories.show', $categoryId)
            ->with('success', 'Sous-catégorie supprimée avec succès.');
    }

    /**
     * Toggle the active status of a subcategory.
     */
    public function toggleActive(int $id, Request $request)
    {
        $subcategory = $this->subcategoryRepo->findWithCategory($id);

        if (!$subcategory) {
            abort(404, 'Subcategory not found');
        }

        $userId = auth()->id();
        if ($subcategory->category->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $updated = $this->subcategoryRepo->toggleActive($id, $subcategory->expense_category_id);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Subcategory status toggled successfully',
                'data' => ['subcategory' => $updated],
                'status' => 200,
            ]);
        }

        return back()->with('success', 'Statut de la sous-catégorie modifié avec succès.');
    }

    /**
     * Update the order of subcategories.
     */
    public function updateOrder(int $categoryId, Request $request)
    {
        $userId = auth()->id();
        $category = $this->categoryRepo->find($categoryId);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $orderData = $request->validate([
            '*.id' => 'required|integer',
            '*.order' => 'required|integer',
        ]);

        $this->subcategoryRepo->updateOrder($categoryId, $orderData);

        return response()->json([
            'message' => 'Order updated successfully',
            'status' => 200,
        ]);
    }
}
