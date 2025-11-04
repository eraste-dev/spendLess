<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Repositories\OrganizationSiteSettingRepository;
use App\Repositories\SocialMediaRepository;
use Illuminate\Http\Request;

class OrganizationSiteSettingsController extends Controller
{
    public function index()
    {
        $loggedInUser = auth()->user();
        $setting = OrganizationSiteSettingRepository::query()->where('organization_id', $loggedInUser->organization->id)->first();

        $socialMedias = SocialMediaRepository::query()->where('organization_id', $loggedInUser->organization->id)->get();
        return view('organization.settings.index', [
            'socialMedias' => $socialMedias,
            'setting' => $setting,
        ]);
    }

    public function store(Request $request)
    {
        $loggedInUser = auth()->user();
        $settingExists = OrganizationSiteSettingRepository::query()->where('organization_id', $loggedInUser->organization->id)->exists();

        if ($settingExists) {
            $setting = OrganizationSiteSettingRepository::query()->where('organization_id', $loggedInUser->organization->id)->first();
            OrganizationSiteSettingRepository::updateByRequest($request, $setting);
        } else {
            OrganizationSiteSettingRepository::storeByRequest($request);
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

        return to_route('org.site.setting.index')->withSuccess('Organization settings updated successfully');
    }
}
