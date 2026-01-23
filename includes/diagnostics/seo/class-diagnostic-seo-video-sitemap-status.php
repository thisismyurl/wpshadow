<?php
declare(strict_types=1);
/**
 * Video Sitemap Status Diagnostic
 *
 * Philosophy: Ensure video content discoverability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Video_Sitemap_Status extends Diagnostic_Base {
    /**
     * Check presence of video sitemap endpoints.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $urls = [home_url('/video-sitemap.xml'), home_url('/sitemap-video.xml')];
        foreach ($urls as $url) {
            $response = wp_remote_head($url, ['timeout' => 3]);
            if (!is_wp_error($response)) {
                $code = wp_remote_retrieve_response_code($response);
                if ($code >= 200 && $code < 400) {
                    return null;
                }
            }
        }
        return [
            'id' => 'seo-video-sitemap-status',
            'title' => 'Video Sitemap Not Found',
            'description' => 'No video sitemap endpoint detected. If hosting videos, consider providing a video sitemap.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/video-sitemaps/',
            'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}