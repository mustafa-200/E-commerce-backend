<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Setting\SettingService;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService)
    {
    }

    public function index()
    {
        $publicKeys = ['store_name', 'store_whatsapp_number', 'store_phone', 'facebook_url', 'instagram_url'];

        $settings = $this->settingService->all()->only($publicKeys);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب إعدادات المتجر بنجاح',
            'data' => $settings,
        ]);
    }
}
