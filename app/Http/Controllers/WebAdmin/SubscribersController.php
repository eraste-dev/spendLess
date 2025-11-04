<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Repositories\SubscriberRepository;
use Illuminate\Http\Request;

class SubscribersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $subscribers = SubscriberRepository::query()
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('plan', function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                });
            })
            ->get();

        return view('subscriber.index', compact('subscribers'));
    }
}
