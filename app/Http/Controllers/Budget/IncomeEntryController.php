<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreIncomeEntryRequest;
use App\Http\Requests\Budget\UpdateIncomeEntryRequest;
use App\Repositories\Budget\IncomeRepository;
use App\Repositories\Budget\IncomeEntryRepository;
use App\Repositories\User\UserSettingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeEntryController extends Controller
{
    public function __construct(
        protected IncomeEntryRepository $incomeEntryRepo,
        protected IncomeRepository $incomeRepo,
        protected UserSettingRepository $settingRepo
    ) {}

    public function index(Request $request)
    {
        $userId = auth()->id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $incomeEntries = $this->incomeEntryRepo->getAllForUserAndMonth($userId, $year, $month);
        $total = $this->incomeEntryRepo->getTotalForUserAndMonth($userId, $year, $month);
        $incomes = $this->incomeRepo->getActiveForUser($userId);
        $settings = $this->settingRepo->getOrCreateForUser($userId);
        $plannedMonths = $this->incomeEntryRepo->getPlannedMonthsForUser($userId);

        return Inertia::render('budget/income-entries/index', [
            'incomeEntries' => $incomeEntries,
            'total' => $total,
            'incomes' => $incomes,
            'settings' => $settings,
            'currentMonth' => $month,
            'currentYear' => $year,
            'plannedMonths' => $plannedMonths,
        ]);
    }

    public function store(StoreIncomeEntryRequest $request)
    {
        $userId = auth()->id();
        $incomeEntry = $this->incomeEntryRepo->createForUser($userId, $request->validated());

        return redirect()
            ->route('budget.income-entries.index')
            ->with('success', 'Revenu ajouté avec succès.');
    }

    public function update(UpdateIncomeEntryRequest $request, int $id)
    {
        $userId = auth()->id();
        $incomeEntry = $this->incomeEntryRepo->find($id);

        if (!$incomeEntry || $incomeEntry->user_id !== $userId) {
            abort(404, 'Revenu non trouvé');
        }

        $this->incomeEntryRepo->updateIncome($id, $request->validated());

        return redirect()
            ->route('budget.income-entries.index')
            ->with('success', 'Revenu modifié avec succès.');
    }

    public function destroy(int $id)
    {
        $userId = auth()->id();
        $incomeEntry = $this->incomeEntryRepo->find($id);

        if (!$incomeEntry || $incomeEntry->user_id !== $userId) {
            abort(404, 'Revenu non trouvé');
        }

        $this->incomeEntryRepo->delete($id);

        return redirect()
            ->route('budget.income-entries.index')
            ->with('success', 'Revenu supprimé avec succès.');
    }

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
