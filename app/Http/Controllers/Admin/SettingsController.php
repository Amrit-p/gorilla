<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingsService;
use App\Support\WebsiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {}

    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => $this->settingsService->allAsArray(),
            'siteLogoUrl' => WebsiteSettings::logoUrl(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->boolean('remove_site_logo')) {
            $this->settingsService->removeLogo($request->user());
        }

        if ($request->hasFile('site_logo')) {
            $this->settingsService->uploadLogo($request->user(), $request->file('site_logo'));
        }

        unset($data['site_logo'], $data['remove_site_logo']);

        $this->settingsService->update($request->user(), $data);

        return response()->json([
            'message' => 'Website settings updated successfully.',
        ]);
    }
}
