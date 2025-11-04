<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServerConfigRequest;
use App\Repositories\ServerConfigurationRepository;
use Illuminate\Http\Request;

class ServerConfigurationController extends Controller
{
    public function index()
    {
        $server = ServerConfigurationRepository::query()->first();
        return view('server.index', compact('server'));
    }

    public function store(ServerConfigRequest $request)
    {
        ServerConfigurationRepository::updateOrCreate($request);
        return redirect()->route('server.index')->with('success', 'Server configuration saved successfully.');
    }
}
