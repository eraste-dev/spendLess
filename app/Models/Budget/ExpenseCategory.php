<?php

namespace App\Models\Budget;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_percentage',
        'target_amount',
        'color',
        'order',
        'is_active',
        'is_recurring_monthly',
        'recurrence_day',
    ];

    protected $casts = [
        'target_percentage' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_recurring_monthly' => 'boolean',
        'order' => 'integer',
        'recurrence_day' => 'integer',
    ];

    /**
     * Get the user that owns the expense category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subcategories for the expense category.
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(ExpenseSubcategory::class);
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get recurring categories.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring_monthly', true);
    }

    /**
     * Scope to order categories by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
