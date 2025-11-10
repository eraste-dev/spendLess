<?php

namespace App\Repositories\Budget;

use App\Models\Budget\IncomeEntry;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class IncomeEntryRepository extends BaseRepository
{
    public function __construct(IncomeEntry $model)
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
            ->with('income')
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

        // Extraire year et month de income_date
        if (isset($data['income_date'])) {
            $incomeDate = Carbon::parse($data['income_date']);
            $data['year'] = $incomeDate->year;
            $data['month'] = $incomeDate->month;
        }

        // Si récurrent, calculer la prochaine occurrence
        if ($data['is_recurring'] ?? false) {
            $incomeDate = Carbon::parse($data['income_date']);
            $recurrenceDay = (int) ($data['recurrence_day'] ?? $incomeDate->day);

            $nextMonth = $incomeDate->copy()->addMonth();
            $day = (int) min($recurrenceDay, $nextMonth->daysInMonth);
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

        // Extraire year et month de income_date si modifié
        if (isset($data['income_date'])) {
            $incomeDate = Carbon::parse($data['income_date']);
            $data['year'] = $incomeDate->year;
            $data['month'] = $incomeDate->month;
        }

        // Recalculer la prochaine occurrence si nécessaire
        if (isset($data['is_recurring']) && $data['is_recurring']) {
            $incomeDate = Carbon::parse($data['income_date'] ?? $income->income_date);
            $recurrenceDay = (int) ($data['recurrence_day'] ?? $incomeDate->day);

            $nextMonth = $incomeDate->copy()->addMonth();
            $day = (int) min($recurrenceDay, $nextMonth->daysInMonth);
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
            ->with('income')
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
            $nextOccurrenceDate = Carbon::parse($income->next_occurrence);

            // Créer la nouvelle occurrence
            $newIncome = $this->create([
                'user_id' => $income->user_id,
                'income_id' => $income->income_id,
                'description' => $income->description,
                'amount' => $income->amount,
                'income_date' => $income->next_occurrence,
                'year' => $nextOccurrenceDate->year,
                'month' => $nextOccurrenceDate->month,
                'is_recurring' => true,
                'recurrence_day' => $income->recurrence_day,
            ]);

            // Calculer la prochaine occurrence
            $nextMonth = $nextOccurrenceDate->addMonth();
            $day = (int) min($income->recurrence_day, $nextMonth->daysInMonth);
            $nextMonth->day = $day;

            // Mettre à jour l'original
            $income->update(['next_occurrence' => $nextMonth->format('Y-m-d')]);

            $created++;
        }

        return $created;
    }

    /**
     * Récupérer tous les mois planifiés pour un utilisateur
     */
    public function getPlannedMonthsForUser(int $userId): array
    {
        $months = $this->model
            ->selectRaw('DISTINCT year, month')
            ->forUser($userId)
            ->planned()
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => ['year' => $item->year, 'month' => $item->month])
            ->toArray();

        return $months;
    }

    /**
     * Vérifier si un mois est planifié
     */
    public function isMonthPlanned(int $userId, int $year, int $month): bool
    {
        return $this->model
            ->forUser($userId)
            ->forMonth($year, $month)
            ->planned()
            ->exists();
    }

    /**
     * Dupliquer les revenus d'un mois vers un ou plusieurs autres mois
     */
    public function duplicateToMonths(int $userId, int $sourceYear, int $sourceMonth, array $targetMonths): int
    {
        $sourceIncomes = $this->getAllForUserAndMonth($userId, $sourceYear, $sourceMonth);
        $created = 0;

        foreach ($targetMonths as $target) {
            foreach ($sourceIncomes as $income) {
                // Créer la date pour le mois cible
                $targetDate = Carbon::create($target['year'], $target['month'], 1);

                $this->create([
                    'user_id' => $userId,
                    'income_id' => $income->income_id,
                    'description' => $income->description,
                    'amount' => $income->amount,
                    'income_date' => $targetDate->format('Y-m-d'),
                    'year' => $target['year'],
                    'month' => $target['month'],
                    'is_planned' => true,
                    'is_recurring' => $income->is_recurring,
                    'recurrence_day' => $income->recurrence_day,
                ]);

                $created++;
            }
        }

        return $created;
    }
}
