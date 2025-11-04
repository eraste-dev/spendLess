<?php

namespace App\Http\Controllers\WebAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Illuminate\Support\Str;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\GenerateInvoiceRepository;
use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use App\Repositories\InvoicesRepository;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $status = $request->status;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $invoices = InvoicesRepository::query()
            ->with(['user', 'courses'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw("lower(invoice_token) like ?", ["%{$search}%"])
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->whereRaw("lower(name) like ?", ["%{$search}%"]);
                        })
                        ->orWhereHas('courses', function ($query) use ($search) {
                            $query->whereRaw("lower(title) like ?", ["%{$search}%"]);
                        });
                });
            })
            ->when($status || $status == 0 && $status != null, function ($query) use ($status) {
                $query->where('payment_status', $status ? true : false);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('payment_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('payment_at', '<=', $endDate);
            })
            ->orderBy('created_at', 'desc');

        return view('invoice.index', [
            'invoices' => $invoices->paginate(20)->withQueryString()
        ]);
    }
    public function restoreTrash(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;
        $invoices = InvoicesRepository::query()
            ->with(['user', 'courses'])
            ->when($request->cat_search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw("lower(invoice_token) like '%{$search}%'")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->whereRaw("lower(name) like '%{$search}%'");
                        })
                        ->orWhereHas('courses', function ($query) use ($search) {
                            $query->whereRaw("lower(title) like '%{$search}%'");
                        });
                });
            })
            ->onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('invoice.restore', [
            'invoices' => $invoices
        ]);
    }
    public function create()
    {
        $courses = CourseRepository::query()->where('is_active', true)->get();
        $users = UserRepository::query()->whereDoesntHave('instructor')->where('is_admin', false)->get();

        return view('invoice.create', [
            'courses' => $courses,
            'users' => $users
        ]);
    }
    public function store(InvoiceRequest $request)
    {
        $invoiceToken = 'IV' . '-' . $request->user_id . '-' . Str::random(3);

        if (Invoice::where('invoice_token', $invoiceToken)->exists()) {
            return back()->withError('sorry cannot create invoice, please refresh the page');
        }


        $alreadyEnrolledCourseIds = EnrollmentRepository::query()
            ->where('user_id', $request->user_id)
            ->whereIn('course_id', $request->course_id)
            ->pluck('course_id')
            ->toArray();

        if ($alreadyEnrolledCourseIds) {
            // Fetch the course names
            $courseNames = CourseRepository::query()->whereIn('id', $alreadyEnrolledCourseIds)->pluck('title')->toArray();
            $courseList = implode(', ', $courseNames);
            return back()->withError("User already enrolled in the following course(s): $courseList");
        }

        $invoice = InvoicesRepository::create([
            'invoice_token' => $invoiceToken,
            'user_id' => $request->user_id,
            'total_price' => $request->course_price,
            'description' => $request->description,
            'qty' => $request->qty,
            'discount_type' => $request->discount_type,
            'discount_amount' => $request->discount_amount,
            'created_at' => now(),
        ]);

        if (isset($request->payment_status)) {
            // Create enrollment for each course
            foreach ($request->course_id as $courseId) {
                $course = CourseRepository::query()->findOrFail($courseId);

                // Create enrollment
                EnrollmentRepository::create([
                    'user_id' => $request->user_id,
                    'course_id' => $course->id,
                    'coupon_id' => null,
                    'course_price' => $request->course_price,
                    'discount_amount' => 0,
                    'certificate_user_name' => $invoice->user?->name,
                ]);
            }
            // Update the invoice with payment status
            $invoice->update([
                'payment_status' => true,
                'payment_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $invoice->courses()->attach($request->course_id);

        return to_route('invoice.index')->withSuccess('Invoice created successfully');
    }

    public function edit(Invoice $invoice)
    {
        $courses = CourseRepository::query()->where('is_active', true)->get();
        $users = UserRepository::query()->whereDoesntHave('instructor')->where('is_admin', false)->get();

        return view('invoice.edit', [
            'invoice' => $invoice,
            'courses' => $courses,
            'users' => $users
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $alreadyEnrolledCourseIds = EnrollmentRepository::query()
            ->where('user_id', $request->user_id)
            ->whereIn('course_id', $request->course_id)
            ->pluck('course_id')
            ->toArray();

        if (isset($request->payment_status)) {
            // Payment is checked: create enrollments for non-enrolled courses
            $newCourses = array_diff($request->course_id, $alreadyEnrolledCourseIds);

            foreach ($newCourses as $courseId) {
                $course = CourseRepository::query()->findOrFail($courseId);

                EnrollmentRepository::create([
                    'user_id' => $request->user_id,
                    'course_id' => $course->id,
                    'coupon_id' => null,
                    'course_price' => $request->course_price,
                    'discount_amount' => 0,
                    'certificate_user_name' => $invoice->user?->name,
                ]);
            }
        } else {
            // Payment is unchecked: remove enrollments for selected courses
            EnrollmentRepository::query()
                ->where('user_id', $request->user_id)
                ->whereIn('course_id', $request->course_id)
                ->delete();
        }

        $invoice->update([
            'user_id' => $request->user_id,
            'total_price' => $request->course_price,
            'description' => $request->description,
            'qty' => $request->qty,
            'discount_type' => $request->discount_type,
            'discount_amount' => $request->discount_amount,
            'payment_status' => (isset($request->payment_status) ? true : false) ?? false,
            'payment_at' => (isset($request->payment_status) ? now() : null) ?? null,
        ]);

        // Sync the course IDs (detach old ones and attach new)
        $invoice->courses()->sync($request->course_id);

        return to_route('invoice.index')->withSuccess('Invoice updated successfully');
    }

    public function delete($id)
    {
        $invoice = InvoicesRepository::query()->findOrFail($id);

        $invoice->delete();

        return to_route('invoice.index')->withSuccess('Invoice deleted successfully');
    }

    public function restore($id)
    {
        $invoice = InvoicesRepository::query()->onlyTrashed()->findOrFail($id);

        $invoice->restore();

        return to_route('invoice.index')->withSuccess('Invoice restored successfully');
    }

    public function invoiceVerify($invoice_token)
    {
        $invoice = InvoicesRepository::query()->where('invoice_token', $invoice_token)
            ->with('courses')
            ->firstOrFail();
        $setting = SettingRepository::query()->first();
        $paymentmethods = PaymentGateway::query()->where('is_active', true)->get();

        return view('invoice.invoice', [
            'invoice' => $invoice,
            'setting' => $setting,
            'paymentmethods' => $paymentmethods,
        ]);
    }

    public function invoiceDownload($invoice_token)
    {
        $invoice = InvoicesRepository::query()->where('invoice_token', request('invoice_token'))
            ->with('courses')
            ->firstOrFail();
        $setting = SettingRepository::query()->first();

        // pdf config
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $fontData['kalpurush'] = [
            'R' => 'kalpurush.ttf',
        ];

        $paperSize = 'A4';

        $mPdf = new Mpdf([
            'mode' => 'UTF-8',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/public/mpdf_tmp'),
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData,
            'format' => $paperSize,
        ]);

        $view = view('invoice.pdf', compact('invoice', 'setting'))->render();
        $mPdf->WriteHTML($view);

        // Output the PDF as a download
        return $mPdf->Output('invoice-' . $invoice->invoice_token . '.pdf', 'D');

        // Output the PDF as a stream
        // return $mPdf->Output('invoice-' . $invoice->invoice_token . '.pdf', 'I');
    }
}
