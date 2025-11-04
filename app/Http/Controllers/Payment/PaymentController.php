<?php

namespace App\Http\Controllers\Payment;

use App\Enum\NotificationTypeEnum;
use App\Events\CustomNotifyEvent;
use App\Events\NotifyEvent;
use App\Http\Resources\TransactionResource;
use App\Models\PaymentGateway;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InvoicesRepository;
use App\Repositories\NotificationInstanceRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrganizationPlanSubscriptionRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\SubscriberRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;


class PaymentController extends Controller
{
    public function index()
    {
        $transactions = TransactionRepository::query()->where('user_id', '=', auth()->user()->id)->where('is_paid', true)->get();

        return $this->json('Transaction list found', [
            'transactions' => TransactionResource::collection($transactions),
        ], 200);
    }

    public function paypalPaymentSuccess(Request $request, string $identifier)
    {
        if ($identifier) {
            return $this->enroll($identifier);
        } else {
            return $this->json('Purchase failed', null, 400);
        }
    }

    public function paypalPaymentCancel()
    {
        if (request()->is('api/*')) {
            return response()->json([
                'message' => 'Payment cancelled',
            ], 400);
        }

        return back()->withError('Payment cancelled');
    }

    public function stripePaymentSuccess(string $identifier)
    {
        return $this->enroll($identifier);
    }

    public function stripePaymentCancel()
    {
        if (request()->is('api/*')) {
            return response()->json([
                'message' => 'Payment cancelled',
            ], 400);
        }

        return back()->withError('Payment cancelled');
    }

