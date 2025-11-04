<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrgPlanRequest;
use App\Models\OrganizationPlanSubscription;
use App\Repositories\OrganizationPlanRepository;
use App\Repositories\OrganizationPlanSubscriptionRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class AdminOrgManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = request()->search;
        $users = OrganizationRepository::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->whereHas('user', function ($query) {
                $query->where('is_org', 1);
            })
            ->with('user')
            ->paginate(10)->withQueryString();

        return view('adminOrganization.index', compact('users'));
    }

    public function edit($id)
    {
        $user = UserRepository::find($id);
        return view('user.edit', compact('user'));
    }

    public function planIndex()
    {
        $plan = [];
        $plans = OrganizationPlanRepository::query()->latest('id')->paginate(10);
        return view('adminOrgPlans.index', compact('plan', 'plans'));
    }

    public function planCreate()
    {
        return view('adminOrgPlans.create');
    }
    public function planStore(OrgPlanRequest $request)
    {
        $orgPlan = OrganizationPlanRepository::storeByRequest($request);

        if ($orgPlan == false) {
            return to_route('organizations.plan.create')->withError('You can add only 3 plans');
        }

        return to_route('organizations.plan.index')->withSuccess('Plan created successfully');
    }

    public function planEdit($id)
    {
        $plan = OrganizationPlanRepository::find($id);
        return view('adminOrgPlans.edit', compact('plan'));
    }
    public function planUpdate($id, OrgPlanRequest $request)
    {
        $plan = OrganizationPlanRepository::find($id);
        OrganizationPlanRepository::updateByRequest($request, $plan);

        return to_route('organizations.plan.index')->withSuccess('Plan updated successfully');
    }

    public function subscribers(Request $request)
    {
        $search = $request->search;
        $subscribers = OrganizationPlanSubscriptionRepository::query()
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('organization', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('plan', function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                });
            })
            ->where('is_paid', true)
            ->latest('id')->paginate(10)->withQueryString();

        return view('adminOrganization.subscribers', compact('subscribers'));
    }
}
