<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanRequest;
use App\Models\Plan;
use App\Repositories\CourseRepository;
use App\Repositories\PlanRepository;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = PlanRepository::query()->latest('id')->paginate(10);
        $totalTrash = PlanRepository::query()->onlyTrashed()->count();
        $setting = SettingRepository::query()->first();

        return view('plan.index', compact('plans', 'totalTrash', 'setting'));
    }

    public function create()
    {
        $courses = CourseRepository::query()->where('is_active', true)->get();
        return view('plan.create', compact('courses'));
    }

    public function store(PlanRequest $request)
    {
        PlanRepository::storeByRequest($request);

        return to_route('plan.index')->withSuccess('New Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        $courses = CourseRepository::query()->where('is_active', true)->get();
        return view('plan.edit', compact('plan', 'courses'));
    }

    public function update(PlanRequest $request, Plan $plan)
    {
        PlanRepository::updateByRequest($request, $plan);

        return to_route('plan.index')->withSuccess('Plan updated successfully.');
    }

    public function delete(Plan $plan)
    {
        $plan->delete();
        return to_route('plan.index')->withSuccess('Plan deleted successfully.');
    }

    public function trash()
    {
        $plans = PlanRepository::query()->onlyTrashed()->latest('id')->paginate(10);
        return view('plan.restore', compact('plans'));
    }

    public function restore($planId)
    {
        $plan = PlanRepository::query()->onlyTrashed()->findOrFail($planId);
        $plan->onlyTrashed()->restore();
        return to_route('plan.index')->withSuccess('Plan restored successfully.');
    }

    public function publish(Request $request)
    {
        $is_publish = false;

        if (isset($request->is_publish) && $request->is_publish == 1) {
            $is_publish = true;
        }

        $setting = SettingRepository::query()->first();

        $setting->update([
            'publish_plan' => $is_publish
        ]);

        return to_route('plan.index')->withSuccess('Plan published successfully.');
    }
}
