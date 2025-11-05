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
            'thousand_separator' => ['required', 'string', 'max:5'],
            'decimal_separator' => ['required', 'string', 'max:5'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
        ]);

        $userId = auth()->id();
        $this->settingRepo->updateForUser($userId, $validated);

        return redirect()
            ->route('budget.settings.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
