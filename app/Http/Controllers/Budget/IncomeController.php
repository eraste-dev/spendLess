<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreIncomeRequest;
use App\Http\Requests\Budget\UpdateIncomeRequest;
use App\Repositories\Budget\IncomeRepository;
use App\Repositories\User\UserSettingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Repositories\Budget\IncomeEntryRepository;

class IncomeController extends Controller
{
    public function __construct(
        protected IncomeRepository $incomeRepo,
        protected UserSettingRepository $settingRepo,
        protected IncomeEntryRepository $incomeEntryRepo
    ) {}

    /**
     * Display a listing of incomes.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $incomes = $this->incomeRepo->getAllForUser($userId);
        $settings = $this->settingRepo->getOrCreateForUser($userId);

        // Get income entries for the selected month
        $incomeEntries = $this->incomeEntryRepo->getAllForUserAndMonth($userId, $year, $month);
        $totalIncome = $this->incomeEntryRepo->getTotalForUserAndMonth($userId, $year, $month);
        $plannedMonths = $this->incomeEntryRepo->getPlannedMonthsForUser($userId);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Incomes found',
                'data' => [
                    'incomes' => $incomes,
                    'incomeEntries' => $incomeEntries,
                    'totalIncome' => $totalIncome,
                ],
                'status' => 200,
            ]);
        }

        return Inertia::render('budget/incomes/index', [
            'incomes' => $incomes,
            'settings' => $settings,
            'incomeEntries' => $incomeEntries,
            'totalIncome' => $totalIncome,
            'currentMonth' => $month,
            'currentYear' => $year,
            'plannedMonths' => $plannedMonths,
        ]);
    }

    /**
     * Show the form for creating a new income.
     */
    public function create()
    {
        return Inertia::render('budget/incomes/create');
    }

    /**
     * Store a newly created income.
     */
    public function store(StoreIncomeRequest $request)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->createForUser($userId, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Income created successfully',
                'data' => ['income' => $income],
                'status' => 201,
            ], 201);
        }

        return redirect()
            ->route('budget.incomes.index')
            ->with('success', 'Revenu créé avec succès.');
    }

    /**
     * Display the specified income.
     */
    public function show(int $id)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->find($id);

        if (!$income || $income->user_id !== $userId) {
            abort(404, 'Income not found');
        }

        return Inertia::render('budget/incomes/show', [
            'income' => $income,
        ]);
    }

    /**
     * Show the form for editing the specified income.
     */
    public function edit(int $id)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->find($id);

        if (!$income || $income->user_id !== $userId) {
            abort(404, 'Income not found');
        }

        return Inertia::render('budget/incomes/edit', [
            'income' => $income,
        ]);
    }

    /**
     * Update the specified income.
     */
    public function update(UpdateIncomeRequest $request, int $id)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->find($id);

        if (!$income || $income->user_id !== $userId) {
            abort(404, 'Income not found');
        }

        $updated = $this->incomeRepo->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Income updated successfully',
                'data' => ['income' => $updated],
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.incomes.index')
            ->with('success', 'Revenu modifié avec succès.');
    }

    /**
     * Remove the specified income.
     */
    public function destroy(int $id, Request $request)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->find($id);

        if (!$income || $income->user_id !== $userId) {
            abort(404, 'Income not found');
        }

        $this->incomeRepo->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Income deleted successfully',
                'status' => 200,
            ]);
        }

        return redirect()
            ->route('budget.incomes.index')
            ->with('success', 'Revenu supprimé avec succès.');
    }

    /**
     * Toggle the active status of an income.
     */
    public function toggleActive(int $id, Request $request)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->toggleActive($id, $userId);

        if (!$income) {
            abort(404, 'Income not found');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Income status toggled successfully',
                'data' => ['income' => $income],
                'status' => 200,
            ]);
        }

        return back()->with('success', 'Statut du revenu modifié avec succès.');
    }

    /**
     * Update the order of incomes.
     */
    public function updateOrder(Request $request)
    {
        $userId = auth()->id();
        $orderData = $request->validate([
            '*.id' => 'required|integer',
            '*.order' => 'required|integer',
        ]);

        $this->incomeRepo->updateOrder($userId, $orderData);

        return response()->json([
            'message' => 'Order updated successfully',
            'status' => 200,
        ]);
    }

    /**
     * Plan months by duplicating income entries
     */
    public function planMonths(Request $request)
    {
        $validated = $request->validate([
            'source_year' => 'required|integer|min:2000|max:2100',
            'source_month' => 'required|integer|min:1|max:12',
            'target_months' => 'required|array|min:1',
            'target_months.*.year' => 'required|integer|min:2000|max:2100',
            'target_months.*.month' => 'required|integer|min:1|max:12',
        ]);

        $userId = auth()->id();

        $created = $this->incomeEntryRepo->duplicateToMonths(
            $userId,
            $validated['source_year'],
            $validated['source_month'],
            $validated['target_months']
        );

        return back()->with('success', "$created revenus ont été planifiés avec succès.");
    }
}
