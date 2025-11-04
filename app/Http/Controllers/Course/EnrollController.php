<?php

namespace App\Http\Controllers\Course;

use App\Enum\NotificationTypeEnum;
use App\Http\Resources\CourseResource;
use App\Http\Resources\EnrolledCourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Repositories\CouponRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InvoicesRepository;
use App\Repositories\NotificationInstanceRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PlanRepository;
use App\Repositories\SubscriberRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Razorpay\Api\Subscription;

class EnrollController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        /** @var User */
        $loggedInUser = auth()->user();
        $enrolledCourses = $loggedInUser?->enrollments()->whereHas('course', function ($query) {
            $query->whereNull('deleted_at')->where('is_active', true);
        })->orderBy('created_at', 'desc')->skip($skip)->take($perPage)->get();

        $regularCourses = $enrolledCourses->where('course_progress', '<=', 100)->where('subscriber_id', null);
        $completedCourses = $enrolledCourses->where('course_progress', '>=', 100.00);
        $subscribedCourses = $enrolledCourses->whereNotNull('subscriber_id');


        return $this->json($enrolledCourses ? 'Enrolled courses found' : 'No enrolled courses found', [
            'total_items' => count($enrolledCourses),
            'courses' => EnrolledCourseResource::collection($enrolledCourses),
            'regular_courses' => EnrolledCourseResource::collection($regularCourses),
            'completed_courses' => EnrolledCourseResource::collection($completedCourses),
            'subscribed_courses' => EnrolledCourseResource::collection($subscribedCourses),
        ], $enrolledCourses ? 200 : 404);
    }

    public function summary()
    {
        $lastActivityCourse = EnrollmentRepository::query()
            ->where('user_id', '=', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->first()?->course;

        return $this->json('Enrollment summary', [
            'total_courses' => EnrollmentRepository::query()->where('user_id', '=', auth()->id())->count(),
            'completed_courses' => EnrollmentRepository::query()->where('user_id', '=', auth()->id())->where('course_progress', '=', 100.00)->count(),
            'certificate_achieved' => CourseRepository::query()
                ->where('certificate_available', '=', true)
                ->whereHas('enrollments', function ($query) {
                    return $query
                        ->where('user_id',  auth()->id())
                        ->where('course_progress', 100.00);
                })->count(),
            'last_activity_course' => $lastActivityCourse ? CourseResource::make($lastActivityCourse) : null,
        ]);
    }

    public function invoicePaymentInitiate(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        // Initiate the transaction
        return $this->initiateTransaction($request, null, $invoice->id);
    }

    public function initiateTransaction(Request $request, $courseId = null, $invoiceId = null)
    {
        $generateInvoice = $invoiceId ? InvoicesRepository::query()->where('id', $invoiceId)?->first() : null;

        $course = $courseId ? CourseRepository::query()->where('id', $courseId)?->first() : null;

        $title = null;

        $user = json_decode($request->user);

        $authUser = Auth::guard('api')->user();

        $amount = $request->total_amount;

        if ($request->payment_gateway == 'aamarpay') {
            $currency = config('app.currency');

            if ($currency != 'BDT') {

                $currencyConversionApi = "https://v6.exchangerate-api.com/v6/efe128e2762a67a6b7dabb59/latest/$currency";

                $jsonResponse = file_get_contents($currencyConversionApi);

                if (false !== $jsonResponse) {
                    try {
                        $response = json_decode($jsonResponse);
                        if ('success' === $response->result) {
                            $currentPrice = $amount;
                            $amount = round(($currentPrice * $response->conversion_rates->BDT), 2);
                        }
                    } catch (Exception $e) {
                        $amount  = $amount * 100;
                    }
                }
            }
        }

        if ($generateInvoice) {
            $courseIds = $generateInvoice->courses?->pluck('id')->toArray();
            $title = Str::limit(($generateInvoice->courses?->pluck('title')->join(', ')) ?? '', 210, '...');
            $titles = $generateInvoice->courses?->pluck('title', 'id');

            $alreadyEnrolledCourses = EnrollmentRepository::query()
                ->where('user_id', $user?->id ?? $authUser->id)
                ->whereIn('course_id', $courseIds)
                ->pluck('course_id')
                ->toArray();

            if (!empty($alreadyEnrolledCourses)) {
                // Get enrolled course titles
                $enrolledTitles = $titles->only($alreadyEnrolledCourses)->values()->toArray();
                $message = 'Already enrolled in: ' . implode(', ', $enrolledTitles);
                return $this->json($message, null, 400);
            }
        } else {
            $alreadyEnrolled = EnrollmentRepository::query()
                ->where('user_id', $user?->id ?? $authUser->id)
                ->where('course_id', $course->id)
                ->exists();
            if ($alreadyEnrolled) {
                return $this->json('Already enrolled in: ' . $course->title, null, 400);
            }
        }


        $paymentGateway = PaymentGateway::where('name', $request->payment_gateway)
            ->where('is_active', true)
            ->first();

        if (!$paymentGateway) {
            return $this->json('Invalid payment gateway', null, 400);
        }

        $coupon = request()->coupon_code ? $this->validateCoupon(request()->coupon_code) : null;

        $discount = $coupon ? $coupon->discount : 0;

        $transactionIdentifier = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 16)), 0, 16);

        $transaction = TransactionRepository::create([
            'identifier' => $transactionIdentifier,
            'course_id' => $course?->id ?? null,
            'invoice_id' => $generateInvoice?->id ?? null,
            'user_id' => $user?->id ?? $authUser->id,
            'coupon_id' => $coupon?->id,
            'course_title' => $course ? $course->title : $title,
            'user_phone' => auth()->user()->phone ?? UserRepository::getAll()->random()->phone,
            'payment_amount' => $amount,
            'payment_method' => $request->payment_gateway,
            'is_paid' => false,
        ]);

        return $this->json('Transaction initiated', [
            'payment_webview_url' => route('payment', ['identifier' => $transaction->identifier]),
        ], 201);
    }

    public function planWiseTransaction(Request $request)
    {
        $courses = CourseRepository::query()->whereIn('id', $request->course_ids)->get();

        $courseTitle = Str::limit($courses->pluck('title')->join(', '), 200, '...');

        $paymentGateway =  PaymentGateway::where('name', $request->payment_gateway)
            ->where('is_active', true)
            ->first();

        $amount = $request->total_amount;

        $plan = PlanRepository::query()->where('id', $request->plan_id)->first();

        $authUser = Auth::guard('api')->user();

        if ($paymentGateway->name == 'aamarpay') {
            $currency = config('app.currency');
            if ($currency != 'BDT') {

                $currencyConversionApi = "https://v6.exchangerate-api.com/v6/efe128e2762a67a6b7dabb59/latest/$currency";

                $jsonResponse = file_get_contents($currencyConversionApi);

                if (false !== $jsonResponse) {
                    try {
                        $response = json_decode($jsonResponse);
                        if ('success' === $response->result) {
                            $currentPrice = $amount;
                            $amount = round(($currentPrice * $response->conversion_rates->BDT), 2);
                        }
                    } catch (Exception $e) {
                        $amount  = $amount * 100;
                    }
                }
            }
        }

        $transactionIdentifier = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 16)), 0, 16);

        $transaction = TransactionRepository::create([
            'identifier' => $transactionIdentifier,
            'course_id' => null,
            'user_id' => $authUser->id,
            'plan_id' => $plan->id,
            'course_title' => $courseTitle,
            'user_phone' => auth()->user()->phone ?? UserRepository::getAll()->random()->phone,
            'payment_amount' => $amount,
            'payment_method' => $paymentGateway->name,
            'is_paid' => false,
        ]);

        $subscription =  SubscriberRepository::query()->updateOrCreate([
            'user_id' => $authUser->id,
            'plan_id' => $plan->id,
        ], [
            'transaction_id' => $transaction->id,
            'starts_at' => now(),
            'ends_at' => $plan->plan_type == 'yearly' ? now()->addYears($plan->duration) : now()->addDays($plan->duration),
            'subscribed_at' => now(),
        ]);

        $subscription->courses()->sync($request->course_ids);

        $transaction->subscriber_id = $subscription->id;
        $transaction->save();

        return $this->json('Transaction initiated', [
            'payment_webview_url' => route('payment', ['identifier' => $transaction->identifier]),
        ], 201);
    }

    public function paymentView(string $identifier)
    {
        $transaction = TransactionRepository::query()->where('identifier', '=', $identifier)->first();

        if ($transaction->course_id) {

            return $this->paymentService->processPayment($transaction->payment_amount, [
                'gateway' => $transaction->payment_method,
                'identifier' => base64_encode($transaction->identifier),
                'product' => [
                    'product' => $transaction->course->title,
                    'price' => $transaction->payment_amount,
                    'images' => $transaction->course->mediaPath
                ],
                'customer' => [
                    'name' => $transaction->user?->name ?? 'N/A',
                    'email' => $transaction->user?->email ?? 'N/A',
                    'phone' => $transaction->user?->phone ?? 'N/A',
                ]
            ]);
        } else if ($transaction->invoice_id) {

            $courseTitle = $transaction->invoice->courses->pluck('title')->toArray();

            $courseTitle = implode(', ', $courseTitle);

            $courseTitle = Str::limit($courseTitle, 180, '...');

            return $this->paymentService->processPayment($transaction->payment_amount, [
                'gateway' => $transaction->payment_method,
                'identifier' => base64_encode($transaction->identifier),
                'product' => [
                    'product' => $courseTitle,
                    'price' => $transaction->payment_amount,
                    'images' => $transaction->course->mediaPath ?? $transaction->invoice->courses->first()?->mediaPath
                ],
                'customer' => [
                    'name' => $transaction->user?->name ?? 'N/A',
                    'email' => $transaction->user?->email ?? 'N/A',
                    'phone' => $transaction->user?->phone ?? 'N/A',
                ]
            ]);
        } else if ($transaction->subscriber_id) {

            $courseTitle = $transaction->course_title;

            return $this->paymentService->processPayment($transaction->payment_amount, [
                'gateway' => $transaction->payment_method,
                'identifier' => base64_encode($transaction->identifier),
                'product' => [
                    'product' => $courseTitle,
                    'price' => $transaction->payment_amount,
                    'images' => $transaction->course->mediaPath ?? 'https://placehold.co/600x400'
                ],
                'customer' => [
                    'name' => $transaction->user?->name ?? 'N/A',
                    'email' => $transaction->user?->email ?? 'N/A',
                    'phone' => $transaction->user?->phone ?? 'N/A',
                ]
            ]);
        } else if ($transaction->organization_plan_id && $transaction->organization_id) {
            return $this->paymentService->processPayment($transaction->payment_amount, [
                'gateway' => $transaction->payment_method,
                'identifier' => base64_encode($transaction->identifier),
                'product' => [
                    'product' => $transaction->organizationPlan?->title . ' ' . $transaction->organizationPlan?->plan_type,
                    'price' => $transaction->payment_amount,
                    'images' => $transaction->course->mediaPath ?? 'https://placehold.co/600x400'
                ],
                'customer' => [
                    'name' => $transaction->user?->name ?? 'N/A',
                    'email' => $transaction->user?->email ?? 'N/A',
                    'phone' => $transaction->user?->phone ?? 'N/A',
                ]
            ]);
        }
    }

    public function verifyCoupon()
    {
        $coupon = $this->validateCoupon(request()->coupon_code);

        return $this->json($coupon ? 'Coupon is valid' : 'Coupon is invalid', [
            'is_valid' => $coupon ? true : false,
            'discount' => $coupon ? $coupon->discount : 0
        ], $coupon ? 200 : 404);
    }

    private function validateCoupon(string $code)
    {
        return CouponRepository::query()
            ->where('code', $code)
            ->where('applicable_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->where('is_active', true)
            ->first();
    }


    public function freeEnrollment(Course $course)
    {
        // Create enrollment
        $enrollment = Enrollment::updateOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
        ], [
            'coupon_id' => null,
            'course_price' => 0,
            'discount_amount' => 0,
            'certificate_user_name' => auth()->user()->name,
        ]);

        $notification = NotificationRepository::query()->where('type', NotificationTypeEnum::NewEnrollmentNotification->value)->first();

        $contentText = str_replace('{course_title}', $enrollment->course->title, $notification->content);
        NotificationInstanceRepository::create([
            'notification_id' => $notification->id,
            'recipient_id' => null,
            'course_id' => $enrollment->course->id,
            'metadata' => json_encode($enrollment->course),
            'url' => route('enrollment.index'),
            'content' => "Hi Chief, Mr. " . $enrollment->user->name . ' You have successfully enrolled in the course ' . $enrollment->course->title,
        ]);

        // Send notification to user
        NotificationInstanceRepository::create([
            'notification_id' => $notification->id,
            'recipient_id' => $enrollment->user->id,
            'course_id' => $enrollment->course->id,
            'metadata' => json_encode($enrollment->course),
            'content' => $contentText,
        ]);

        return $this->json('Course purchased successfully', [
            'enrollment_id' => $enrollment->id,
            'status' => 'success',
        ]);
    }
}
