<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class MailService
{
    public static function getSystemData(): array
    {
        return [
            'url' => config('app.front_url'),
            'title' => config('app.name'),
            'logo' => Storage::disk('public')->path('system/images/logo-light-without-text-100_100.svg'),
        ];
    }
}