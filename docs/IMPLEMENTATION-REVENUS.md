# Implémentation des Revenus - Guide Complet

## ✅ Ce qui a été créé

### 1. Migrations
- ✅ `user_settings` - Paramètres utilisateur (devise, symbole, format date)
- ✅ `incomes` - Revenus avec montant, date, récurrence

### 2. Modèles
- ✅ `Income` - Model avec scopes et méthodes de calcul
- ✅ `UserSetting` - Paramètres avec devises disponibles

### 3. Repositories
- ✅ `IncomeRepository` - CRUD + totaux + récurrence
- ✅ `UserSettingRepository` - Gestion des paramètres

### 4. Form Requests
- ✅ `StoreIncomeRequest` - Validation création
- ✅ `UpdateIncomeRequest` - Validation modification

### 5. Enregistrement
- ✅ Repositories enregistrés dans `AppServiceProvider`

## 📝 À faire maintenant

### 1. Créer le Controller Income

**Fichier**: `app/Http/Controllers/Budget/IncomeController.php`

```php
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
```

### 2. Créer le Controller Settings

**Fichier**: `app/Http/Controllers/Budget/SettingsController.php`

```php
<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserSettingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function __construct(
        protected UserSettingRepository $settingRepo
    ) {}

    public function index()
    {
        $userId = auth()->id();
        $settings = $this->settingRepo->getOrCreateForUser($userId);
        $currencies = $this->settingRepo->getAvailableCurrencies();

        return Inertia::render('budget/settings/index', [
            'settings' => $settings,
            'currencies' => $currencies,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
        ]);

        $userId = auth()->id();
        $this->settingRepo->updateForUser($userId, $validated);

        return redirect()
            ->route('budget.settings.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
```

### 3. Ajouter les routes

**Dans**: `routes/web.php`, section budget

```php
Route::prefix('budget')->name('budget.')->group(function () {
    // Revenus
    Route::get('/incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::put('/incomes/{id}', [IncomeController::class, 'update'])->name('incomes.update');
    Route::delete('/incomes/{id}', [IncomeController::class, 'destroy'])->name('incomes.destroy');

    // Configuration
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Catégories (existantes)
    Route::resource('income-categories', IncomeCategoryController::class);
    // ... autres routes
});
```

### 4. Mettre à jour les types TypeScript

**Fichier**: `resources/js/types/budget.ts`

Ajouter:

```typescript
export interface Income {
    id: number;
    user_id: number;
    income_category_id: number;
    category?: IncomeCategory;
    description: string | null;
    amount: number;
    income_date: string;
    is_recurring: boolean;
    recurrence_day: number | null;
    next_occurrence: string | null;
    created_at: string;
    updated_at: string;
}

export interface UserSettings {
    id: number;
    user_id: number;
    currency: string;
    currency_symbol: string;
    date_format: string;
    locale: string;
}

export interface Currency {
    symbol: string;
    name: string;
}
```

### 5. Frontend - Page Revenus

**Fichier**: `resources/js/pages/budget/incomes/index.tsx`

**Fonctionnalités**:
- Titre: "Revenus" (pas "Catégories de revenus")
- Header avec:
  - Date du jour au format français
  - Total du mois avec symbole de devise
- Liste des revenus du mois
- FAB pour ajouter un revenu
- Modal avec formulaire:
  - Select catégorie
  - Input montant (number)
  - Input date
  - Textarea description (optionnel)
  - Switch "Revenu mensuel"
  - Si switch activé: afficher jour de récurrence (1-31)

### 6. Frontend - Page Configuration

**Fichier**: `resources/js/pages/budget/settings/index.tsx`

**Fonctionnalités**:
- Titre: "Configuration"
- Sélection de devise avec preview
- Liste des devises disponibles: XOF, EUR, USD, GBP, CAD, CHF
- Preview du symbole sélectionné

## Structure finale attendue

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Budget/
│   │       ├── IncomeCategoryController.php (existant)
│   │       ├── IncomeController.php (à créer)
│   │       └── SettingsController.php (à créer)
│   └── Requests/
│       └── Budget/
│           ├── StoreIncomeRequest.php ✅
│           └── UpdateIncomeRequest.php ✅
├── Models/
│   ├── Budget/
│   │   ├── Income.php ✅
│   │   └── IncomeCategory.php (existant)
│   └── User/
│       └── UserSetting.php ✅
└── Repositories/
    ├── Budget/
    │   ├── IncomeRepository.php ✅
    │   └── IncomeCategoryRepository.php (existant)
    └── User/
        └── UserSettingRepository.php ✅

resources/js/
├── pages/
│   └── budget/
│       ├── incomes/
│       │   └── index.tsx (à créer)
│       ├── settings/
│       │   └── index.tsx (à créer)
│       └── income-categories/ (existant)
├── components/
│   └── features/
│       └── budget/
│           ├── income-card-mobile.tsx (à créer)
│           ├── income-list-mobile.tsx (à créer)
│           ├── income-form-modal-mobile.tsx (à créer)
│           └── revenue-header.tsx (à créer)
└── types/
    └── budget.ts (à mettre à jour)
```

## Prochaines étapes

1. Créer les controllers manquants
2. Ajouter les routes
3. Mettre à jour la navigation (remplacer "Catégories de revenus" par "Revenus")
4. Créer la page revenus avec header et totaux
5. Créer la page configuration
6. Tester le CRUD complet
7. Tester la récurrence mensuelle

## Notes importantes

- La récurrence crée automatiquement la prochaine occurrence
- Le jour de récurrence s'adapte aux mois courts (ex: 31 devient 28/29 pour février)
- Les totaux sont calculés par mois
- La devise est stockée dans user_settings
