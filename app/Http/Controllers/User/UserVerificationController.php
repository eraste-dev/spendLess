<?php

namespace App\Http\Controllers\User;

use App\Events\MailSendEvent;
use App\Repositories\UserRepository;
use App\Repositories\VerifyOtpRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserVerificationController extends Controller
{
    public function index(Request $request)
    {
        return view('notice.verifyNotice');
    }

    public function sendotp(string $email)
    {
        $user = UserRepository::query()->where('email', $email)->first();

        $otp = rand(111111, 999999);
        $token = Str::random(15);

        VerifyOtpRepository::query()->updateOrCreate([
            'contact' => $user->email,
        ], [
            'otp_code' => $otp,
            'token' => $token
        ]);

        try {
            MailSendEvent::dispatch($otp, $user->email);
            session()->put('verification_token', $token);
        } catch (\Exception $e) {
            // dd($e->getMessage());
        }

        return back()->with('otp_sent', 'OTP sent successfully');
    }

    public function verify(Request $request, string $email)
    {
        $otpString = implode('', (array)$request->otp);

        $user = UserRepository::query()->where('email', $email)->first();

        if (!$user) {
            return back()->with('verify_error', 'The provided credentials do not match our records.');
        }

        $getToken = VerifyOtpRepository::query()->where('contact', $user->email)->where('otp_code', $otpString)->first();

        $token = session('verification_token') ?? $getToken->token;

        $informationExists = VerifyOtpRepository::query()->where('contact', $user?->email)->where('otp_code', $otpString)->where('token', $token)->exists();

        if ($user && $informationExists) {
            $user->markEmailAsVerified();
            session()->forget('verification_token');

            // Redirect based on user role
            if ($user->is_admin && $user->hasRole('admin')) {
                return to_route('admin.dashboard');
            } else if ($user->is_org && $user->organization) {
                return to_route('org.dashboard');
            } else if ($user->hasRole('instructor') || $user->instructor) {
                return to_route('instructor.dashboard');
            }
        }

        return back()->with('verify_error', 'The provided credentials do not match our records.');
    }
}
