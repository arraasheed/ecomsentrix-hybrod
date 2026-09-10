<?php

namespace Workdo\PwaManifest\Http\Controllers;

use App\Models\Store;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ManifestController extends Controller
{
    public function show($slug)
    {
        $store = Store::where('slug', $slug)->where('enable_pwa_store', 'on')->first();

        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        $manifest = $this->generateManifest($store);

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }

    private function generateManifest(Store $store)
    {
        $shortName = substr($store->name, 0, 12);
        $startUrl = route('landing_page', $store->slug) . '?source=pwa';
        $scope = '/' . $store->slug;

        $themeSettings = json_decode($store->theme_settings ?? '{}', true);
        $backgroundColor = $themeSettings['header_bg_color'] ?? '#ffffff';
        $themeColor = $themeSettings['header_color'] ?? '#000000';
        $logoUrl = !empty($themeSettings['header_logo']) 
            ? get_file($themeSettings['header_logo']) 
            : asset('storage/uploads/logo/favicon.png');

        $icons = $this->buildIcons($logoUrl);
        $dir = $store->default_language === 'ar' ? 'rtl' : 'ltr';

        return [
            'name' => $store->name,
            'short_name' => $shortName,
            'start_url' => $startUrl,
            'scope' => $scope,
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => $backgroundColor,
            'theme_color' => $themeColor,
            'icons' => $icons,
            'categories' => ['shopping'],
            'lang' => $store->default_language ?? 'en',
            'dir' => $dir,
            'screenshots' => []
        ];
    }

    private function buildIcons($logoUrl)
    {
        $sizes = [192, 512];
        $icons = [];

        foreach ($sizes as $size) {
            $icons[] = [
                'src' => $logoUrl,
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any'
            ];
        }

        $icons[] = [
            'src' => $logoUrl,
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ];

        return $icons;
    }
}
