<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Http\Resources\SubscriberResource;
use App\Models\Subscriber;
use App\Repositories\EnrollmentRepository;
use App\Repositories\SubscriberRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanWiseEnrollController extends Controller
{
    public function planWiseEnrollList()
    {
        $loggedInUser = Auth::guard('api')->user();

        $subscriptionPlans = SubscriberRepository::query()->where('user_id', $loggedInUser->id)
            ->where('is_subscribed', true)
            ->whereNotNull('plan_id')
            ->get();

        if ($subscriptionPlans->isEmpty()) {
            return $this->json('No Subscription found', null, 200);
        }

        return $this->json('Succesfully fetched Subscription Plans', SubscriberResource::collection($subscriptionPlans), 200);
    }
}
