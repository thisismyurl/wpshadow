<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Notification & Toast Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-notification-toast-design
 * Training: https://wpshadow.com/training/design-notification-toast-design
 */
class Diagnostic_Design_NOTIFICATION_TOAST_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-notification-toast-design',
            'title' => __('Notification & Toast Design', 'wpshadow'),
            'description' => __('Confirms notifications auto-dismiss (3-5s), stack properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-notification-toast-design',
            'training_link' => 'https://wpshadow.com/training/design-notification-toast-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}