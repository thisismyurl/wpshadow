<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Gestures extends Diagnostic_Base {
    
    protected static $slug = 'test-mobile-gestures';
    protected static $title = 'Mobile Gestures Test';
    protected static $description = 'Tests for gesture-only functionality (accessibility issue)';
    
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
        // Check for touch event listeners without mouse equivalents
        $has_touch_only = preg_match('/ontouchstart|ontouchmove|ontouchend/i', $html);
        $has_mouse_events = preg_match('/onclick|onmousedown|onmouseup/i', $html);
        
        // Check for swipe libraries
        $has_swipe_lib = preg_match('/swiper|hammer\.js|touchy|swipe\.js/i', $html);
        
        // Check for gesture-specific CSS (like transform on touch)
        $has_gesture_css = preg_match('/touch-action:\s*manipulation|touch-action:\s*none/i', $html);
        
        // If touch events exist but no alternatives, flag for accessibility
        if (($has_touch_only && !$has_mouse_events) || ($has_swipe_lib && !$has_mouse_events)) {
            return [
                'id' => 'mobile-gesture-only',
                'title' => 'Gesture-Only Functionality Detected',
                'description' => 'Touch/gesture events detected without mouse/keyboard alternatives. WCAG SC 2.5.1 requires all functionality to work without gestures (for users with motor disabilities).',
                'color' => '#ff5722',
                'bg_color' => '#ffebee',
                'kb_link' => 'https://wpshadow.com/kb/gesture-alternatives/',
                'training_link' => 'https://wpshadow.com/training/accessible-interactions/',
                'auto_fixable' => false,
                'threat_level' => 60,
                'module' => 'Accessibility',
                'priority' => 2,
                'meta' => [
                    'has_touch_events' => $has_touch_only,
                    'has_mouse_events' => $has_mouse_events,
                    'has_swipe_lib' => $has_swipe_lib,
                    'has_gesture_css' => $has_gesture_css,
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        return null; // PASS - has alternatives
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Mobile Gestures', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for gesture-only functionality (WCAG 2.5.1).', 'wpshadow');
    }
}
