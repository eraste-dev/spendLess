<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstructorResource;
use App\Http\Resources\PageResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\PaymentGateway;
use App\Models\SocialMedia;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\OrganizationSiteSettingRepository;
use App\Repositories\PageRepository;
use App\Repositories\SettingRepository;
use App\Repositories\SocialMediaRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index(Request $request)
    {
        $mostValuableCoursePrice = (int) CourseRepository::query()->orderBy('price', 'desc')->first()?->price;

        $organization = app()->bound('currentOrganization') ? app('currentOrganization') : null;

        if ($organization) {
            // Organization site settings\
            $setting = OrganizationSiteSettingRepository::query()->where('organization_id', $organization->id)->first();
        } else {
            // Main LMS settings
            $setting = SettingRepository::query()->first();
        }

        $socialMedia = SocialMediaRepository::query()->whereNull('organization_id')->whereNotNull('url')->get();

        if ($organization) {
            $socialMedia = SocialMediaRepository::query()->where('organization_id', $organization->id)->whereNotNull('url')->get();
        }

        $instructors = InstructorRepository::query()->inRandomOrder()->limit(4)->get();

        $whatsappSupportNumber = '';
        $whatsappSupport = str_replace(['+', ' ', '-', '(', ')'], '', $setting->whatsapp_support_number);

        if ($whatsappSupport) {
            $whatsappSupportNumber = 'https://api.whatsapp.com/send?phone=' . $whatsappSupport;
        }

        return $this->json('Master info found', [
            'master' => [
                'name' => $organization ? $setting->app_name : config('app.name'),
                'mode' => config('app.env'),
                'version' => config('app.version'),
                'logo' => $setting->logoPath,
                'favicon' => $setting->faviconPath,
                'footer' => $setting->footerPath,
                'scaner' => $setting->scanerPath,
                'currency_symbol' => $organization ? $setting->app_currency_symbol : config('app.currency_symbol'),
                'currency' => $organization ? $setting->app_currency : config('app.currency'),
                'default_language' => config('app.locale'),
                'minimum_amount' => config('app.minimum_amount'),
                'currency_position' => $organization ? "Left" : $setting->currency_position,
                'timezone' => config('app.timezone'),
                'credit_text' => $setting->footer_text,
                'min_course_price' => 0,
                'max_course_price' => 0 == $mostValuableCoursePrice ? 1_000 : $mostValuableCoursePrice,
                'payment_methods' => PaymentGatewayResource::collection(PaymentGateway::query()->where('is_active', '=', true)->get()),
                'pages' => PageResource::collection(PageRepository::query()->get()),
                'total_student' =>  UserRepository::getAll()->count(),
                'total_courses' =>  CourseRepository::getAll()->count(),
                'total_enrollments' =>  EnrollmentRepository::getAll()->count(),
                'footer_contact' => $setting->footer_contact_number,
                'footer_email' => $setting->footer_support_mail,
                'footer_description' => $setting->footer_description,
                'footer_social_icons' => $socialMedia,
                'footer_apple_link' => $setting->app_store_url,
                'footer_google_link' => $setting->play_store_url,
                'instructors' => InstructorResource::collection($instructors),
                'total_instructors' => InstructorRepository::query()->count(),
                'total_featured_instructors' => InstructorRepository::query()->where('is_featured', '=', true)->count(),
                'languages' => LanguageRepository::query()->get(),
                'show_banner' => $setting->show_banner ?? config('app.show_banner'),
                'publish_plan' => $organization ? false : $setting->publish_plan,
                'google_map' => $setting->google_map_embed_code,
                'whatsapp_support_title' => $setting->whatsapp_support_title,
                'whatsapp_support_number' => $whatsappSupportNumber,
                'whatsapp_contact_us' => $setting->whatsapp_support_number,
                'hero_thumbnail' => $setting->heroPath,
                'about_thumbnail' => $setting->aboutPath,
                'footer_bg_thumbnail' => $setting->footerBGPath,
                'hero_title' => $setting->hero_title,
                'hero_subtitle' => $setting->hero_subtitle,
                'hero_description' => $setting->hero_description,
            ],
        ]);
    }

    public function checkDeviceAndRedirect(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        if (stripos($userAgent, 'Android') !== false) {
            return redirect('https://play.google.com/store');
        }

        if (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            return redirect('https://www.apple.com/app-store');
        }

        return redirect('/');
    }
}
