<?php

namespace App\Models\Budget;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'income_id',
        'description',
        'amount',
        'income_date',
        'year',
        'month',
        'is_planned',
        'is_recurring',
        'recurrence_day',
        'next_occurrence',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'next_occurrence' => 'date',
        'is_recurring' => 'boolean',
        'is_planned' => 'boolean',
        'recurrence_day' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le revenu (income source/type)
     */
    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class, 'income_id');
    }

    /**
     * Scope pour les revenus d'un utilisateur
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les revenus d'un mois spécifique
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)
                     ->where('month', $month);
    }

    /**
     * Scope pour les mois planifiés
     */
    public function scopePlanned($query)
    {
        return $query->where('is_planned', true);
    }

    /**
     * Scope pour les revenus récurrents
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Calculer la prochaine occurrence si récurrent
     */
    public function calculateNextOccurrence(): ?string
    {
        if (!$this->is_recurring || !$this->recurrence_day) {
            return null;
        }

        $currentDate = $this->income_date;
        $nextMonth = $currentDate->copy()->addMonth();

        // Ajuster si le jour n'existe pas dans le mois suivant
        $day = (int) min($this->recurrence_day, $nextMonth->daysInMonth);
        $nextMonth->day = $day;

        return $nextMonth->format('Y-m-d');
    }
}
