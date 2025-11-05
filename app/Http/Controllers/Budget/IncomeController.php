<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreIncomeRequest;
use App\Http\Requests\Budget\UpdateIncomeRequest;
use App\Repositories\Budget\IncomeCategoryRepository;
use App\Repositories\Budget\IncomeRepository;
use App\Repositories\User\UserSettingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeController extends Controller
{
    public function __construct(
        protected IncomeRepository $incomeRepo,
        protected IncomeCategoryRepository $categoryRepo,
        protected UserSettingRepository $settingRepo
    ) {}

    public function index(Request $request)
    {
        $userId = auth()->id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $incomes = $this->incomeRepo->getAllForUserAndMonth($userId, $year, $month);
        $total = $this->incomeRepo->getTotalForUserAndMonth($userId, $year, $month);
        $categories = $this->categoryRepo->getActiveForUser($userId);
        $settings = $this->settingRepo->getOrCreateForUser($userId);

        return Inertia::render('budget/incomes/index', [
            'incomes' => $incomes,
            'total' => $total,
            'categories' => $categories,
            'settings' => $settings,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    public function store(StoreIncomeRequest $request)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->createForUser($userId, $request->validated());

        return redirect()
            ->route('budget.incomes.index')
            ->with('success', 'Revenu ajouté avec succès.');
    }

    public function update(UpdateIncomeRequest $request, int $id)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->find($id);

        if (!$income || $income->user_id !== $userId) {
            abort(404, 'Revenu non trouvé');
        }

        $this->incomeRepo->updateIncome($id, $request->validated());

        return redirect()
            ->route('budget.incomes.index')
            ->with('success', 'Revenu modifié avec succès.');
    }

    public function destroy(int $id)
    {
        $userId = auth()->id();
        $income = $this->incomeRepo->find($id);

        if (!$income || $income->user_id !== $userId) {
            abort(404, 'Revenu non trouvé');
        }

        $this->incomeRepo->delete($id);

        return redirect()
            ->route('budget.incomes.index')
            ->with('success', 'Revenu supprimé avec succès.');
    }
}
