<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Zoom_Enabled extends Diagnostic_Base {
    
    protected static $slug = 'test-mobile-zoom-enabled';
    protected static $title = 'Mobile Zoom Test';
    protected static $description = 'Tests that zoom is not disabled (accessibility)';
    
    public static function check(?string $url = null, ?string $html = null): ?array {
        if ($html !== null) {
            return self::analyze_html($html, $url ?? 'provided-html');
        }
        
        $html = self::fetch_html($url ?? home_url('/'));
        if ($html === false) {
            return null;
        }
        
        return self::analyze_html($html, $url ?? home_url('/'));
    }
    
    protected static function analyze_html(string $html, string $checked_url): ?array {
        // Check viewport meta tag for zoom restrictions
        if (preg_match('/<meta[^>]+name=["\']viewport["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $match)) {
            $viewport_content = $match[1];
            
            // Check for zoom-disabling patterns
            $disables_zoom = preg_match('/user-scalable\s*=\s*no|user-scalable\s*=\s*0/i', $viewport_content);
            $max_scale_low = preg_match('/maximum-scale\s*=\s*1(?:\.0)?/i', $viewport_content);
            
            if ($disables_zoom || $max_scale_low) {
                return [
                    'id' => 'mobile-zoom-disabled',
                    'title' => 'Mobile Zoom Disabled',
                    'description' => 'Viewport meta tag disables zoom (user-scalable=no or maximum-scale=1). This prevents users from zooming text and violates WCAG accessibility guidelines.',
                    'color' => '#ff5722',
                    'bg_color' => '#ffebee',
                    'kb_link' => 'https://wpshadow.com/kb/mobile-zoom/',
                    'training_link' => 'https://wpshadow.com/training/mobile-accessibility/',
                    'auto_fixable' => false,
                    'threat_level' => 60,
                    'module' => 'Accessibility',
                    'priority' => 2,
                    'meta' => [
                        'viewport_content' => $viewport_content,
                        'disables_zoom' => $disables_zoom,
                        'low_max_scale' => $max_scale_low,
                        'checked_url' => $checked_url,
                    ],
                ];
            }
        }
        
        return null; // PASS - zoom not disabled
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Mobile Zoom Enabled', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks that zoom is not disabled (WCAG accessibility).', 'wpshadow');
    }
}
