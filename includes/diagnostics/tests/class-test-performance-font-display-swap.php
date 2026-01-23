<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Font_Display_Swap extends Diagnostic_Base {
    
    protected static $slug = 'test-performance-font-display-swap';
    protected static $title = 'Font Display Swap Test';
    protected static $description = 'Tests for font-display: swap property';
    
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
        // Check for @font-face without font-display
        preg_match_all('/@font-face\s*{[^}]+}/is', $html, $fontfaces);
        
        $missing_display = 0;
        $total_fontfaces = count($fontfaces[0]);
        
        foreach ($fontfaces[0] as $fontface) {
            if (!preg_match('/font-display\s*:/i', $fontface)) {
                $missing_display++;
            }
        }
        
        if ($total_fontfaces === 0) {
            return null; // No @font-face, no issue
        }
        
        if ($missing_display === 0) {
            return null; // PASS - all have font-display
        }
        
        $percentage = round(($missing_display / $total_fontfaces) * 100);
        
        return [
            'id' => 'performance-font-display',
            'title' => 'Missing font-display Property',
            'description' => sprintf(
                '%d of %d @font-face rules lack font-display. This can cause invisible text (FOIT) while fonts load. Add font-display: swap for better UX.',
                $missing_display,
                $total_fontfaces
            ),
            'color' => '#ff9800',
            'bg_color' => '#fff3e0',
            'kb_link' => 'https://wpshadow.com/kb/font-display/',
            'training_link' => 'https://wpshadow.com/training/web-fonts/',
            'auto_fixable' => false,
            'threat_level' => 45,
            'module' => 'Performance',
            'priority' => 2,
            'meta' => [
                'total_fontfaces' => $total_fontfaces,
                'missing_display' => $missing_display,
                'percentage' => $percentage,
                'checked_url' => $checked_url,
            ],
        ];
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Font Display Swap', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for font-display: swap to prevent invisible text.', 'wpshadow');
    }
}
