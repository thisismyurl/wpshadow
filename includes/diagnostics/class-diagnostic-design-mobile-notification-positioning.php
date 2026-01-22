<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mobile Notification Positioning
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-notification-positioning
 * Training: https://wpshadow.com/training/design-mobile-notification-positioning
 */
class Diagnostic_Design_MOBILE_NOTIFICATION_POSITIONING {
    public static function check() {
        return [
            'id' => 'design-mobile-notification-positioning',
            'title' => __('Mobile Notification Positioning', 'wpshadow'),
            'description' => __('Checks notification positioning.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-notification-positioning',
            'training_link' => 'https://wpshadow.com/training/design-mobile-notification-positioning',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
