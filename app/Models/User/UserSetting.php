<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency',
        'currency_symbol',
        'thousand_separator',
        'decimal_separator',
        'decimal_places',
        'date_format',
        'locale',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Devises disponibles
     */
    public static function availableCurrencies(): array
    {
        return [
            'XOF' => ['symbol' => 'FCFA', 'name' => 'Franc CFA (BCEAO)'],
            'EUR' => ['symbol' => '€', 'name' => 'Euro'],
            'USD' => ['symbol' => '$', 'name' => 'Dollar américain'],
            'GBP' => ['symbol' => '£', 'name' => 'Livre sterling'],
            'CAD' => ['symbol' => 'CA$', 'name' => 'Dollar canadien'],
            'CHF' => ['symbol' => 'CHF', 'name' => 'Franc suisse'],
        ];
    }
}
