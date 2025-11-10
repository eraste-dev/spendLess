<?php

namespace App\Http\Middleware;

use App\Services\Budget\BudgetCalculatorService;
use App\Repositories\User\UserSettingRepository;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $sharedData = [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];

        // Add budget summary and user settings for authenticated users
        if ($request->user()) {
            $budgetCalculator = app(BudgetCalculatorService::class);
            $userSettingRepo = app(UserSettingRepository::class);

            $sharedData['budgetSummary'] = $budgetCalculator->getBudgetSummary($request->user()->id);
            $sharedData['userSettings'] = $userSettingRepo->getOrCreateForUser($request->user()->id);
        }

        return $sharedData;
    }
}
