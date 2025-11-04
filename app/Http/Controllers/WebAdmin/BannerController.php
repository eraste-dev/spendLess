<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfferBannerRequest;
use App\Http\Requests\OfferBannerUpdateRequest;
use App\Repositories\OfferBannerRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationSiteSettingRepository;
use App\Repositories\SettingRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        $host = request()->getSchemeAndHttpHost();
        $organization = OrganizationRepository::query()->where('domain', $host)->first();
        $setting = SettingRepository::query()->first();
        if ($organization) {
            $setting = OrganizationSiteSettingRepository::query()->first();
        }
        $banners = OfferBannerRepository::query()->latest('id')->paginate(5);

        return view('banner.index', [
            'banners' => $banners,
            'is_publish' => $setting?->show_banner
        ]);
    }

    public function store(OfferBannerRequest $request)
    {
        $isActive = false;

        if (isset($request->is_active) && $request->is_active == 1) {
            $isActive = true;
        }

        // deactive all is_active = true
        if ($isActive == true) {
            OfferBannerRepository::query()->where('is_active', true)->update(['is_active' => false]);
        }

        OfferBannerRepository::storeByRequest($request, $isActive);


        return to_route('banner.index')->withSuccess('Banner created successfully.');
    }

    public function update(OfferBannerUpdateRequest $request, $id)
    {
        $banner = OfferBannerRepository::findOrFail($id);
        $isActive = false;

        if (isset($request->is_active) && $request->is_active == 1) {
            $isActive = true;
        }

        // deactive all is_active = true
        if ($isActive == true) {
            OfferBannerRepository::query()->where('is_active', true)->update(['is_active' => false]);
        }

        OfferBannerRepository::updateByRequest($request, $isActive, $banner);

        return to_route('banner.index')->withSuccess('Banner updated successfully.');
    }

    public function delete($id)
    {
        $banner = OfferBannerRepository::findOrFail($id);
        $banner->delete();
        return to_route('banner.index')->withSuccess('Banner deleted successfully.');
    }

    public function publish(Request $request)
    {
        $is_publish = false;
        $host = request()->getSchemeAndHttpHost();
        $organization = OrganizationRepository::query()->where('domain', $host)->first();

        if (isset($request->is_publish) && $request->is_publish == 1) {
            $is_publish = true;
        }

        $setting = SettingRepository::query()->first();

        if ($organization) {
            $setting = OrganizationSiteSettingRepository::query()->first();
        }


        $setting->update([
            'show_banner' => $is_publish
        ]);

        $this->setEnv('SHOW_BANNER', $is_publish ? 'true' : 'false');

        Artisan::call('config:clear');

        return to_route('banner.index')->withSuccess('Banner published successfully.');
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
