<?php declare(strict_types=1);
/**
 * Image Sitemap Status Diagnostic
 *
 * Philosophy: Ensure media discoverability via sitemaps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Image_Sitemap_Status {
    /**
     * Check presence of image sitemap endpoints.
     *
     * @return array|null
     */
    public static function check() {
        $urls = [home_url('/image-sitemap.xml'), home_url('/sitemap-images.xml')];
        foreach ($urls as $url) {
            $response = wp_remote_head($url, ['timeout' => 3]);
            if (!is_wp_error($response)) {
                $code = wp_remote_retrieve_response_code($response);
                if ($code >= 200 && $code < 400) {
                    return null; // Found one, OK
                }
            }
        }
        return [
            'id' => 'seo-image-sitemap-status',
            'title' => 'Image Sitemap Not Found',
            'description' => 'No image sitemap endpoint detected. Ensure image URLs are discoverable via a sitemap.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/image-sitemaps/',
            'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
