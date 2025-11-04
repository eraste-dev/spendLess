<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Repositories\OrganizationPlanRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\ServerConfigurationRepository;
use Illuminate\Http\Request;

class DnsConfigController extends Controller
{
    public function index()
    {
        $domain = OrganizationRepository::query()
            ->where('user_id', auth()->id())
            ->first()
            ?->domain;

        $server = ServerConfigurationRepository::query()->first();

        return view('organization.dns.index', compact('domain', 'server'));;
    }

    public function store(Request $request)
    {
        // Logic to store DNS configuration
        $request->validate([
            'domain' => 'required|unique:organizations,domain',
        ]);

        $organization = OrganizationRepository::query()->where('user_id', auth()->id())->first();
        $organization->domain = $request->domain;
        $organization->save();

        return to_route('org.dns.index')->with('success', 'DNS configuration updated successfully.');
    }
}
