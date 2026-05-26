<?php

namespace App\Support;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Storage;

/**
 * Website branding and global settings readable across the app.
 */
class WebsiteSettings
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return config('mowing.website_defaults', []);
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        try {
            $stored = app(SettingsService::class)->allAsArray();
        } catch (\Throwable) {
            $stored = [];
        }

        return array_merge(self::defaults(), $stored);
    }

    public static function get(string $key, ?string $default = null): string
    {
        $all = self::all();

        return (string) ($all[$key] ?? $default ?? self::defaults()[$key] ?? '');
    }

    public static function logoUrl(): ?string
    {
        $path = self::get('site_logo_path');
        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Branding shown in sidebar, login, and document titles.
     *
     * @return array{site_name: string, site_tagline: string, site_logo_initial: string, site_logo_url: string|null}
     */
    public static function branding(): array
    {
        $initial = self::get('site_logo_initial', 'M');

        return [
            'site_name' => self::get('site_name', config('app.name', 'Mowing CRM')),
            'site_tagline' => self::get('site_tagline', 'Operations hub'),
            'site_logo_initial' => strtoupper(substr($initial !== '' ? $initial : 'M', 0, 1)),
            'site_logo_url' => self::logoUrl(),
        ];
    }

    /**
     * SEO and social meta tags (Admin → Website Settings).
     *
     * @return array{meta_title: string, meta_description: string, meta_keywords: string, og_title: string, og_description: string, robots_noindex: bool}
     */
    public static function seo(): array
    {
        return [
            'meta_title' => self::get('seo_meta_title'),
            'meta_description' => self::get('seo_meta_description'),
            'meta_keywords' => self::get('seo_meta_keywords'),
            'og_title' => self::get('seo_og_title'),
            'og_description' => self::get('seo_og_description'),
            'robots_noindex' => self::get('seo_robots_noindex') === '1',
        ];
    }
}
