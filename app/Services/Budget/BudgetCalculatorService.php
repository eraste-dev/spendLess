<?php

namespace App\Services\Budget;

use App\Repositories\Budget\IncomeRepository;
use App\Repositories\Budget\ExpenseCategoryRepository;
use Carbon\Carbon;

class BudgetCalculatorService
{
    public function __construct(
        protected IncomeRepository $incomeRepo,
        protected ExpenseCategoryRepository $expenseCategoryRepo
    ) {}

    /**
     * Calculate remaining budget for today based on monthly income and daily allocation
     */
    public function getRemainingBudgetForToday(int $userId): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $daysInMonth = $endOfMonth->day;
        $currentDay = $now->day;
        $daysRemaining = $endOfMonth->diffInDays($now) + 1;

        // Get total monthly income from active incomes
        $incomes = $this->incomeRepo->getActiveForUser($userId);
        $totalMonthlyIncome = $incomes->where('is_monthly', true)->sum('amount') ?? 0;

        // Get total monthly expenses (target amounts)
        $expenseCategories = $this->expenseCategoryRepo->getActiveForUser($userId);
        $totalMonthlyExpenses = $expenseCategories->sum('target_amount') ?? 0;

        // Calculate daily budget allocation
        $monthlyBudget = $totalMonthlyIncome - $totalMonthlyExpenses;
        $dailyBudget = $daysInMonth > 0 ? $monthlyBudget / $daysInMonth : 0;

        // Budget remaining for today (assuming linear spending)
        $budgetUsedSoFar = $dailyBudget * ($currentDay - 1);
        $remainingBudget = $monthlyBudget - $budgetUsedSoFar;

        // Budget for today specifically
        $todayBudget = $dailyBudget;

        return [
            'monthly_income' => $totalMonthlyIncome,
            'monthly_expenses' => $totalMonthlyExpenses,
            'monthly_budget' => $monthlyBudget,
            'daily_budget' => $dailyBudget,
            'today_budget' => $todayBudget,
            'remaining_budget' => $remainingBudget,
            'days_remaining' => $daysRemaining,
            'current_day' => $currentDay,
            'days_in_month' => $daysInMonth,
        ];
    }

    /**
     * Get budget summary for display
     */
    public function getBudgetSummary(int $userId): array
    {
        $budgetData = $this->getRemainingBudgetForToday($userId);

        return [
            'today_budget' => $budgetData['today_budget'],
            'remaining_this_month' => $budgetData['remaining_budget'],
            'days_remaining' => $budgetData['days_remaining'],
        ];
    }
}
