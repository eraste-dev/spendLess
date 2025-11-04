<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Repositories\OrganizationPlanSubscriptionRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;

class PlanManagementController extends Controller
{

    public function index()
    {
        $organization = auth()->user()->organization;
        $subscription = OrganizationPlanSubscriptionRepository::query()->where('id', $organization->organization_plan_subscription_id)->first();
        $plan = $subscription?->plan;
        $paymentGateways = PaymentGateway::where('is_active', true)->get() ?? [];

        return view('organization.subscription.index', compact('plan', 'paymentGateways','subscription'));
    }
    public function billingHistory()
    {
        $loggedInUser = auth()->user();
        $transactions = TransactionRepository::query()
            ->where('user_id', $loggedInUser->id)
            ->where('organization_id', $loggedInUser->organization_id)
            ->where('is_paid', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('organization.subscription.transaction', compact('transactions'));
    }
}
