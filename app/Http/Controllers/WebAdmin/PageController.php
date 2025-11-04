<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\PageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageUpdateRequest;
use App\Models\Page;
use App\Repositories\PageRepository;

class PageController extends Controller
{
    public function index()
    {
        $org = app()->bound('currentOrganization') ? app('currentOrganization') : null;

        $pages = Page::where('organization_id', optional($org)->id)->get();

        return view('page.index', [
            'pages' => $pages,
        ]);
    }

    public function edit($slug)
    {
        $org = app()->bound('currentOrganization') ? app('currentOrganization') : null;

        $page = Page::where('organization_id', $org?->id)
            ->where('slug', $slug)
            ->first();

        return view('page.edit', [
            'page' => $page ?? null,
            'slug' => $slug,
        ]);
    }

    public function update(PageUpdateRequest $request, $slug)
    {
        $org = app()->bound('currentOrganization') ? app('currentOrganization') : null;

        switch ($slug) {
            case 'privacy_policy':
                $request->merge(['title' => PageEnum::PRIVACY->value]);
                break;
            case 'terms_and_conditions':
                $request->merge(['title' => PageEnum::TERMS->value]);
                break;
            case 'refund_policy':
                $request->merge(['title' => PageEnum::REFUND->value]);
                break;
            case 'faq':
                $request->merge(['title' => PageEnum::FAQ->value]);
                break;
            case 'about_us':
                $request->merge(['title' => PageEnum::ABOUT->value]);
                break;
            default:
                $request->merge(['title' => PageEnum::CONTACT->value]);
        }

        PageRepository::updateByRequest($request, $slug, $org?->id);

        return to_route('page.edit', ['slug' => $slug])->withSuccess('Page updated');
    }
}
