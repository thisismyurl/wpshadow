<?php declare(strict_types=1);
/**
 * Touch Gesture Usability Diagnostic
 *
 * Philosophy: Mobile interactions must feel natural
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Touch_Gesture_Usability {
    public static function check() {
        return [
            'id' => 'seo-touch-gesture-usability',
            'title' => 'Touch Gesture Usability',
            'description' => 'Ensure touch gestures (swipe, pinch-zoom) work naturally. Avoid hover-only interactions.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/touch-gestures/',
            'training_link' => 'https://wpshadow.com/training/mobile-ux/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
