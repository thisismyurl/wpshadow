<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Notification Templates
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-notification-templates
 * Training: https://wpshadow.com/training/design-notification-templates
 */
class Diagnostic_Design_DESIGN_NOTIFICATION_TEMPLATES {
    public static function check() {
        return [
            'id' => 'design-notification-templates',
            'title' => __('Notification Templates', 'wpshadow'),
            'description' => __('Checks password reset and comment emails are styled and branded.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-notification-templates',
            'training_link' => 'https://wpshadow.com/training/design-notification-templates',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

