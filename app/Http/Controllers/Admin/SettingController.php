<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Services\Setting\SettingService;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService)
    {
    }

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الإعدادات بنجاح',
            'data' => $this->settingService->all(),
        ]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $settings = $this->settingService->updateMany($request->validated()['settings']);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الإعدادات بنجاح',
            'data' => $settings,
        ]);
    }
}
