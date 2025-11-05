<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreIncomeCategoryRequest;
use App\Http\Requests\Budget\UpdateIncomeCategoryRequest;
use App\Repositories\Budget\IncomeCategoryRepository;
use App\Repositories\User\UserSettingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeCategoryController extends Controller
{
    public function __construct(
        protected IncomeCategoryRepository $incomeCategoryRepo,
        protected UserSettingRepository $settingRepo
    ) {}

    /**
     * Display a listing of income categories.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $categories = $this->incomeCategoryRepo->getAllForUser($userId);
        $settings = $this->settingRepo->getOrCreateForUser($userId);

        // Calculate total income from categories
        $totalIncome = $categories->where('is_active', true)
            ->sum('amount');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Categories found',
                'data' => [
                    'categories' => $categories,
                    'totalIncome' => $totalIncome,
                ],
                'status' => 200,
            ]);
        }

        return Inertia::render('budget/income-categories/index', [
            'categories' => $categories,
            'settings' => $settings,
            'totalIncome' => $totalIncome,
        ]);
    }

    /**
     * Show the form for creating a new income category.
     */
    public function create()
    {
        return Inertia::render('budget/income-categories/create');
    }

    /**
     * Store a newly created income category.
     */
    public function store(StoreIncomeCategoryRequest $request)
    {
        $userId = auth()->id();
        $category = $this->incomeCategoryRepo->createForUser($userId, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category created successfully',
                'data' => ['category' => $category],
                'status' => 201,
            ], 201);
        }

        return redirect()
            ->route('budget.income-categories.index')
            ->with('success', 'Catégorie de revenu créée avec succès.');
    }

    /**
     * Display the specified income category.
     */
    public function show(int $id)
    {
        $userId = auth()->id();
        $category = $this->incomeCategoryRepo->find($id);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        return Inertia::render('budget/income-categories/show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified income category.
     */
    public function edit(int $id)
    {
        $userId = auth()->id();
        $category = $this->incomeCategoryRepo->find($id);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        return Inertia::render('budget/income-categories/edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified income category.
     */
    public function update(UpdateIncomeCategoryRequest $request, int $id)
    {
        $userId = auth()->id();
        $category = $this->incomeCategoryRepo->find($id);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $updated = $this->incomeCategoryRepo->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category updated successfully',
                'data' => ['category' => $updated],
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.income-categories.index')
            ->with('success', 'Catégorie de revenu modifiée avec succès.');
    }

    /**
     * Remove the specified income category.
     */
    public function destroy(int $id, Request $request)
    {
        $userId = auth()->id();
        $category = $this->incomeCategoryRepo->find($id);

        if (!$category || $category->user_id !== $userId) {
            abort(404, 'Category not found');
        }

        $this->incomeCategoryRepo->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Category deleted successfully',
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.income-categories.index')
            ->with('success', 'Catégorie de revenu supprimée avec succès.');
    }

    /**
     * Toggle the active status of an income category.
     */
    public function toggleActive(int $id, Request $request)
    {
        $userId = auth()->id();
        $category = $this->incomeCategoryRepo->toggleActive($id, $userId);

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
     * Update the order of income categories.
     */
    public function updateOrder(Request $request)
    {
        $userId = auth()->id();
        $orderData = $request->validate([
            '*.id' => 'required|integer',
            '*.order' => 'required|integer',
        ]);

        $this->incomeCategoryRepo->updateOrder($userId, $orderData);

        return response()->json([
            'message' => 'Order updated successfully',
            'status' => 200,
        ]);
    }
}
