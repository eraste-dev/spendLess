<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = PlanRepository::query()->where('is_active', true)->get();

        if (is_null($plans) || $plans->isEmpty()) {
            return $this->json('banner fetch successfully', null, 200);
        }

        return $this->json('plan fetch succesfully', PlanResource::collection($plans), 200);
    }

    public function show(Plan $plan)
    {
        return $this->json('plan fetch successfully', PlanResource::make($plan), 200);
    }
}
