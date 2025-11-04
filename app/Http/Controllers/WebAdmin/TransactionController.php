<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Repositories\OrganizationRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->transaction_search ? strtolower($request->transaction_search) : null;
        $transactionQuery = TransactionRepository::query();

        $host = request()->getSchemeAndHttpHost();
        $organization = OrganizationRepository::query()->where('domain', $host)->first();

        if ($organization) {
            $transactionQuery->when($organization, function ($query) use ($organization) {
                $query->whereHas('course', function ($q) use ($organization) {
                    $q->where('organization_id', $organization->id);
                });
            });
        }

        $transections = $transactionQuery->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('transaction.index', [
            'transactions' => $transections,
        ]);
    }

    public function failedTransaction(Request $request)
    {
        $search = $request->transaction_search ? strtolower($request->transaction_search) : null;
        $transactionQuery = TransactionRepository::query();

        $transections = $transactionQuery->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })
            ->where('is_paid', false)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('transaction.index', [
            'transactions' => $transections,
        ]);
    }

    public function courseWiseTransaction(Request $request)
    {
        $search = $request->transaction_search ? strtolower($request->transaction_search) : null;
        $transactionQuery = TransactionRepository::query();

        $transections = $transactionQuery->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })
            ->whereNotNull('course_id')
            ->whereNotNull('enrollment_id')
            ->whereNull('organization_id')
            ->whereNull('subscriber_id')
            ->whereNull('plan_id')
            ->whereNull('invoice_id')
            ->whereNull('organization_plan_id')
            ->where('is_paid', true)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('transaction.index', [
            'transactions' => $transections,
        ]);
    }

    public function invoiceWiseTransaction(Request $request)
    {
        $search = $request->transaction_search ? strtolower($request->transaction_search) : null;
        $transactionQuery = TransactionRepository::query();

        $transections = $transactionQuery->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })
            ->whereNotNull('enrollment_id')
            ->whereNotNull('invoice_id')
            ->whereNull('organization_id')
            ->whereNull('course_id')
            ->whereNull('subscriber_id')
            ->whereNull('plan_id')
            ->whereNull('organization_plan_id')
            ->where('is_paid', true)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('transaction.index', [
            'transactions' => $transections,
        ]);
    }

    public function subscriptionWiseTransaction(Request $request)
    {
        $search = $request->transaction_search ? strtolower($request->transaction_search) : null;
        $transactionQuery = TransactionRepository::query();

        $transections = $transactionQuery->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('plan', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        })
            ->whereNotNull('plan_id')
            ->whereNotNull('subscriber_id')
            ->whereNull('organization_id')
            ->whereNull('invoice_id')
            ->whereNull('organization_plan_id')
            ->where('is_paid', true)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // dd($transections);

        return view('transaction.subscription', [
            'transactions' => $transections,
        ]);
    }
    public function dnsWiseTransaction(Request $request)
    {
        $search = $request->transaction_search ? strtolower($request->transaction_search) : null;
        $transactionQuery = TransactionRepository::query();

        $transections = $transactionQuery->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('organization', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('organizationPlan', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        })
            ->whereNotNull('organization_id')
            ->whereNotNull('organization_plan_id')
            ->where('is_paid', true)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // dd($transections);

        return view('transaction.dns', [
            'transactions' => $transections,
        ]);
    }
}
