<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingUpdateRequest;
use App\Repositories\SettingRepository;
use App\Repositories\SocialMediaRepository;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $zones = array();
        $timestamp = time();
        $setting = SettingRepository::query()->first();

        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones[$key]['zone'] = $zone;
            $zones[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }

        return view('setting.edit', [
            'timezones' => $zones,
            'setting' => $setting,
        ]);
    }
    public function homePageSetup()
    {
        return view('setting.homeSetting', [
            'setting' => SettingRepository::query()->first(),
        ]);
    }

    public function smtpSetup()
    {
        return view('setting.smtp', [
            'setting' => SettingRepository::query()->first(),
        ]);
    }
    public function socialMediaSetup()
    {
        return view('setting.socialMedia', [
            'setting' => SettingRepository::query()->first(),
            'socialMedias' => SocialMediaRepository::query()->whereNull('organization_id')->get(),
        ]);
    }

    public function update(SettingUpdateRequest $request)
    {
        $loggedInUser = auth()->user();
        $setting = SettingRepository::query()->first();

        SettingRepository::updateByRequest($request, $setting);

        if ($request->app_name || $request->app_currency || $request->app_currency_symbol || $request->app_timezone || $request->app_minimum_amount) {
            $this->setEnv('APP_NAME', $request->get('app_name'));
            $this->setEnv('APP_CURRENCY', $request->get('app_currency'));
            $this->setEnv('APP_CURRENCY_SYMBOL', $request->get('app_currency_symbol'));
            $this->setEnv('APP_TIMEZONE', $request->get('app_timezone'));
            $this->setEnv('APP_MINIMUM_AMOUNT', $request->get('app_minimum_amount'));
        }

        if ($request->mail_host || $request->mail_port || $request->mail_user || $request->mail_password || $request->mail_encryption || $request->mail_address || $request->mail_from_name) {
            $this->setEnv('MAIL_HOST', $request->get('mail_host'));
            $this->setEnv('MAIL_PORT', $request->get('mail_port'));
            $this->setEnv('MAIL_USERNAME', $request->get('mail_user'));
            $this->setEnv('MAIL_PASSWORD', $request->get('mail_password'));
            $this->setEnv('MAIL_ENCRYPTION', $request->get('mail_encryption'));
            $this->setEnv('MAIL_FROM_ADDRESS', $request->get('mail_address'));
            $this->setEnv('MAIL_FROM_NAME', $request->get('mail_from_name'));
        }

        if ($request->jwt_secret) {
            $this->setEnv('JWT_SECRET', $request->get('jwt_secret'));
        }


        $medias = $request?->social_links;

        if (!empty($medias)) {
            foreach ($medias as $id => $url) {
                SocialMediaRepository::query()->updateOrCreate(
                    ['id' => $id],
                    [
                        'url' => $url,
                        'status' => $url ? true : false,
                    ],
                );
            };
        }

        Artisan::call('config:clear');

        return back()->withSuccess('Settings updated');
    }

    protected function setEnv($key, $value): bool
    {
        try {
            $path = base_path('.env');
            $file = file($path); // Open File Line By line
            $diffFileLines = array_diff($file, ["\n"]); // Remove all empty lines

            $exists = false;
            foreach ($diffFileLines as $lineNo => $oldValue) {
                if (strpos($oldValue, $key . '=') !== false) {
                    $file[$lineNo] = $key . '="' . $value . '"' . "\n";
                    $exists = true;
                }
            }
            if (!$exists) {
                $file[] = $key . '="' . $value . '"' . "\n";
            }

            file_put_contents($path, implode('', $file));

            return true;
        } catch (Exception $e) {
            // Log or report the exception
            Log::error("Error updating environment variable: {$e->getMessage()}");
            return false;
        }
    }
}
