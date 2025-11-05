<?php

namespace App\Repositories\Budget;

use App\Models\Budget\Income;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class IncomeRepository extends BaseRepository
{
    public function __construct(Income $model)
    {
        parent::__construct($model);
    }

    /**
     * Récupérer tous les revenus d'un utilisateur pour un mois donné
     */
    public function getAllForUserAndMonth(int $userId, ?int $year = null, ?int $month = null): mixed
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return $this->model
            ->with('category')
            ->forUser($userId)
            ->forMonth($year, $month)
            ->orderBy('income_date', 'desc')
            ->get();
    }

    /**
     * Récupérer le total des revenus d'un utilisateur pour un mois donné
     */
    public function getTotalForUserAndMonth(int $userId, ?int $year = null, ?int $month = null): float
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return (float) $this->model
            ->forUser($userId)
            ->forMonth($year, $month)
            ->sum('amount');
    }

    /**
     * Créer un revenu pour un utilisateur
     */
    public function createForUser(int $userId, array $data): mixed
    {
        $data['user_id'] = $userId;

        // Si récurrent, calculer la prochaine occurrence
        if ($data['is_recurring'] ?? false) {
            $incomeDate = Carbon::parse($data['income_date']);
            $recurrenceDay = $data['recurrence_day'] ?? $incomeDate->day;

            $nextMonth = $incomeDate->copy()->addMonth();
            $day = min($recurrenceDay, $nextMonth->daysInMonth);
            $nextMonth->day = $day;

            $data['next_occurrence'] = $nextMonth->format('Y-m-d');
            $data['recurrence_day'] = $recurrenceDay;
        }

        return $this->create($data);
    }

    /**
     * Mettre à jour un revenu
     */
    public function updateIncome(int $id, array $data): mixed
    {
        $income = $this->find($id);

        // Recalculer la prochaine occurrence si nécessaire
        if (isset($data['is_recurring']) && $data['is_recurring']) {
            $incomeDate = Carbon::parse($data['income_date'] ?? $income->income_date);
            $recurrenceDay = $data['recurrence_day'] ?? $incomeDate->day;

            $nextMonth = $incomeDate->copy()->addMonth();
            $day = min($recurrenceDay, $nextMonth->daysInMonth);
            $nextMonth->day = $day;

            $data['next_occurrence'] = $nextMonth->format('Y-m-d');
            $data['recurrence_day'] = $recurrenceDay;
        } else {
            $data['next_occurrence'] = null;
            $data['recurrence_day'] = null;
        }

        return $this->update($id, $data);
    }

    /**
     * Récupérer les revenus récurrents à créer
     */
    public function getRecurringIncomesForNextMonth(int $userId): mixed
    {
        return $this->model
            ->with('category')
            ->forUser($userId)
            ->recurring()
            ->whereNotNull('next_occurrence')
            ->where('next_occurrence', '<=', now())
            ->get();
    }

    /**
     * Créer les occurrences récurrentes
     */
    public function createRecurringOccurrences(int $userId): int
    {
        $recurringIncomes = $this->getRecurringIncomesForNextMonth($userId);
        $created = 0;

        foreach ($recurringIncomes as $income) {
            // Créer la nouvelle occurrence
            $newIncome = $this->create([
                'user_id' => $income->user_id,
                'income_category_id' => $income->income_category_id,
                'description' => $income->description,
                'amount' => $income->amount,
                'income_date' => $income->next_occurrence,
                'is_recurring' => true,
                'recurrence_day' => $income->recurrence_day,
            ]);

            // Calculer la prochaine occurrence
            $nextMonth = Carbon::parse($income->next_occurrence)->addMonth();
            $day = min($income->recurrence_day, $nextMonth->daysInMonth);
            $nextMonth->day = $day;

            // Mettre à jour l'original
            $income->update(['next_occurrence' => $nextMonth->format('Y-m-d')]);

            $created++;
        }

        return $created;
    }
}
