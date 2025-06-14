<?php

namespace App\Services\SocialProvider;

use App\Data\SocialProvider\SocialProviderCreateData;
use App\Models\SocialProvider;

class SocialProviderService
{
    public function create(SocialProviderCreateData $data): SocialProvider
    {
        $socialProvider = new SocialProvider();
        $socialProvider->provider_id = $data->provider_id;
        $socialProvider->provider_name = $data->provider_name;

        $socialProvider->user()->associate($data->user);
        $socialProvider->save();

        return $socialProvider;
    }
}