    public function aamarpayPaymentSuccess(Request $request)
    {
        // Extract the transaction ID from the request
        $request_id = $request->mer_txnid;

        // Construct the URL to check the transaction status
        $url = "http://sandbox.aamarpay.com/api/v1/trxcheck/request.php"
            . "?request_id=$request_id"
            . "&store_id=aamarpaytest"
            . "&signature_key=dbb74894e82415a2f7ff0ec3a97e4183"
            . "&type=json";

        // Initialize cURL
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            )
        );

        // Execute the cURL request and get the response
        $response = curl_exec($curl);

        // Close the cURL session
        curl_close($curl);

        return $this->enroll($request->mer_txnid);
    }

    public function aamarpayPaymentCancel()
    {
        if (request()->is('api/*')) {
            return response()->json([
                'message' => 'Payment cancelled',
            ], 400);
        }

        return back()->withError('Payment cancelled');
    }

    public function razorPaySuccess(string $identifier)
    {

        if (!$identifier) {
            return $this->json('Payment failed', null, 400);
        }

        $this->enroll($identifier);
        $arr = array('msg' => 'Payment successfully credited', 'status' => true);

        return Response()->json($arr);
    }

    public function razorpayPaymentFail(Request $request)
    {
        if (request()->is('api/*')) {
            return response()->json([
                'message' => 'Payment Failed',
            ], 400);
        }

        return back()->withError('Payment failed');
    }

    public function aamarpayPaymentFail(Request $request)
    {
        if (request()->is('api/*')) {
            return response()->json([
                'message' => 'Payment Failed',
            ], 400);
        }

        return back()->withError('Payment failed');
    }

    public function paymobPaymentSuccess(Request $request)
    {
        return $this->enroll($request->identifier);
    }

    public function paymobPaymentFail(Request $request)
    {
        //Don't send json, It will cause problem in the some browser's popup window. you can render a view here
        return 'Payment failed';
    }

    public function paymobPaymentCancel()
    {
        return $this->json('Payment cancelled', null, 400);
    }

    public function enroll(string $identifier)
    {
        $enrollment = null;

        $transaction = TransactionRepository::query()->where('identifier', '=', base64_decode($identifier))->first();

        if (!$transaction) {
            return $this->json('Invalid transaction', null, 400);
        }

        if ($transaction->invoice_id) {

            $invoice = InvoicesRepository::query()->where('id', $transaction->invoice_id)->with('courses')->first();

            foreach ($invoice->courses as $course) {

                $enrollment = EnrollmentRepository::create([
                    'user_id' => $transaction->user->id,
                    'course_id' => $course?->id,
                    'coupon_id' => $transaction->coupon?->id,
                    'course_price' => $course->price ? $course->price : $course->regular_price,
                    'discount_amount' => $transaction->coupon ? $transaction->coupon->discount : 0,
                    'authorization_name' => $transaction->user->name,
                ]);
            }

            if ($invoice) {
                $invoice->update([
                    'payment_status' => true,
                    'payment_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else if ($transaction->subscriber_id) {

            $subscriber = SubscriberRepository::query()->where('id', $transaction->subscriber_id)->first();

            foreach ($subscriber->courses as $course) {

                $existingEnrollment = EnrollmentRepository::query()
                    ->where('user_id', $subscriber?->user->id)
                    ->where('course_id', $course->id)
                    ->first();

                $enrollmentData = [
                    'user_id' => $subscriber->user?->id,
                    'course_id' => $course?->id,
                    'coupon_id' => $transaction->coupon?->id,
                    'course_price' => $transaction->payment_amount,
                    'discount_amount' => $transaction->coupon ? $transaction->coupon->discount : 0,
                    'authorization_name' => $subscriber->user?->name,
                    'subscriber_id' => $subscriber->id
                ];

                if ($existingEnrollment) {
                    $existingEnrollment->update($enrollmentData);
                    $enrollment = $existingEnrollment;
                } else {
                    $enrollment = EnrollmentRepository::create($enrollmentData);
                }
            }

            if ($subscriber) {
                $subscriber->update([
                    'subscribed_at' => now(),
                    'is_subscribed' => true,
                ]);
            }
        } else if ($transaction->organization_plan_id && $transaction->organization_id) {
            $orgPlanSubscription = OrganizationPlanSubscriptionRepository::query()
                ->where('transaction_id', $transaction->id)
                ->where('organization_id', $transaction->organization_id)
                ->where('organization_plan_id', $transaction->organization_plan_id)
                ->first();

            $organization = OrganizationRepository::query()->where('id', $transaction->organization_id)->first();

            $orgPlanSubscription->update(
                [
                    'subscribed_at' => now(),
                    'expires_at' => $transaction->organizationPlan->plan_type == 'yearly' ? now()->addYears($transaction->organizationPlan->duration) : now()->addDays($transaction->organizationPlan->duration),
                    'is_paid' => true
                ]
            );

            $organization->update([
                'organization_plan_subscription_id' => $orgPlanSubscription->id
            ]);
        } else {
            $enrollment = EnrollmentRepository::create([
                'user_id' => $transaction->user->id,
                'course_id' => $transaction->course->id,
                'coupon_id' => $transaction->coupon?->id,
                'course_price' => $transaction->course->price ? $transaction->course->price : $transaction->course->regular_price,
                'discount_amount' => $transaction->coupon ? $transaction->coupon->discount : 0,
                'authorization_name' => $transaction->user->name,
            ]);
        }

        $transaction->update(['is_paid' => true, 'paid_at' => now(), 'enrollment_id' => $enrollment?->id]);

        //Send notification to admin

        if ($transaction->course_id) {

            $notification = NotificationRepository::query()->where('type', NotificationTypeEnum::NewEnrollmentNotification->value)->first();

            $contentText = str_replace('{course_title}', $transaction->course_title, $notification->content);

            NotificationInstanceRepository::create([
                'notification_id' => $notification->id,
                'recipient_id' => null,
                'course_id' => $transaction->course->id,
                'metadata' => json_encode($transaction->course),
                'url' => route('enrollment.index'),
                'content' => "Hi Chief, Mr. " . $transaction->user->name . ' You have successfully enrolled in the course ' . $transaction->course->title,
            ]);

            // Send notification to user
            NotificationInstanceRepository::create([
                'notification_id' => $notification->id,
                'recipient_id' => $transaction->user->id,
                'course_id' => $transaction->course->id,
                'metadata' => json_encode($transaction->course),
                'content' => $contentText,
            ]);
        }

        $fcm_token = $transaction->user?->fcm_token;

        if (!empty($fcm_token)) {
            CustomNotifyEvent::dispatch($fcm_token, $transaction->course_title, 'Mr. ' . $transaction->user->name . ' You have successfully enrolled in the course ');
        }

        if ($transaction->organization_id && $transaction->organization_plan_id) {
            return to_route('org.dns.index')->withSuccess('Subscription created successful');
        }

        return;
    }
}
