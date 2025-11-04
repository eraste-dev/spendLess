<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferBannerResource;
use App\Repositories\OfferBannerRepository;
use Illuminate\Http\Request;

class OfferBannerController extends Controller
{
    public function index()
    {
        $banner = OfferBannerRepository::query()->where('is_active', 1)->latest('id')->first();

        if (!$banner) {
            return $this->json('banner fetch successfully', ['data' => null], 200);
        }

        return $this->json('banner fetch successfully', OfferBannerResource::make($banner), 200);
    }
}
