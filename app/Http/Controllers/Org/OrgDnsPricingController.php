<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\OrganizationPlan;
use App\Models\OrganizationPlanSubscription;
use App\Models\PaymentGateway;
use App\Repositories\OrganizationPlanRepository;
use App\Repositories\OrganizationPlanSubscriptionRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class OrgDnsPricingController extends Controller
{
    public function index()
    {
        $plans = OrganizationPlanRepository::query()->where('is_active', true)->get() ?? [];
        $paymentGateways = PaymentGateway::where('is_active', true)->get() ?? [];
        return view('organization.dnsPlan.index', compact('plans', 'paymentGateways'));
    }

    public function paymentInitiate(Request $request, OrganizationPlan $plan)
    {
        $loggedInUser = auth()->user();
        $organization = $loggedInUser->organization;
        $paymentGateway = PaymentGateway::where('id', $request->payment_gateway_id)->first();

        $transactionIdentifier = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 16)), 0, 16);

        $transaction = TransactionRepository::create([
            'organization_id' => $organization->id,
            'organization_plan_id' => $plan->id,
            'identifier' => $transactionIdentifier,
            'user_id' => $loggedInUser->id,
            'user_phone' => $loggedInUser->phone ?? UserRepository::getAll()->random()->phone,
            'payment_amount' => $plan->price,
            'payment_method' => $paymentGateway->name,
            'is_paid' => false,
        ]);

        $orgPlanSubs = OrganizationPlanSubscriptionRepository::query()->create([
            'organization_id' => $organization->id,
            'organization_plan_id' => $plan->id,
            'transaction_id' => $transaction->id,
            'subscribed_at' => now(),
            'expires_at' => $plan->plan_type == 'yearly' ? now()->addYears($plan->duration) : now()->addDays($plan->duration),
            'is_paid' => false,
        ]);

        return to_route('payment', ['identifier' => $transaction->identifier]);
    }
}
