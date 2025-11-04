<?php

namespace App\Http\Controllers\Org\Auth;

use App\Events\MailSendEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\organization\RegisterRequest;
use App\Repositories\OrganizationRepository;
use App\Repositories\UserRepository;
use App\Repositories\VerifyOtpRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register()
    {
        return view('organization.auth.register');
    }

    public function authenticate(RegisterRequest $request)
    {
        // first org create
        $organization = OrganizationRepository::query()->create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // secound user create
        $user = UserRepository::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'is_org' => true,
            'organization_id' => $organization->id
        ]);

        // finally assign user to organization
        $organization->user_id = $user->id;
        $organization->save();

        // assign role
        $user->assignRole('organization');
        orgSocialLinksCreate($organization->id);

        // configure otp
        $otp = rand(111111, 999999);
        $token = Str::random(15);

        // store otp
        VerifyOtpRepository::query()->updateOrCreate([
            'contact' => $user->email,
        ], [
            'otp_code' => $otp,
            'token' => $token
        ]);

        // send otp
        try {
            MailSendEvent::dispatch($otp, $user->email);
            session()->put('verification_token', $token);
        } catch (\Exception $e) {
            // dd($e->getMessage());
        }

        return to_route('admin.login')->with('account-created', 'Account created successfully, please check your email for verification');
    }
}
